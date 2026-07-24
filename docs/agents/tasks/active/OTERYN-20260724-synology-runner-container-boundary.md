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
- [ ] Focused validation and repository CI pass on the final head.

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
blockers:
  - GitHub-hosted Actions jobs currently fail before any step starts; current connector evidence exposes no job log payload
cross_repository_tasks: []
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T06:02:00Z
head: e0cee8a19bccda473cf2a4b79df25aa5ba3b1efc
branch: fix/OTERYN-20260724-synology-runner-container-boundary
pr: 128
status: blocked
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
  - workflow run 30070806697 and its single failed-job rerun both completed with failure before any job step was created
derived:
  - the current CI failure is external to executed repository steps because no runner step began
unknown:
  - exact GitHub UI reason shown for the pre-step GitHub-hosted runner failures
conflicts: []
first_failure:
  marker: Build Synology Staging Images run 30070806697
  evidence: all four jobs concluded failure with steps absent; one rerun reproduced the same pre-step failure
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
  - command: Build Synology Staging Images run 30070806697
    result: BLOCKED
    evidence: initial attempt and failed-job rerun both failed before any step started; GitHub returned no downloadable job log
  - command: repository pull-request workflows on e0cee8a19bccda473cf2a4b79df25aa5ba3b1efc
    result: BLOCKED
    evidence: CI, governance, outage, phase-7 and concurrency jobs also failed before steps started
blockers:
  - inspect the GitHub Actions UI banner for a billing, policy, availability or hosted-runner allocation explanation
next_action: inspect the latest failed Build Synology Staging Images run in the GitHub UI and capture the pre-step failure banner.
```
