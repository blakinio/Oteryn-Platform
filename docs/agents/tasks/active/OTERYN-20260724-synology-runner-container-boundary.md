---
task_id: OTERYN-20260724-synology-runner-container-boundary
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - deploy/synology/README.md
search_first:
  - active Synology deployment tasks and open PR ownership
---

# OTERYN-20260724-synology-runner-container-boundary

## Goal

Make the Synology deployment package work when the GitHub Actions deployment runner itself runs inside Container Manager with only the Docker socket and persistent state path mounted.

## Acceptance criteria

- [x] Runtime Compose does not require repository checkout paths to exist on the Synology host.
- [x] Repository-owned TLS and nginx bootstrap files are transferred through the Docker API into a named volume before services start.
- [x] Health checks probe service network namespaces rather than the runner container loopback.
- [x] Focused validation and repository CI pass on the validated implementation head.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260724-synology-runner-container-boundary.md
  - deploy/synology/compose.yml
  - deploy/synology/scripts/deploy.sh
  - deploy/synology/scripts/health-check.sh
  - .github/workflows/build-synology-staging-images.yml
modules:
  - Synology staging deployment
dependencies:
  - merged Synology staging package PR #127
blockers: []
cross_repository_tasks: []
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T06:31:00Z
validated_head: e9bcc2eda5984f71bb5ac760af91586e86a35255
branch: fix/OTERYN-20260724-synology-runner-container-boundary
pr: 128
status: ready_to_merge
context_routes:
  - agent-governance
  - architecture
  - testing
  - security
  - ci-repair
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260724-synology-runner-container-boundary.md
  - deploy/synology/compose.yml
  - deploy/synology/scripts/deploy.sh
  - deploy/synology/scripts/health-check.sh
  - .github/workflows/build-synology-staging-images.yml
proven:
  - the repository-scoped Synology runner is online in a Container Manager container and listens for jobs
  - runtime Compose no longer contains repository checkout bind mounts
  - TLS and nginx bootstrap files are staged through docker cp into a named volume owned by the Synology Docker daemon
  - service health probes use target container network namespaces rather than runner loopback
  - PR 128 contains only the bounded Synology runner-boundary repair paths
  - repository visibility was restored to public for the active development and CI phase
  - all six pull-request workflows completed successfully on validated head e9bcc2eda5984f71bb5ac760af91586e86a35255
unknown: []
conflicts: []
first_failure:
  marker: GitHub-hosted jobs were blocked before runner allocation while the repository was private
  evidence: GitHub UI reported failed account payments or an insufficient spending limit
resolution:
  - repository visibility was changed back to public for the development phase
  - failed workflow jobs were rerun and completed successfully
rejected_hypotheses:
  - expose staging ports on all interfaces solely for runner health checks: rejected because it weakens the staging boundary
  - run ordinary CI on the Synology self-hosted deployment runner: rejected because it violates the no-build-on-NAS and trusted-runner boundary
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-synology-runner-container-boundary.md
  - deploy/synology/compose.yml
  - deploy/synology/scripts/deploy.sh
  - deploy/synology/scripts/health-check.sh
  - .github/workflows/build-synology-staging-images.yml
validation:
  - command: Build Synology Staging Images run 30070992698
    result: PASS
    evidence: validation plus all three image build jobs completed successfully after rerun
  - command: CI run 30070992736
    result: PASS
  - command: Agent Governance run 30070992760
    result: PASS
  - command: Phase 7 Production-Like Validation run 30070992747
    result: PASS
  - command: Game Auth Ticket Concurrency run 30070992716
    result: PASS
  - command: Platform DB Outage Validation run 30070992757
    result: PASS
blockers: []
next_action: merge PR 128, wait for main image publication, then run the guarded Synology staging deployment workflow.
```
