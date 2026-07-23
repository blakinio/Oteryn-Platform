#!/usr/bin/env python3
from __future__ import annotations

import importlib.util
import os
import re
import sys
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
            "run",
            "--rm",
            "--network",
            self.networks[network_key],
            "-v",
            f"{ca}:/certs/ca.crt:ro",
            "curlimages/curl:8.12.1",
            "curl",
            "-sS",
            "--cacert",
            "/certs/ca.crt",
            "-o",
            "/tmp/body",
            "-w",
            "%{http_code}",
            "-X",
            method,
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
            "build",
            "-f",
            str(trust_dockerfile),
            "-t",
            f"{self.prefix}-otclient:latest",
            str(self.temp),
            capture=False,
        )

    # Canary PR #841 owns the production-like orchestration harness. The
    # Platform-hosted runner adapts two harness-only execution details: safe
    # argument passing for curl probes and normal system trust installation of
    # the ephemeral CA for OTClient. It never disables TLS verification and it
    # does not alter any pinned product component source revision.
    harness.Rehearsal.curl_status = safe_curl_status
    harness.Rehearsal.build_runtime_images = build_runtime_images_with_client_ca
    return harness.main()


if __name__ == "__main__":
    sys.exit(main())
