from __future__ import annotations

import json
import re
import secrets
import shutil
import time
from pathlib import Path
from typing import Any


CACHE_CONTROL_PARTS = ("no-store", "no-cache", "must-revalidate", "private")


def install(harness: Any) -> None:
    original_ticket_matrix = harness.Rehearsal.validate_ticket_and_failure_matrix
    original_physical = harness.Rehearsal.run_physical_otclient
    original_sensitive_scan = harness.Rehearsal.sensitive_scan

    def cache_headers_ok(headers: dict[str, str]) -> bool:
        cache_control = headers.get("cache-control", "").lower()
        return (
            all(part in cache_control for part in CACHE_CONTROL_PARTS)
            and headers.get("pragma", "").lower() == "no-cache"
            and headers.get("expires", "") == "0"
        )

    def http_exchange(
        self: Any,
        network_key: str,
        url: str,
        *,
        method: str = "POST",
        payload: str | None = None,
        token: str | None = None,
        request_id: str | None = None,
    ) -> tuple[int, dict[str, str], str]:
        command = [
            "run",
            "--rm",
            "--network",
            self.networks[network_key],
            "-v",
            f"{self.tls['ca']}:/certs/ca.crt:ro",
            "-e",
            f"URL={url}",
            "-e",
            f"METHOD={method}",
            "-e",
            f"PAYLOAD={payload or ''}",
            "-e",
            f"TOKEN={token or ''}",
            "-e",
            f"REQUEST_ID={request_id or ''}",
            "curlimages/curl:8.12.1",
            "sh",
            "-c",
        ]
        headers = ['-H "Accept: application/json"']
        if payload is not None:
            headers.append('-H "Content-Type: application/json"')
        if token is not None:
            headers.append('-H "Authorization: Bearer $TOKEN"')
        if request_id is not None:
            headers.append('-H "X-Request-ID: $REQUEST_ID"')
        data = '--data "$PAYLOAD"' if payload is not None else ""
        command.append(
            "curl -sS --cacert /certs/ca.crt -i -X \"$METHOD\" "
            + " ".join(headers)
            + f" {data} \"$URL\" -w '\n__STATUS__:%{{http_code}}'; "
            + "rc=$?; if [ $rc -ne 0 ]; then printf '\n__STATUS__:000'; fi"
        )
        completed = harness.docker(*command, check=False)
        text = (completed.stdout or b"").decode("utf-8", errors="replace")
        raw, marker, status_text = text.rpartition("\n__STATUS__:")
        status = int(status_text.strip()) if marker and status_text.strip().isdigit() else 0
        header_text, separator, body = raw.partition("\r\n\r\n")
        if not separator:
            header_text, _, body = raw.partition("\n\n")
        parsed_headers: dict[str, str] = {}
        for line in header_text.replace("\r", "").split("\n")[1:]:
            if ":" not in line:
                continue
            name, value = line.split(":", 1)
            parsed_headers[name.strip().lower()] = value.strip()
        return status, parsed_headers, body

    def issue_canary_session(self: Any, *, account_id: int = 101, world_id: int = 1) -> tuple[str, dict[str, str]]:
        payload = json.dumps(
            {
                "protocol_version": 1,
                "canary_account_id": account_id,
                "world_id": world_id,
                "login_attempt_id": secrets.token_hex(16),
            },
            separators=(",", ":"),
        )
        status, headers, body = http_exchange(
            self,
            "gateway_private",
            "https://canary-issuer.oteryn.test/internal/v1/game-sessions",
            payload=payload,
            token=self.canary_current,
        )
        if status != 200:
            raise harness.RehearsalError(f"controlled Canary Game Session issue returned {status}")
        try:
            decoded = json.loads(body)
            credential = decoded["session"]["credential"]
        except (KeyError, TypeError, json.JSONDecodeError) as exc:
            raise harness.RehearsalError("controlled Canary Game Session response was invalid") from exc
        if not isinstance(credential, str) or not credential:
            raise harness.RehearsalError("controlled Canary Game Session credential was empty")
        self.secret_values.append(credential)
        return credential, headers

    def start_static_tls_response(
        self: Any,
        *,
        key: str,
        network_key: str,
        ip: str,
        alias: str,
        location: str,
        body: str,
        access_log: str,
    ) -> None:
        self.remove_container(key)
        conf = self.temp / f"{key}-static-malformed.conf"
        escaped_body = body.replace("'", "\\'")
        conf.write_text(
            "events {}\n"
            "http {\n"
            f"  access_log /evidence/{access_log};\n"
            "  server {\n"
            "    listen 443 ssl;\n"
            f"    server_name {alias};\n"
            "    ssl_certificate /certs/server.crt;\n"
            "    ssl_certificate_key /certs/server.key;\n"
            f"    location = {location} {{\n"
            "      default_type application/json;\n"
            "      add_header Cache-Control 'no-store, no-cache, must-revalidate, private' always;\n"
            "      add_header Pragma 'no-cache' always;\n"
            "      add_header Expires '0' always;\n"
            f"      return 200 '{escaped_body}';\n"
            "    }\n"
            "    location / { default_type application/json; return 503 '{\"error\":\"unavailable\"}'; }\n"
            "  }\n"
            "}\n",
            encoding="utf-8",
        )
        harness.docker(
            "create",
            "--name",
            self.container(key),
            "--network",
            self.networks[network_key],
            "--ip",
            ip,
            "--network-alias",
            alias,
            "-v",
            f"{conf}:/etc/nginx/nginx.conf:ro",
            "-v",
            f"{self.tls['server_crt']}:/certs/server.crt:ro",
            "-v",
            f"{self.tls['server_key']}:/certs/server.key:ro",
            "-v",
            f"{self.evidence}:/evidence",
            "nginx:1.27-alpine",
        )
        harness.docker("start", self.container(key))
        self.wait_container_running(key)

    def run_direct_negative(self: Any, *, label: str, credential: str, mode: str) -> None:
        shutil.copy2(self.harness_root / "otclient_session_negative_e2e.lua", self.otclient_source / "otclientrc.lua")
        events_path = self.evidence / "session-negative-events.tsv"
        internal_log = self.evidence / "otclient.session-negative.internal.log"
        events_path.unlink(missing_ok=True)
        internal_log.unlink(missing_ok=True)
        command = [
            "run",
            "--rm",
            "--name",
            f"{self.prefix}-otclient-{label}",
            "--network",
            self.networks["public"],
            "-v",
            f"{self.otclient_source}:/otclient",
            "-v",
            f"{self.otclient_bin}:/usr/local/bin/otclient:ro",
            "-v",
            f"{self.evidence}:/evidence",
            "-e",
            f"REHEARSAL_CLIENT_VERSION={self.client_version}",
            "-e",
            "REHEARSAL_WORLD=Canary E2E",
            "-e",
            "REHEARSAL_GAME_HOST=canary-game.oteryn.test",
            "-e",
            "REHEARSAL_GAME_PORT=7172",
            "-e",
            f"REHEARSAL_SESSION_CREDENTIAL={credential}",
            "-e",
            f"REHEARSAL_NEGATIVE_MODE={mode}",
            "-e",
            "REHEARSAL_AUTHORIZED_CHARACTER=Knight 1",
            "-e",
            "REHEARSAL_UNAUTHORIZED_CHARACTER=Knight 2",
            "-e",
            "REHEARSAL_ARTIFACT_DIR=/evidence",
            "-e",
            "REHEARSAL_GLOBAL_TIMEOUT_SECONDS=50",
            "-e",
            "LIBGL_ALWAYS_SOFTWARE=1",
            f"{self.prefix}-otclient:latest",
            "sh",
            "-c",
            "xvfb-run -a /usr/local/bin/otclient",
        ]
        completed = harness.docker(*command, check=False, capture=False)
        if completed.returncode != 0 or not events_path.exists():
            raise harness.RehearsalError(f"physical OTClient negative scenario {label} did not complete")
        events = events_path.read_text(encoding="utf-8", errors="replace")
        expected = {
            "invalid_session": "negative_result\tinvalid_session_rejected",
            "unauthorized_character_burn": "negative_result\tunauthorized_character_rejected_and_session_burned",
        }[mode]
        if expected not in events or "successful_world_entries\t0" not in events or "e2e\tsuccess" not in events:
            raise harness.RehearsalError(f"physical OTClient negative scenario {label} failed closed evidence")
        shutil.copy2(events_path, self.evidence / f"{label}-events.tsv")
        if internal_log.exists():
            shutil.copy2(internal_log, self.evidence / f"otclient.{label}.internal.log")

    def run_malformed_gateway_client(self: Any) -> None:
        shutil.copy2(self.harness_root / "otclient_malformed_gateway_e2e.lua", self.otclient_source / "otclientrc.lua")
        events = self.evidence / "malformed-gateway-client-events.tsv"
        browser_events = self.evidence / "malformed-gateway-browser-events.tsv"
        access_log = self.evidence / "malformed-gateway-access.log"
        for path in (events, browser_events, access_log):
            path.unlink(missing_ok=True)
        start_static_tls_response(
            self,
            key="gateway_public",
            network_key="public",
            ip="10.201.0.11",
            alias="gateway.oteryn.test",
            location="/v1/login",
            body='{"protocol_version":1,"session":{"credential":42}}',
            access_log="malformed-gateway-access.log",
        )
        try:
            command = [
                "run",
                "--rm",
                "--name",
                f"{self.prefix}-otclient-malformed-gateway",
                "--network",
                self.networks["public"],
                "-v",
                f"{self.otclient_source}:/otclient",
                "-v",
                f"{self.otclient_bin}:/usr/local/bin/otclient:ro",
                "-v",
                f"{self.evidence}:/evidence",
                "-v",
                f"{self.harness_root}:/harness:ro",
                "-v",
                f"{self.temp / 'client-bin'}:/harness-bin:ro",
                "-v",
                f"{self.tls['ca']}:/certs/ca.crt:ro",
                "-e",
                "PATH=/harness-bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin",
                "-e",
                "REHEARSAL_AUTH_URL_FILE=/tmp/native-auth-url",
                "-e",
                "REHEARSAL_BROWSER_EVENTS=/evidence/malformed-gateway-browser-events.tsv",
                "-e",
                "REHEARSAL_CA_FILE=/certs/ca.crt",
                "-e",
                f"REHEARSAL_IDENTITY_EMAIL={self.identity_email}",
                "-e",
                f"REHEARSAL_IDENTITY_PASSWORD={self.identity_password}",
                "-e",
                f"REHEARSAL_CLIENT_VERSION={self.client_version}",
                "-e",
                "REHEARSAL_GAME_HOST=canary-game.oteryn.test",
                "-e",
                "REHEARSAL_GAME_PORT=7172",
                "-e",
                "REHEARSAL_PLATFORM_PUBLIC_URL=https://platform.oteryn.test",
                "-e",
                "REHEARSAL_GATEWAY_PUBLIC_URL=https://gateway.oteryn.test",
                "-e",
                f"REHEARSAL_OAUTH_CLIENT_ID={self.oauth_client_id}",
                "-e",
                "REHEARSAL_ARTIFACT_DIR=/evidence",
                "-e",
                "REHEARSAL_FAILURE_OBSERVATION_MS=15000",
                "-e",
                "REHEARSAL_GLOBAL_TIMEOUT_SECONDS=50",
                "-e",
                "LIBGL_ALWAYS_SOFTWARE=1",
                f"{self.prefix}-otclient:latest",
                "sh",
                "-c",
                "python3 /harness/browser_driver.py & browser=$!; xvfb-run -a /usr/local/bin/otclient; client=$?; wait $browser; browser_rc=$?; test $client -eq 0; test $browser_rc -eq 0",
            ]
            completed = harness.docker(*command, check=False, capture=False)
            if completed.returncode != 0 or not events.exists():
                raise harness.RehearsalError("physical malformed Gateway response scenario did not complete")
            text = events.read_text(encoding="utf-8", errors="replace")
            if "malformed_gateway_response\trejected" not in text or "successful_world_entries\t0" not in text or "e2e\tsuccess" not in text:
                raise harness.RehearsalError("OTClient did not fail closed on malformed Gateway response")
            access = access_log.read_text(encoding="utf-8", errors="replace") if access_log.exists() else ""
            if "POST /v1/login" not in access:
                raise harness.RehearsalError("malformed Gateway proxy did not observe the physical login request")
        finally:
            self.start_gateway_public_proxy()

    def extended_ticket_matrix(self: Any) -> None:
        original_ticket_matrix(self)
        self.failure["private_gateway_to_canary_path_blocked_fail_closed"] = self.failure.get("canary_issuer_unavailable_fail_closed") is True
        self.failure["private_gateway_to_canary_path_recovery"] = self.failure.get("canary_issuer_recovery") is True

        override_ticket = self.issue_ticket()
        self.raw_tickets.append(override_ticket)
        override_payload = json.dumps(
            {
                "protocol_version": 1,
                "game_login_ticket": override_ticket,
                "canary_account_id": 999999,
            },
            separators=(",", ":"),
        )
        override_status, override_headers, _ = http_exchange(
            self,
            "public",
            "https://gateway.oteryn.test/v1/login",
            payload=override_payload,
        )
        self.failure["client_controlled_account_override_rejected"] = override_status == 400
        self.failure["account_override_response_cache_headers"] = cache_headers_ok(override_headers)

        malformed_ticket = self.issue_ticket()
        self.raw_tickets.append(malformed_ticket)
        start_static_tls_response(
            self,
            key="canary_issuer",
            network_key="gateway_private",
            ip="10.201.1.11",
            alias="canary-issuer.oteryn.test",
            location="/internal/v1/game-sessions",
            body='{"protocol_version":1,"session":{"credential":42,"expires_at":false}}',
            access_log="malformed-canary-access.log",
        )
        try:
            malformed_status = self.gateway_login_status(malformed_ticket)
            self.failure["malformed_canary_session_response_fail_closed"] = malformed_status >= 500
            malformed_access = self.evidence / "malformed-canary-access.log"
            self.failure["malformed_canary_request_reached_private_boundary"] = malformed_access.exists() and "POST /internal/v1/game-sessions" in malformed_access.read_text(encoding="utf-8", errors="replace")
        finally:
            self.start_proxy("canary_issuer", "gateway_private", "10.201.1.11", "canary-issuer.oteryn.test", "canary_private", "10.201.2.20:18082")

        session_credential, canary_headers = issue_canary_session(self)
        correlation_id = "native-auth-rehearsal-gateway-correlation"
        happy_ticket = self.issue_ticket()
        self.raw_tickets.append(happy_ticket)
        happy_payload = json.dumps({"protocol_version": 1, "game_login_ticket": happy_ticket}, separators=(",", ":"))
        gateway_status, gateway_headers, gateway_body = http_exchange(
            self,
            "public",
            "https://gateway.oteryn.test/v1/login",
            payload=happy_payload,
            request_id=correlation_id,
        )
        if gateway_status == 200:
            try:
                gateway_credential = json.loads(gateway_body)["session"]["credential"]
                if isinstance(gateway_credential, str) and gateway_credential:
                    self.secret_values.append(gateway_credential)
            except (KeyError, TypeError, json.JSONDecodeError):
                pass

        platform_status, platform_headers, _ = http_exchange(
            self,
            "gateway_private",
            "https://platform-internal.oteryn.test/internal/v1/game-auth/accounts/101/login-context",
            method="GET",
            token=self.platform_current,
        )
        platform_request_id = platform_headers.get("x-request-id", "")
        platform_log_text = ""
        for _ in range(40):
            platform_logs = harness.docker("logs", self.container("platform"), check=False)
            platform_log_text = ((platform_logs.stdout or b"") + (platform_logs.stderr or b"")).decode("utf-8", errors="replace")
            if platform_request_id and platform_request_id in platform_log_text:
                break
            time.sleep(0.25)

        gateway_log_text = ""
        for _ in range(40):
            gateway_logs = harness.docker("logs", self.container("gateway"), check=False)
            gateway_log_text = ((gateway_logs.stdout or b"") + (gateway_logs.stderr or b"")).decode("utf-8", errors="replace")
            if correlation_id in gateway_log_text:
                break
            time.sleep(0.25)

        cache_result = {
            "schema_version": 1,
            "canary_issuer_success": cache_headers_ok(canary_headers),
            "gateway_login_success": cache_headers_ok(gateway_headers),
            "gateway_login_status": gateway_status,
            "normal_happy_path_no_5xx": gateway_status == 200,
        }
        cache_result["status"] = "PASS" if all(
            cache_result[key] is True
            for key in ("canary_issuer_success", "gateway_login_success", "normal_happy_path_no_5xx")
        ) else "FAIL"
        harness.write_json(self.evidence / "cache-header-validation.json", cache_result)

        correlation = {
            "schema_version": 1,
            "platform_status": platform_status,
            "platform_response_request_id": platform_request_id,
            "platform_id_logged": bool(platform_request_id and platform_request_id in platform_log_text),
            "gateway_supplied_request_id": correlation_id,
            "gateway_response_request_id": gateway_headers.get("x-request-id", ""),
            "gateway_id_logged": correlation_id in gateway_log_text,
        }
        correlation["status"] = "PASS" if (
            platform_status == 200
            and correlation["platform_id_logged"] is True
            and correlation["gateway_response_request_id"] == correlation_id
            and correlation["gateway_id_logged"] is True
        ) else "FAIL"
        harness.write_json(self.evidence / "request-correlation.json", correlation)
        self.runtime["normal_happy_path_no_5xx"] = cache_result["normal_happy_path_no_5xx"]
        self.runtime["request_correlation"] = correlation["status"]
        self.runtime["cache_header_validation"] = cache_result["status"]
        self.secret_values.append(session_credential)
        if cache_result["status"] != "PASS" or correlation["status"] != "PASS":
            raise harness.RehearsalError("cache-header or request-correlation validation failed")

    def extended_physical(self: Any) -> None:
        random_credential = secrets.token_urlsafe(48)
        self.secret_values.append(random_credential)
        run_direct_negative(self, label="random-invalid-session", credential=random_credential, mode="invalid_session")
        self.runtime["physical_random_invalid_game_session"] = "PASS"

        unauthorized_credential, _ = issue_canary_session(self)
        run_direct_negative(
            self,
            label="unauthorized-character-burn",
            credential=unauthorized_credential,
            mode="unauthorized_character_burn",
        )
        self.runtime["physical_unauthorized_character_burn"] = "PASS"

        restart_credential, _ = issue_canary_session(self)
        self.start_canary(enabled=True, previous=True)
        run_direct_negative(self, label="restart-invalidated-session", credential=restart_credential, mode="invalid_session")
        self.runtime["canary_restart_invalidated_process_local_session"] = True
        self.runtime["canary_restart_service_recovered"] = self.private_canary_status(self.canary_current) == 200
        if self.runtime["canary_restart_service_recovered"] is not True:
            raise harness.RehearsalError("Canary issuer did not recover after restart invalidation test")

        run_malformed_gateway_client(self)
        self.failure["physical_otclient_malformed_gateway_fail_closed"] = True
        shutil.copy2(self.harness_root / "otclient_native_flow_e2e.lua", self.otclient_source / "otclientrc.lua")
        original_physical(self)

    def extended_sensitive_scan(self: Any) -> None:
        original_sensitive_scan(self)
        result_path = self.evidence / "sensitive-log-scan.json"
        result = json.loads(result_path.read_text(encoding="utf-8"))
        findings = list(result.get("findings", []))
        jwt_pattern = re.compile(r"\beyJ[A-Za-z0-9_-]{8,}\.[A-Za-z0-9_-]{8,}\.[A-Za-z0-9_-]{8,}\b")
        for path in sorted(self.evidence.rglob("*")):
            if not path.is_file() or path == result_path:
                continue
            text = path.read_text(encoding="utf-8", errors="ignore")
            if jwt_pattern.search(text):
                findings.append({"file": path.name, "pattern": "jwt_like_token"})
        result["findings"] = findings
        result["known_runtime_secrets_scanned"] = len(self.secret_values) + len(self.raw_tickets)
        result["jwt_pattern_scanned"] = True
        result["status"] = "PASS" if not findings else "FAIL"
        harness.write_json(result_path, result)
        if findings:
            raise harness.RehearsalError("sensitive-log scan found token-like material")

    harness.Rehearsal.validate_ticket_and_failure_matrix = extended_ticket_matrix
    harness.Rehearsal.run_physical_otclient = extended_physical
    harness.Rehearsal.sensitive_scan = extended_sensitive_scan
