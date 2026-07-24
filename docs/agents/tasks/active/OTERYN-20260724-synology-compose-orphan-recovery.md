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
- [x] Prefixed Platform and Canary replacement names are absent and canonical Compose names exist.
- [x] Platform, Gateway and Canary are running after repair.
- [ ] The public native OAuth client id is captured without terminal punctuation as sanitized non-secret evidence for the Windows staging client.
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
updated_at: 2026-07-25T00:35:00+02:00
head: d0916f969db980295b10c6eaa62bd93cb3ddacdd
branch: fix/OTERYN-20260724-synology-oauth-id-punctuation
pr: 167
status: ready
context_routes:
  - agent-governance
  - testing
  - ci-repair
owned_paths:
  - .github/workflows/repair-synology-compose-orphans.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-compose-orphan-recovery.md
  - docs/agents/tasks/archive/OTERYN-20260724-synology-compose-orphan-recovery.md
proven:
  - Initial DSM evidence showed Platform as 697925a1a27e_oteryn-staging-platform-1 and Canary as 1746ad006c25_oteryn-staging-canary-1 with Container undefined errors.
  - Gateway reverse proxy http://synology:8089/ready returned status ready before repair.
  - PR 162 added bounded label-verified docker rename recovery and merged as a9498dacd8a18b51f1bfd940f61796ca76bb393e after all required checks passed.
  - PR 164 added sanitized issue reporting and merged as 63c7d7e9b6dc2584919be0fadd64f68f8d7ef0d1 after all required checks passed.
  - Synology execution 30128638979 reached Docker access and failed inside the combined repair/OAuth step without publishing secrets.
  - PR 166 separated repair verification from OAuth extraction and merged as e9cb018d609f2bb5064370a5f850ddd091083258 after all required checks passed.
  - Synology execution 30129145948 reported name repair verification success, repaired services none, and canonical platform, canary and gateway running.
  - Execution 30129145948 proved no prefixed candidates remained when the separated workflow ran, so an earlier bounded run had already restored the canonical names.
  - Execution 30129145948 read the public OAuth client value but included the Laravel sentence-ending period because the bounded character class allowed a trailing dot.
  - PR 167 changes only the parser so dots must separate non-empty identifier segments; a local exact-message check returned 019f93b3-5e7e-721c-bc23-7e22e4bee7cc without punctuation.
  - Exact PR 167 implementation head d0916f969db980295b10c6eaa62bd93cb3ddacdd passed CI 30129307622, Agent Governance 30129307586, Platform DB Outage Validation 30129307581, Phase 7 Production-Like Validation 30129307588 and Game Auth Ticket Concurrency 30129307552.
derived:
  - Docker rename repaired the Container Manager-visible names without restarting runtime containers or touching named volumes.
  - The stored OAuth client id is expected to be 019f93b3-5e7e-721c-bc23-7e22e4bee7cc, pending one final runner publication with the corrected parser.
unknown:
  - final corrected runner publication of the public OAuth client id
conflicts: []
first_failure:
  marker: synology-container-manager-undefined-container
  evidence: DSM could not attach to the prefixed Platform container and reported Container undefined does not exist.
rejected_hypotheses:
  - restart the entire Container Manager package: rejected because it would interrupt unrelated containers including liquid20-collector
  - delete and recreate containers: rejected because verified docker rename repaired the names without downtime
  - delete Docker volumes: rejected because the defect was container metadata/name state, not persistent data
changed_paths:
  - .github/workflows/repair-synology-compose-orphans.yml
  - docs/agents/tasks/active/OTERYN-20260724-synology-compose-orphan-recovery.md
validation:
  - command: PR 162, PR 164 and PR 166 exact-head repository checks
    result: PASS
    evidence: each PR passed required CI, governance, outage, Phase 7 and concurrency checks before squash merge.
  - command: Synology repair execution 30128638979
    result: FAIL
    evidence: combined repair/OAuth step failed while Docker access and sanitized reporting passed.
  - command: Synology separated execution 30129145948
    result: PASS
    evidence: repair verification and OAuth read both succeeded; canonical platform, canary and gateway were running, but displayed OAuth evidence retained terminal punctuation.
  - command: local bounded parser check against observed formatted Laravel message
    result: PASS
    evidence: corrected expression returned 019f93b3-5e7e-721c-bc23-7e22e4bee7cc without the sentence-ending period.
  - command: CI 30129307622 on d0916f969db980295b10c6eaa62bd93cb3ddacdd
    result: PASS
    evidence: Composer validation/audit, formatting, static analysis and tests passed.
  - command: Agent Governance 30129307586 on d0916f969db980295b10c6eaa62bd93cb3ddacdd
    result: PASS
    evidence: checkpoint and workflow governance passed.
  - command: Platform DB Outage Validation 30129307581 on d0916f969db980295b10c6eaa62bd93cb3ddacdd
    result: PASS
    evidence: fail-closed database outage validation passed.
  - command: Phase 7 Production-Like Validation 30129307588 on d0916f969db980295b10c6eaa62bd93cb3ddacdd
    result: PASS
    evidence: production-like validation passed.
  - command: Game Auth Ticket Concurrency 30129307552 on d0916f969db980295b10c6eaa62bd93cb3ddacdd
    result: PASS
    evidence: ticket concurrency validation passed.
blockers:
  - none
next_action: Squash-merge PR 167, then verify the corrected OAuth client id published to issue 163.
```
