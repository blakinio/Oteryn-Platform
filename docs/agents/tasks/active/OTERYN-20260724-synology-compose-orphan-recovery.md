---
task_id: OTERYN-20260724-synology-compose-orphan-recovery
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/BUILD_TEST_MATRIX.md
search_first:
  - active tasks and open PRs touching deploy/synology or the oteryn-staging runner
  - existing Synology deployment and rollback scripts
optional_reads:
  - deploy/synology/README.md
---

# OTERYN-20260724-synology-compose-orphan-recovery

## Goal

Recover the live Synology staging stack from Docker Compose replacement containers left with short-ID-prefixed names that Synology Container Manager cannot manage, without restarting containers or touching persistent volumes.

## Acceptance criteria

- [x] Recovery detects names matching `<12-hex>_oteryn-staging-<service>-<index>`.
- [x] A candidate is changed only after exact Compose project/service labels are verified.
- [x] Canonical name collisions fail closed rather than overwriting another container.
- [x] Named volumes and unrelated containers, including `liquid20-collector`, are never removed or restarted.
- [ ] Current prefixed Platform and Canary containers are renamed to canonical Compose names.
- [ ] Platform, Gateway and Canary remain running after repair.
- [ ] The public native OAuth client id is captured as sanitized non-secret evidence for the Windows staging client.
- [ ] The one-shot push trigger is removed after successful execution; bounded manual repair remains available.

## Ownership

```yaml
owned_paths:
  - .github/workflows/repair-synology-compose-orphans.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-compose-orphan-recovery.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-compose-orphan-recovery.md
modules:
  - Synology staging deployment
  - Docker Compose recovery
  - self-hosted deployment runner boundary
dependencies:
  - existing oteryn-staging self-hosted runner
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T23:25:00+02:00
head: 05bbefba7b607069865c0700c82d34c090d68f8d
branch: fix/OTERYN-20260724-synology-compose-orphan-recovery
pr: 162
status: implementing
context_routes:
  - agent-governance
  - testing
  - ci-repair
owned_paths:
  - .github/workflows/repair-synology-compose-orphans.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-compose-orphan-recovery.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-compose-orphan-recovery.md
proven:
  - Synology shows running Platform as 697925a1a27e_oteryn-staging-platform-1 and Canary as 1746ad006c25_oteryn-staging-canary-1.
  - Synology Container Manager shows blank container ID/project metadata and reports Container undefined does not exist.
  - Gateway reverse proxy http://synology:8089/ready returns status ready.
  - The Compose file uses project name oteryn-staging and named persistent volumes.
  - PR 162 contains a bounded runner workflow that verifies exact Compose labels before using docker rename.
derived:
  - Platform and Canary are stale Compose replacement containers left under temporary short-ID-prefixed names.
  - Renaming verified running containers to their canonical names avoids data loss, restart and unrelated workload interruption.
unknown:
  - exact live container labels before runner-side execution
  - exact public native OAuth client id stored in the Platform database
conflicts: []
first_failure:
  marker: synology-container-manager-undefined-container
  evidence: DSM cannot attach/open terminal for the prefixed Platform container and reports Container undefined does not exist.
rejected_hypotheses:
  - restart the entire Container Manager package: rejected because it would interrupt unrelated containers including liquid20-collector
  - delete and recreate containers: rejected because an exact verified docker rename can repair names without downtime
  - delete Docker volumes: rejected because the defect is container metadata/name state, not persistent data
changed_paths:
  - .github/workflows/repair-synology-compose-orphans.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-compose-orphan-recovery.md
validation:
  - command: live Gateway /ready through Synology reverse proxy
    result: PASS
    evidence: user observed {"status":"ready"}
  - command: exact-head repository checks
    result: NOT_RUN
    evidence: PR 162 is open as draft and CI must validate the workflow/task head.
blockers:
  - none
next_action: Complete PR 162 exact-head checks, merge it, and inspect the automatic runner repair result before removing the one-shot push trigger.
```
