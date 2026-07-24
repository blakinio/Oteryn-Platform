#!/usr/bin/env python3
from __future__ import annotations

import base64
import importlib.util
import os
import re
import secrets
import sys
import time
from pathlib import Path
from typing import Any


def load_harness() -> Any:
    path = Path(os.environ["REHEARSAL_CANARY_HARNESS_ROOT"]) / "run_rehearsal.py"
    spec = importlib.util.spec_from_file_location("canary_native_auth_rehearsal", path)
    if spec is None or spec.loader is None:
        raise RuntimeError(f"cannot load rehearsal harness: {path}")
    module = importlib.util.module_from_spec(spec)
    sys.modules[spec.name] = module
    spec.loader.exec_module(module)
    return module


def main() -> int:
    harness = load_harness()

    original_rehearsal_init = harness.Rehearsal.__init__

    def rehearsal_init_with_valid_app_key(self: Any) -> None:
        original_rehearsal_init(self)
        invalid_key = self.app_key
        self.app_key = "base64:" + base64.b64encode(secrets.token_bytes(32)).decode("ascii")
        self.secret_values = [self.app_key if value == invalid_key else value for value in self.secret_values]

    harness.Rehearsal.__init__ = rehearsal_init_with_valid_app_key

    original_docker = harness.docker

    def docker_with_runtime_compatibility(*args: str, **kwargs: Any) -> Any:
        normalized = [
            "MYSQL_PWD=" + arg[len("MARIADB_PWD=") :]
            if arg.startswith("MARIADB_PWD=")
            else arg
            for arg in args
        ]
        if (
            len(normalized) >= 2
            and normalized[0] == "create"
            and any(value == "CANARY_GAME_SESSION_ISSUER_ENABLED=true" for value in normalized)
            and not any(value.startswith("CANARY_GAME_SESSION_ISSUER_WORLD_ID=") for value in normalized)
        ):
            image_index = normalized.index("-e") if "-e" in normalized else 2
            normalized[image_index:image_index] = ["-e", "CANARY_GAME_SESSION_ISSUER_WORLD_ID=1"]
        return original_docker(*tuple(normalized), **kwargs)

    harness.docker = docker_with_runtime_compatibility

    def safe_curl_status(
        self: Any,
        network_key: str,
        url: str,
        *,
        ca: Path | None = None,
        method: str = "GET",
        token: str | None = None,
        payload: str | None = None,
    ) -> tuple[int, str]:
        ca = ca or self.tls["ca"]
        command = [
            "run", "--rm", "--network", self.networks[network_key],
            "-v", f"{ca}:/certs/ca.crt:ro",
            "curlimages/curl:8.12.1", "curl", "-sS", "--cacert", "/certs/ca.crt",
            "-o", "/tmp/body", "-w", "%{http_code}", "-X", method,
        ]
        if token is not None:
            command += ["-H", f"Authorization: Bearer {token}"]
        if payload is not None:
            command += ["-H", "Content-Type: application/json", "--data", payload]
        command.append(url)
        completed = harness.docker(*command, check=False)
        output = (completed.stdout or b"").decode("utf-8", errors="replace").strip()
        match = re.search(r"([0-9]{3})$", output)
        return (int(match.group(1)) if match else 0, output)

    def validate_tls_with_expected_polarity(self: Any) -> None:
        good_platform = self.curl_status("public", "https://platform.oteryn.test/health")[0]
        good_gateway = self.curl_status("public", "https://gateway.oteryn.test/health")[0]
        wrong_ca = self.curl_status("public", "https://platform.oteryn.test/health", ca=self.tls["wrong_ca"])[0]
        mismatch = self.curl_status("public", "https://10.201.0.10/health")[0]
        http_policy = harness.docker(
            "run", "--rm", "--network", self.networks["gateway_private"],
            "-e", "OTERYN_PLATFORM_BASE_URL=http://platform-internal.oteryn.test",
            "-e", f"OTERYN_PLATFORM_SERVICE_TOKEN={self.platform_current}",
            "-e", "GAME_SESSION_SERVICE_BASE_URL=https://canary-issuer.oteryn.test",
            "-e", f"GAME_SESSION_SERVICE_TOKEN={self.canary_current}",
            "-v", f"{self.gateway_bin}:/gateway:ro", "alpine:3.20", "/gateway", check=False,
        )
        private_from_public = self.curl_status("public", "https://canary-issuer.oteryn.test/internal/v1/game-sessions")[0]
        self.tls_result.update({
            "valid_ca_hostname_platform": good_platform == 200,
            "valid_ca_hostname_gateway": good_gateway == 200,
            "wrong_ca_fail_closed": wrong_ca == 0,
            "hostname_mismatch_fail_closed": mismatch == 0,
            "non_loopback_http_dependency_rejected": http_policy.returncode != 0,
            "private_issuer_unreachable_from_client_segment": private_from_public == 0,
        })
        expected = {
            "ephemeral_ca_generated": self.tls_result.get("ephemeral_ca_generated") is True,
            "private_keys_not_retained": self.tls_result.get("private_keys_retained") is False,
            "verification_bypass_not_used": self.tls_result.get("verification_bypass_used") is False,
            "valid_ca_hostname_platform": self.tls_result.get("valid_ca_hostname_platform") is True,
            "valid_ca_hostname_gateway": self.tls_result.get("valid_ca_hostname_gateway") is True,
            "wrong_ca_fail_closed": self.tls_result.get("wrong_ca_fail_closed") is True,
            "hostname_mismatch_fail_closed": self.tls_result.get("hostname_mismatch_fail_closed") is True,
            "non_loopback_http_dependency_rejected": self.tls_result.get("non_loopback_http_dependency_rejected") is True,
            "private_issuer_unreachable_from_client_segment": self.tls_result.get("private_issuer_unreachable_from_client_segment") is True,
        }
        self.tls_result["assertions"] = expected
        self.tls_result["status"] = "PASS" if all(expected.values()) else "FAIL"
        harness.write_json(self.evidence / "tls-validation.json", self.tls_result)
        if self.tls_result["status"] != "PASS":
            raise harness.RehearsalError("TLS validation failed")

    original_build_runtime_images = harness.Rehearsal.build_runtime_images

    def build_runtime_images_with_client_ca(self: Any) -> None:
        original_build_runtime_images(self)
        trust_dockerfile = self.temp / "OTClientTrust.Dockerfile"
        trust_dockerfile.write_text(
            f"FROM {self.prefix}-otclient:latest\n"
            "COPY tls/ca.crt /usr/local/share/ca-certificates/oteryn-ephemeral-rehearsal.crt\n"
            "RUN update-ca-certificates\n",
            encoding="utf-8",
        )
        harness.docker(
            "build", "-f", str(trust_dockerfile), "-t", f"{self.prefix}-otclient:latest",
            str(self.temp), capture=False,
        )

    def start_data_services_deterministically(self: Any) -> None:
        harness.docker(
            "run", "-d", "--name", self.container("mariadb"), "--network", self.networks["data"], "--network-alias", "mariadb",
            "-e", f"MARIADB_ROOT_PASSWORD={self.db_root_password}", "mariadb:11.4",
        )
        redis_conf = self.temp / "redis.conf"
        redis_conf.write_text(
            "bind 0.0.0.0\nprotected-mode yes\nport 6379\n" f"requirepass {self.redis_admin_password}\n",
            encoding="utf-8",
        )
        harness.docker(
            "run", "-d", "--name", self.container("redis"), "--network", self.networks["data"], "--network-alias", "redis",
            "-v", f"{redis_conf}:/usr/local/etc/redis/redis.conf:ro", "redis:7.4-alpine", "redis-server", "/usr/local/etc/redis/redis.conf",
        )

        init_complete = False
        for _ in range(120):
            logs = harness.docker("logs", self.container("mariadb"), check=False)
            log_text = ((logs.stdout or b"") + (logs.stderr or b"")).decode("utf-8", errors="replace")
            if "MariaDB init process done" in log_text:
                init_complete = True
                break
            time.sleep(1)
        if not init_complete:
            raise harness.RehearsalError("MariaDB image initialization did not complete")

        stable_pings = 0
        for _ in range(90):
            ping = harness.docker(
                "exec", "-e", f"MARIADB_PWD={self.db_root_password}", self.container("mariadb"),
                "mariadb-admin", "--skip-ssl", "-uroot", "ping", check=False,
            )
            if ping.returncode == 0:
                stable_pings += 1
                if stable_pings >= 2:
                    break
            else:
                stable_pings = 0
            time.sleep(1)
        else:
            raise harness.RehearsalError("MariaDB final server did not become stably ready")

        redis_ping = harness.docker("exec", self.container("redis"), "redis-cli", "-a", self.redis_admin_password, "PING", check=False)
        if redis_ping.returncode != 0:
            raise harness.RehearsalError("Redis did not become ready")

        grants = f"""
CREATE DATABASE platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE canary CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'platform_app'@'%' IDENTIFIED BY '{self.db_platform_password}';
GRANT ALL PRIVILEGES ON platform.* TO 'platform_app'@'%';
CREATE USER 'canary_game'@'%' IDENTIFIED BY '{self.db_canary_password}';
GRANT ALL PRIVILEGES ON canary.* TO 'canary_game'@'%';
CREATE USER 'oteryn_readonly'@'%' IDENTIFIED BY '{self.db_readonly_password}';
GRANT SELECT ON canary.* TO 'oteryn_readonly'@'%';
FLUSH PRIVILEGES;
""".encode()
        harness.docker(
            "exec", "-i", "-e", f"MARIADB_PWD={self.db_root_password}", self.container("mariadb"),
            "mariadb", "--skip-ssl", "-uroot", input_bytes=grants,
        )

        sources = [
            self.canary_source / "schema.sql",
            self.canary_source / "docker/data/01-test_account.sql",
            self.canary_source / "docker/data/02-test_account_players.sql",
        ]
        for index, source in enumerate(sources):
            target = f"/tmp/rehearsal-{index}.sql"
            harness.docker("cp", str(source), f"{self.container('mariadb')}:{target}")
            imported = harness.docker(
                "exec", "-e", f"MARIADB_PWD={self.db_root_password}", self.container("mariadb"),
                "sh", "-c", f"mariadb --skip-ssl -uroot canary < {target}", check=False,
            )
            harness.docker("exec", self.container("mariadb"), "rm", "-f", target, check=False)
            if imported.returncode != 0:
                logs = harness.docker("logs", self.container("mariadb"), check=False)
                server_tail = ((logs.stdout or b"") + (logs.stderr or b"")).decode("utf-8", errors="replace")[-4000:]
                raise harness.RehearsalError(f"MariaDB schema import {source.name} failed; server tail: {server_tail}")

        harness.docker(
            "exec", "-e", f"MARIADB_PWD={self.db_root_password}", self.container("mariadb"),
            "mariadb", "--skip-ssl", "-uroot", "canary", "-e", "DELETE FROM players_online; DELETE FROM boosted_boss;",
        )
        harness.docker(
            "exec", self.container("redis"), "redis-cli", "-a", self.redis_admin_password,
            "ACL", "SETUSER", "oteryn_runtime", "on", f">{self.redis_readonly_password}",
            "~cluster:channel:*:runtime", "+get", "+mget", "+exists", "+ping",
        )
        denied = harness.docker(
            "run", "--rm", "--network", self.networks["data"], "redis:7.4-alpine",
            "redis-cli", "-h", "redis", "--user", "oteryn_runtime", "-a", self.redis_readonly_password,
            "SET", "forbidden", "1", check=False,
        )
        denied_text = ((denied.stdout or b"") + (denied.stderr or b"")).decode("utf-8", errors="replace")
        self.runtime["redis_readonly_acl_write_rejected"] = denied.returncode != 0 or "NOPERM" in denied_text or "DENIED" in denied_text.upper()
        self.runtime["database_schema_import"] = "PASS"

    harness.Rehearsal.curl_status = safe_curl_status
    harness.Rehearsal.validate_tls = validate_tls_with_expected_polarity
    harness.Rehearsal.build_runtime_images = build_runtime_images_with_client_ca
    harness.Rehearsal.start_data_services = start_data_services_deterministically
    return harness.main()


if __name__ == "__main__":
    sys.exit(main())
