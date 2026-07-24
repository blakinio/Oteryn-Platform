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

Recover the live Synology staging stack from Docker Compose replacement containers left with short-ID-prefixed names that Synology Container Manager cannot manage, and make future deployments remove only verified stale replacement containers before recreating the canonical services.

## Acceptance criteria

- [ ] Deployment detects names matching `<12-hex>_oteryn-staging-<service>-<index>`.
- [ ] A candidate is removed only after exact Compose project/service labels are verified.
- [ ] Named volumes and unrelated containers, including `liquid20-collector`, are never removed.
- [ ] The current prefixed Platform and Canary containers are recreated under canonical Compose names.
- [ ] Platform, Gateway and Canary health checks pass after repair.
- [ ] The public native OAuth client id is captured as sanitized non-secret evidence for the Windows staging client.
- [ ] Temporary one-shot repair automation is removed after successful execution.

## Ownership

```yaml
owned_paths:
  - deploy/synology/scripts/deploy.sh
  - .github/workflows/repair-synology-compose-orphans.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-compose-orphan-recovery.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-compose-orphan-recovery.md
modules:
  - Synology staging deployment
  - Docker Compose recovery
  - self-hosted deployment runner boundary
dependencies:
  - existing oteryn-staging GitHub Environment and self-hosted runner
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T22:00:00+02:00
head: 9245fca6762918f7d2a230b8a7dfe231bd4b8131
branch: fix/OTERYN-20260724-synology-compose-orphan-recovery
pr: none
status: implementing
context_routes:
  - agent-governance
  - testing
  - ci-repair
owned_paths:
  - deploy/synology/scripts/deploy.sh
  - .github/workflows/repair-synology-compose-orphans.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-compose-orphan-recovery.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-compose-orphan-recovery.md
proven:
  - Synology shows running Platform as 697925a1a27e_oteryn-staging-platform-1 and Canary as 1746ad006c25_oteryn-staging-canary-1.
  - Synology Container Manager shows blank container ID/project metadata and reports Container undefined does not exist.
  - Gateway reverse proxy http://synology:8089/ready returns status ready.
  - The Compose file uses project name oteryn-staging and named persistent volumes.
derived:
  - Platform and Canary are stale Compose replacement containers left under temporary short-ID-prefixed names.
  - Targeted container recreation can preserve all named volumes and unrelated workloads.
unknown:
  - exact live container labels before runner-side inspection
  - exact public native OAuth client id stored in the Platform database
conflicts: []
first_failure:
  marker: synology-container-manager-undefined-container
  evidence: DSM cannot attach/open terminal for the prefixed Platform container and reports Container undefined does not exist.
rejected_hypotheses:
  - restart the entire Container Manager package: rejected because it would interrupt unrelated containers including liquid20-collector
  - delete Docker volumes: rejected because the defect is container metadata/name state, not persistent data
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-synology-compose-orphan-recovery.md
validation:
  - command: live Gateway /ready through Synology reverse proxy
    result: PASS
    evidence: user observed {"status":"ready"}
blockers:
  - none
next_action: Implement fail-closed stale replacement cleanup and a bounded one-shot repair workflow on the oteryn-staging runner.
```
