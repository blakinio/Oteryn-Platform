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
- [ ] The collector receives no exchange keys, trading credentials, Docker socket, inbound ports or restart policy.
- [ ] The exact workflow is exercised on the `oteryn-staging` runner and the resulting state is recorded.

## Ownership

```yaml
owned_paths:
  - .github/workflows/liquid20-synology-control.yml
  - deploy/liquid20/synology-control.sh
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
blockers: []
cross_repository_tasks:
  - blakinio/freqtrade is read-only in this task; its exact approved commit is consumed as image build input
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T15:10:00Z
head: UNKNOWN
branch: feat/OTERYN-20260724-liquid20-synology-control
pr: none
status: implementing
context_routes:
  - testing
  - security
  - architecture
owned_paths:
  - .github/workflows/liquid20-synology-control.yml
  - deploy/liquid20/synology-control.sh
  - deploy/liquid20/README.md
  - docs/agents/tasks/active/OTERYN-20260724-liquid20-synology-control.md
  - docs/agents/tasks/archive/OTERYN-20260724-liquid20-synology-control.md
proven:
  - The existing Synology runner is registered to blakinio/Oteryn-Platform with label oteryn-staging and has the host Docker socket mounted.
  - Existing Oteryn deployment workflow uses runs-on oteryn-staging and validates Docker, Compose, curl, Python, sha256sum and timeout on the runner.
  - The user-provided Liquid20 smoke artifacts prove both exchange clocks synchronized, both collectors completed, and no trading credentials were present.
  - Freqtrade commit c00a091c5adc67cf75c46db5805e358ffc72fad7 contains the reviewed Liquid20 Dockerfile and data-only entrypoint.
derived:
  - The Oteryn runner can control a sibling Liquid20 container through the host Docker daemon without granting the Liquid20 container Docker access.
  - A GHCR image plus runner workflow removes the fragile DSM inline-command deployment path.
unknown:
  - Whether the Oteryn repository GITHUB_TOKEN can publish the new ghcr.io/blakinio/liquid20-collector package.
  - Whether a Liquid20 container is currently running on Synology when the first control workflow executes.
conflicts: []
first_failure:
  marker: none
  evidence: implementation not yet executed on the self-hosted runner
rejected_hypotheses:
  - Direct assistant access to DSM or the container: no such connection is available; visibility must be mediated through GitHub Actions.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-liquid20-synology-control.md
validation:
  - command: not-run
    result: NOT_RUN
    evidence: implementation in progress
blockers:
  - none
next_action: Add the bounded runner control script and workflow, then open a draft PR for repository validation.
```

## Notes

This task does not modify Freqtrade strategy, execution, DCA, leverage or protected holdout behavior. It operates only on public liquidation market-data collection.
