---
task_id: OTERYN-20260727-tibia-linux-runner-analysis
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/tasks/TASK_TEMPLATE.md
  - .github/workflows/deploy-synology-staging.yml
search_first:
  - oteryn-staging
  - synology staging workflow
optional_reads: []
---

# OTERYN-20260727-tibia-linux-runner-analysis

## Goal

Use the existing Synology self-hosted runner to install the official Tibia Linux package in an isolated Docker container and produce a text-only static-analysis report without modifying Oteryn staging services or redistributing proprietary binaries.

## Acceptance criteria

- [ ] Workflow executes on runner label `oteryn-staging`.
- [ ] Official Linux launcher is downloaded over HTTPS and run under Xvfb with an isolated HOME.
- [ ] Downloaded and installed bytes remain only under `/var/lib/oteryn-staging-state/tibia-linux-analysis` on the Synology host.
- [ ] Workflow reports whether a separate game-client ELF was installed and records hashes, ELF metadata, dynamic dependencies and selected authentication/BattlEye indicators.
- [ ] No CipSoft binaries, archives, assets, credentials, cookies or session data are committed or uploaded as GitHub artifacts.
- [ ] No existing Oteryn staging container, network port, database, volume or secret is touched.

## Ownership

```yaml
owned_paths:
  - .github/workflows/tibia-linux-runner-analysis.yml
  - docs/agents/tasks/active/OTERYN-20260727-tibia-linux-runner-analysis.md
modules:
  - GitHub Actions infrastructure
dependencies:
  - self-hosted runner oteryn-synology-staging
  - host Docker socket
  - outbound HTTPS/DNS from Synology
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-26T22:30:00Z
head: UNKNOWN
branch: ci/OTERYN-20260727-tibia-linux-runner-analysis
pr: none
status: implementing
context_routes:
  - testing
owned_paths:
  - .github/workflows/tibia-linux-runner-analysis.yml
  - docs/agents/tasks/active/OTERYN-20260727-tibia-linux-runner-analysis.md
proven:
  - The Synology runner is registered in blakinio/Oteryn-Platform.
  - Existing deployment workflow uses runs-on oteryn-staging.
  - The runner has access to the host Docker daemon.
  - The dedicated persistent state root is /var/lib/oteryn-staging-state/tibia-linux-analysis.
derived:
  - A Docker-isolated Xvfb run can install and inspect the Linux package without using staging application secrets.
unknown:
  - Whether the launcher automatically installs the current game client without UI interaction on Synology.
  - Exact location and filename of the installed Linux game-client ELF.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - The runner was initially targeted from blakinio/otclient; it is repository-scoped to blakinio/Oteryn-Platform.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260727-tibia-linux-runner-analysis.md
validation:
  - command: repository and runner workflow inspection
    result: PASS
    evidence: .github/workflows/deploy-synology-staging.yml uses runs-on oteryn-staging
blockers:
  - none
next_action: Add and trigger the isolated one-off workflow on the Synology runner.
```

## Notes

The workflow must remain operational-only and must not be merged with downloaded client data. Only concise text evidence may appear in GitHub logs.