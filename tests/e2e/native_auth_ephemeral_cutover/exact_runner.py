#!/usr/bin/env python3
from __future__ import annotations

import json
import os
import sys
from pathlib import Path
from typing import Any

import acceptance_extensions
import platform_runner


def required(name: str) -> str:
    value = os.environ.get(name, "").strip()
    if not value:
        raise RuntimeError(f"required environment variable is missing: {name}")
    return value


def install_exact_harness_adapter() -> None:
    original_load_harness = platform_runner.load_harness

    def load_harness_with_exact_metadata() -> Any:
        harness = original_load_harness()
        harness.PLATFORM_REF = required("PLATFORM_REF")
        harness.GATEWAY_REF = required("GATEWAY_REF")
        harness.CANARY_REF = required("CANARY_REF")
        harness.OTCLIENT_REF = required("OTCLIENT_REF")
        harness.OTCLIENT_BUILD_RUN = int(required("SOURCE_BUILD_RUN"))
        harness.OTCLIENT_BUILD_ARTIFACT_ID = int(required("OTCLIENT_ARTIFACT_ID"))
        harness.OTCLIENT_BUILD_ARTIFACT_DIGEST = required("OTCLIENT_ARTIFACT_DIGEST")

        original_platform_env = harness.Rehearsal.platform_env

        def platform_env_with_trusted_proxy(self: Any, previous: bool) -> list[str]:
            env = original_platform_env(self, previous)
            env.extend(["-e", "TRUSTED_PROXIES=10.201.3.0/24"])
            return env

        harness.Rehearsal.platform_env = platform_env_with_trusted_proxy
        acceptance_extensions.install(harness)
        return harness

    platform_runner.load_harness = load_harness_with_exact_metadata


def update_retained_evidence() -> None:
    evidence_dir = Path(required("REHEARSAL_EVIDENCE_DIR"))
    evidence_dir.mkdir(parents=True, exist_ok=True)

    revisions_path = evidence_dir / "runtime-revisions.json"
    if revisions_path.exists():
        revisions = json.loads(revisions_path.read_text(encoding="utf-8"))
    else:
        revisions = {"schema_version": 1, "build": {}, "components": {}}
    revisions["build"] = {
        "workflow": "Native Auth Ephemeral Cutover Rehearsal",
        "workflow_run": os.environ.get("GITHUB_RUN_ID", "local"),
        "source_build_run": required("SOURCE_BUILD_RUN"),
    }
    revisions["components"] = {
        "platform": {"repository": "blakinio/Oteryn-Platform", "source_sha": required("PLATFORM_REF")},
        "gateway": {"repository": "blakinio/Oteryn-Platform", "source_sha": required("GATEWAY_REF")},
        "canary": {"repository": "blakinio/canary", "source_sha": required("CANARY_REF")},
        "otclient": {"repository": "blakinio/otclient", "source_sha": required("OTCLIENT_REF")},
        "canary_harness": {"repository": "blakinio/canary", "source_sha": required("CANARY_HARNESS_REF")},
    }
    revisions_path.write_text(json.dumps(revisions, indent=2, sort_keys=True) + "\n", encoding="utf-8")

    digests_path = evidence_dir / "artifact-digests.json"
    if digests_path.exists():
        digests = json.loads(digests_path.read_text(encoding="utf-8"))
    else:
        digests = {"schema_version": 1}
    digests["source_build_run"] = int(required("SOURCE_BUILD_RUN"))
    digests["gateway_source_build_artifact_id"] = int(required("GATEWAY_ARTIFACT_ID"))
    digests["gateway_source_build_artifact_digest"] = required("GATEWAY_ARTIFACT_DIGEST")
    digests["canary_source_build_artifact_id"] = int(required("CANARY_ARTIFACT_ID"))
    digests["canary_source_build_artifact_digest"] = required("CANARY_ARTIFACT_DIGEST")
    digests["otclient_source_build_artifact_id"] = int(required("OTCLIENT_ARTIFACT_ID"))
    digests["otclient_source_build_artifact_digest"] = required("OTCLIENT_ARTIFACT_DIGEST")
    digests["otclient_source_build_run"] = int(required("SOURCE_BUILD_RUN"))
    digests_path.write_text(json.dumps(digests, indent=2, sort_keys=True) + "\n", encoding="utf-8")


if __name__ == "__main__":
    install_exact_harness_adapter()
    exit_code = platform_runner.main()
    update_retained_evidence()
    sys.exit(exit_code)
