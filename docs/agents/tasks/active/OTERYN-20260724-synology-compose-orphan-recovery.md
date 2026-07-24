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
  - private issue 163 for sanitized operational evidence
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T23:45:00+02:00
head: 77e7a99a2c66d08a7ddd87be9f6740b81d5ad040
branch: fix/OTERYN-20260724-synology-compose-repair-evidence
pr: 164
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
  - PR 162 added bounded label-verified docker rename recovery and merged as a9498dacd8a18b51f1bfd940f61796ca76bb393e.
  - Exact PR 162 implementation head c2c6b73ee4dedbd7b41e5fb72d90d6c42c8c74d7 passed CI 30127654178, Agent Governance 30127654186, Platform DB Outage Validation 30127654225, Phase 7 Production-Like Validation 30127654182 and Game Auth Ticket Concurrency 30127654171.
  - Exact PR 162 final head 604d1c26c253cb5086c240630057ed893c1bd987 passed CI 30127928095, Agent Governance 30127928088, Platform DB Outage Validation 30127928075, Phase 7 Production-Like Validation 30127928076 and Game Auth Ticket Concurrency 30127928081.
  - Private issue 163 exists as a sanitized connector-readable repair evidence channel.
derived:
  - Platform and Canary are stale Compose replacement containers left under temporary short-ID-prefixed names.
  - Renaming verified running containers to their canonical names avoids data loss, restart and unrelated workload interruption.
  - Publishing only repaired service names, running-state summary and the public OAuth client id does not expose deployment secrets.
unknown:
  - whether the first main push repair already renamed the live candidates
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
  - command: PR 162 exact-head repository checks
    result: PASS
    evidence: all required CI, governance, outage, Phase 7 and concurrency checks passed before squash merge a9498dacd8a18b51f1bfd940f61796ca76bb393e.
  - command: PR 164 exact-head repository checks
    result: NOT_RUN
    evidence: sanitized issue reporting workflow change is awaiting exact-head CI and governance validation.
blockers:
  - none
next_action: Validate and merge PR 164, then read private issue 163 for the sanitized live repair result and public OAuth client id.
```
