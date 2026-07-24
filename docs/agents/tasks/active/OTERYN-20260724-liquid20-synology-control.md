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

- [ ] A reviewed workflow builds an immutable Liquid20 image from the exact approved Freqtrade commit and publishes it to GHCR.
- [ ] The workflow deploys acceptance mode only when it will not interrupt an already running collector.
- [ ] Scheduled monitoring reports container state and bounded logs without restarting the collector.
- [ ] A completed run is copied from the Synology bind mount and uploaded once as a GitHub Actions artifact.
- [x] The collector receives no exchange keys, trading credentials, Docker socket, inbound ports or restart policy.
- [ ] The exact workflow is exercised on the `oteryn-staging` runner and the resulting state is recorded.

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
updated_at: 2026-07-24T15:45:00Z
head: f0b1b204edd6fb9ea0d2cae44f5ae7ada42f41ba
branch: fix/OTERYN-20260724-liquid20-status-board
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
  - PR 147 merged the reviewed runner control plane to main as 52e249d74f462ea17345b0dd1aea89fdd5da3acb.
  - Its exact final head passed Liquid20 Synology Control run 30105544815, CI run 30105545051, Agent Governance run 30105545025, Phase 7 run 30105544846, DB outage run 30105544936 and concurrency run 30105544975.
  - The existing Synology runner is registered with label oteryn-staging and has host Docker access while the Liquid20 container does not.
  - Freqtrade commit c00a091c5adc67cf75c46db5805e358ffc72fad7 contains the reviewed Liquid20 image and data-only entrypoint.
  - Issue 148 exists as a fixed, non-secret status board.
  - The follow-up publisher limits the issue body to container state, image, run ID, timestamps, operation outcome and Actions run URL.
  - No logs, secrets or raw liquidation data are published to the issue.
derived:
  - A fixed issue updated by the trusted runner gives connector-readable visibility without direct DSM or SSH access.
  - Re-running bootstrap after the status-board merge is safe because the control script preserves an already running collector.
unknown:
  - The current result of the first post-merge bootstrap from PR 147 is not discoverable through the available connector action, which lists pull-request runs only.
  - Whether the Oteryn repository token can publish the Liquid20 package and update issue 148 will be proven by the next trusted-main bootstrap.
  - Whether current Actions storage quota permits the final artifact upload; a failed upload remains retryable and does not alter run evidence.
conflicts: []
first_failure:
  marker: no-connector-readable-runtime-status
  evidence: runner workflow exists, but the available connector cannot enumerate push and scheduled workflow runs by itself
rejected_hypotheses:
  - Direct assistant access to DSM or the container: no such connection is available.
  - Publish bounded logs or event data to issue 148: rejected because the board must remain non-secret metadata only.
  - Replace a running acceptance container during the status-board rollout: rejected; bootstrap must preserve it.
changed_paths:
  - .github/workflows/liquid20-synology-control.yml
  - deploy/liquid20/publish-status.sh
  - deploy/liquid20/README.md
  - docs/agents/tasks/active/OTERYN-20260724-liquid20-synology-control.md
validation:
  - command: PR 147 merge-gate suite
    result: PASS
    evidence: all six exact-head workflows passed before merge
  - command: status-board follow-up validation
    result: NOT_RUN
    evidence: implementation branch not yet opened as a PR
blockers:
  - none
next_action: Open and validate the status-board follow-up PR, merge it, then read issue 148 to verify GHCR publication and the actual Synology container state.
```

## Notes

This task does not modify Freqtrade strategy, execution, DCA, leverage or protected holdout behavior. It operates only on public liquidation market-data collection.
