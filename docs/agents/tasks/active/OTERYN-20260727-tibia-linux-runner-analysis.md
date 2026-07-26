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

- [x] Workflow executes on runner label `oteryn-staging`.
- [ ] Official Linux launcher is downloaded over HTTPS and run under Xvfb with an isolated HOME.
- [ ] Downloaded and installed bytes remain only in the dedicated Docker volume `oteryn-tibia-linux-analysis` on Synology.
- [ ] Workflow reports whether a separate game-client ELF was installed and records hashes, ELF metadata, dynamic dependencies and selected authentication/BattlEye indicators.
- [x] No CipSoft binaries, archives, assets, credentials, cookies or session data are committed or uploaded as GitHub artifacts.
- [x] No existing Oteryn staging container, network port, database, volume or secret is touched.

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
updated_at: 2026-07-26T22:42:00Z
head: 2dbd537d3fc41b2d0d9bc86e7b0b89c48bb7804c
branch: ci/OTERYN-20260727-tibia-linux-runner-analysis
pr: 218
status: validating
context_routes:
  - testing
owned_paths:
  - .github/workflows/tibia-linux-runner-analysis.yml
  - docs/agents/tasks/active/OTERYN-20260727-tibia-linux-runner-analysis.md
proven:
  - The Synology runner is registered in blakinio/Oteryn-Platform.
  - Existing deployment workflow uses runs-on oteryn-staging.
  - Workflow run 30223479545 executed on runner oteryn-synology-staging with host Docker access.
  - Docker reports Synology NAS on x86_64.
  - Host path /var/lib/oteryn-staging-state does not exist on the Docker host.
  - No proprietary binary is uploaded as a GitHub artifact by this workflow.
derived:
  - A dedicated Docker named volume avoids relying on an unknown Synology host path and remains isolated from existing staging volumes.
  - Running the launcher from its extracted directory matches the expected relative-resource layout.
unknown:
  - Whether the launcher automatically installs the current game client without UI interaction on Synology.
  - Exact location and filename of the installed Linux game-client ELF.
conflicts: []
first_failure:
  marker: invalid mount config for type bind
  evidence: workflow run 30223479545 job 89849940050; bind source /var/lib/oteryn-staging-state does not exist
rejected_hypotheses:
  - The runner was initially targeted from blakinio/otclient; it is repository-scoped to blakinio/Oteryn-Platform.
  - The deploy workflow default state path was assumed to exist physically on the Docker host; runtime evidence disproved it.
changed_paths:
  - .github/workflows/tibia-linux-runner-analysis.yml
  - docs/agents/tasks/active/OTERYN-20260727-tibia-linux-runner-analysis.md
validation:
  - command: workflow run 30223479545
    result: FAIL
    evidence: exact bind-source-path failure captured in job 89849940050
  - command: workflow patched to Docker named volume
    result: PENDING
    evidence: head 2dbd537d3fc41b2d0d9bc86e7b0b89c48bb7804c
blockers:
  - none
next_action: Inspect the named-volume workflow run and analyze the installed Linux client or its exact launcher failure.
```

## Notes

The workflow is operational-only and must not be merged with downloaded client data. Only concise text evidence may appear in GitHub logs.