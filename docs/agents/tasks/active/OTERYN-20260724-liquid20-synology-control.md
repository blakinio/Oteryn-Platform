---
task_id: OTERYN-20260724-liquid20-synology-control
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/agents/CONTEXT_HANDOFF.md
  - .github/workflows/deploy-synology-staging.yml
search_first:
  - active Synology runner and deployment workflow ownership
  - existing self-hosted runner labels and Docker control patterns
  - existing GHCR login and immutable image conventions
optional_reads: []
---

# OTERYN-20260724-liquid20-synology-control

## Goal

Use the existing `oteryn-staging` self-hosted GitHub Actions runner on Synology to build, publish, deploy, monitor and collect evidence from the data-only Freqtrade `liquid20-v1` liquidation collector without requiring manual DSM log or file transfer steps.

## Acceptance criteria

- [x] A reviewed workflow builds an immutable Liquid20 image from the exact approved Freqtrade commit and publishes it to GHCR.
- [ ] The workflow deploys acceptance mode only when it will not interrupt an already running collector.
- [ ] Scheduled monitoring reports container state and aggregate acceptance status without restarting the collector or automatically uploading artifacts.
- [ ] A completed run can be copied from the Synology bind mount and uploaded once after an explicit `collect` request.
- [x] The collector receives no exchange keys, trading credentials, Docker socket, inbound ports or restart policy.
- [x] The exact workflow has been exercised on the `oteryn-staging` runner and its failure state is recorded in issue `#148`.

## Ownership

```yaml
owned_paths:
  - .github/workflows/liquid20-synology-control.yml
  - deploy/liquid20/synology-control.sh
  - deploy/liquid20/publish-status.sh
  - deploy/liquid20/README.md
  - docs/agents/tasks/active/OTERYN-20260724-liquid20-synology-control.md
  - docs/agents/tasks/archive/OTERYN-20260724-liquid20-synology-control.md
modules:
  - Synology staging operations
  - GitHub Actions self-hosted runner control
  - Liquid20 data-only research collection
dependencies:
  - existing online self-hosted runner labeled oteryn-staging
  - read-only Freqtrade source commit c00a091c5adc67cf75c46db5805e358ffc72fad7
  - Synology host data path /volume1/docker/freqtrade-liquidations/data
  - fixed non-secret status issue 148
blockers: []
cross_repository_tasks:
  - blakinio/freqtrade is read-only in this task; its exact approved commit is consumed as image build input
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T16:35:00Z
head: 3e8edcd2873c932973699329ce2cce24b01b819a
branch: fix/OTERYN-20260724-liquid20-monitor-without-artifact
pr: none
status: implementing
context_routes:
  - testing
  - security
  - architecture
owned_paths:
  - .github/workflows/liquid20-synology-control.yml
  - deploy/liquid20/synology-control.sh
  - deploy/liquid20/publish-status.sh
  - deploy/liquid20/README.md
  - docs/agents/tasks/active/OTERYN-20260724-liquid20-synology-control.md
  - docs/agents/tasks/archive/OTERYN-20260724-liquid20-synology-control.md
proven:
  - PR 147 merged the reviewed runner control plane as 52e249d74f462ea17345b0dd1aea89fdd5da3acb after all required checks passed.
  - PR 149 merged the connector-readable issue 148 status board as 002241849c3009b9003f2887065a3230e9abf753.
  - PR 150 merged bounded stopped-container diagnostics as c6e3c21c54fb44091abf1a95650ac6c735ed858d.
  - Issue 148 proves the exact immutable GHCR image was built and launched by workflow run 30107992604.
  - That launch failed before collection with exit code 1 and diagnostic `mkdir: cannot create directory '/data': Permission denied`.
  - PR 151 proved explicit user 0:0 did not resolve the Synology bind-write failure.
  - PR 152 merged removal of Docker's root-filesystem read-only flag as 450ee3b44657a52c8d0f3cd679b1d8b2615bf25f after all exact-head checks passed.
  - The collector still retains `cap-drop ALL`, `no-new-privileges`, isolated tmpfs, no ports, no Docker socket, no credentials and restart=no.
  - The user's GitHub Actions storage quota is currently full; automatic hourly artifact upload is therefore not an acceptable monitoring dependency.
  - The current branch changes hourly schedule behavior from `monitor` to metadata-only `status` and keeps artifact upload behind explicit `collect`.
  - The current branch extends issue 148 with aggregate acceptance status, failed gate names, coverage counts and per-source metrics without publishing raw events.
derived:
  - Metadata-only hourly checks prevent stale smoke artifacts or a full Actions storage quota from delaying routine status observation.
  - Full immutable evidence can remain on the NAS and be collected later without weakening or mutating the acceptance result.
  - A final acceptance decision can be read from issue 148 even while artifact storage is unavailable because the issue publishes only the evaluator's aggregate report.
unknown:
  - Whether the post-PR-152 bootstrap has reached the self-hosted runner; issue 148 has not yet recorded a newer workflow run.
  - Whether removing `--read-only` resolves the Synology data bind behavior; this requires the next trusted-main bootstrap.
  - Whether the 24-hour immutable run will pass every frozen gate.
conflicts: []
first_failure:
  marker: synology-data-bind-unwritable
  evidence: issue 148 records `/data` permission failure for workflow run 30107992604; explicit root did not change the result
rejected_hypotheses:
  - Direct assistant access to DSM or the container: no such connection is available; visibility is mediated through GitHub Actions and issue 148.
  - User identity alone caused the bind failure: rejected because explicit user 0:0 failed identically.
  - Automatic hourly artifact upload is required for observability: rejected because aggregate status is sufficient and the NAS preserves full evidence.
  - Publish raw NDJSON or credentials to issue 148: rejected.
changed_paths:
  - .github/workflows/liquid20-synology-control.yml
  - deploy/liquid20/publish-status.sh
  - deploy/liquid20/README.md
  - docs/agents/tasks/active/OTERYN-20260724-liquid20-synology-control.md
validation:
  - command: PR 152 exact-head checks on 2a68b6a7a436ca9897aec65891c556eb08d09d25
    result: PASS
    evidence: Liquid20 validation, CI, Phase 7, DB outage and concurrency workflows all passed
  - command: bash -n deploy/liquid20/publish-status.sh
    result: PASS
    evidence: syntax validated before branch update
  - command: metadata-only monitoring PR checks
    result: NOT_RUN
    evidence: PR not opened yet
blockers:
  - none
next_action: Open and validate the metadata-only monitoring PR, merge it, then wait for the serialized trusted-main bootstrap and read issue 148 for the actual Synology runtime state.
```

## Notes

This task does not modify Freqtrade strategy, execution, DCA, leverage or protected holdout behavior. It operates only on public liquidation market-data collection.
