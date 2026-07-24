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

- [ ] Runtime Compose does not require repository checkout paths to exist on the Synology host.
- [ ] Repository-owned TLS and nginx bootstrap files are transferred through the Docker API into a named volume before services start.
- [ ] Health checks probe service network namespaces rather than the runner container loopback.
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
blockers: []
cross_repository_tasks: []
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T05:40:00Z
head: 51e7bfc21d493a6ca15591ce4ea2a78158c7b7d5
branch: fix/OTERYN-20260724-synology-runner-container-boundary
pr: pending
status: implementing
context_routes:
  - agent-governance
  - architecture
  - testing
  - security
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260724-synology-runner-container-boundary.md
  - deploy/synology/compose.yml
  - deploy/synology/scripts/deploy.sh
  - deploy/synology/scripts/health-check.sh
  - .github/workflows/build-synology-staging-images.yml
proven:
  - the repository-scoped Synology runner is online in a Container Manager container and listens for jobs
  - runtime Compose currently contains repository-relative bind mounts resolved by the Synology Docker daemon rather than the runner filesystem
  - current host-loopback health probes resolve inside the runner container rather than the Synology host
derived:
  - bootstrap files must cross the Docker socket through docker cp or be embedded in images
  - health probes should run in each service network namespace
unknown: []
conflicts: []
first_failure:
  marker: pre-deployment architecture review
  evidence: runtime bind sources and 127.0.0.1 probes are not reachable from the containerized runner boundary
rejected_hypotheses:
  - expose staging ports on all interfaces solely for runner health checks: rejected because it weakens the staging boundary
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-synology-runner-container-boundary.md
validation:
  - command: not-run
    result: NOT_RUN
    evidence: implementation starting
blockers: []
next_action: replace runtime bind mounts with a Docker-API-populated named volume and update service-local health probes.
```
