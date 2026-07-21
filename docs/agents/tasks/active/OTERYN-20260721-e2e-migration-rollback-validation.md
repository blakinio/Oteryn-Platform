---
task_id: OTERYN-20260721-e2e-migration-rollback-validation
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/adr/0008-risk-based-continuous-e2e-validation.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/testing/E2E_MIGRATION_ROLLBACK_EVIDENCE.md
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
  - .github/workflows/phase7-production-like-validation.yml
search_first:
  - open PRs and active tasks overlapping phase7-production-like-validation.yml migration rollback or release validation
  - existing Phase 7 release rollback and backup restore mechanisms before adding orchestration
  - existing migrations factories seeders and acceptance fixtures before creating a representative upgrade dataset
optional_reads:
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
  - docs/testing/PRODUCTION_SMOKE_CHECKLIST.md
---

# OTERYN-20260721-e2e-migration-rollback-validation

## Goal

Close issue #98 with a bounded P0 existing-data upgrade/migration and controlled rollback/redeploy validation slice integrated into the existing Phase 7 production-like release harness. Use deterministic synthetic data only, prove data/schema survivability and bounded application behavior, and preserve the staging-versus-production evidence boundary.

## Acceptance criteria

- [x] Reuse the existing Phase 7 release/deployment mechanism; do not create a competing deployment workflow.
- [x] Establish a deterministic synthetic representative pre-upgrade dataset from repository-owned schema/contracts without production data.
- [x] Build the representative dataset against the previous known-good `BASE_SHA` schema before applying candidate migrations.
- [x] Upgrade the dataset with the exact candidate `VALIDATION_SHA` migrations and verify migration/schema/data invariants.
- [x] Run bounded application smoke against the upgraded representative-data state.
- [x] Exercise code rollback to `BASE_SHA` only through the existing controlled release-switch mechanism and verify expected old-code compatibility with the post-upgrade database where the migration contract supports it.
- [x] Verify representative persisted data remains intact through rollback and redeploy of `VALIDATION_SHA`.
- [x] Run bounded application smoke after rollback and after redeploy where deterministic and safe.
- [x] Fail closed on migration, data-integrity, rollback-compatibility or smoke failure.
- [x] Produce durable non-secret exact-SHA evidence with `STAGING_PROVEN` classification only.
- [x] Keep issue #91 as the independent final production verification tracker and perform no production action.
- [x] Perform no Canary/login-server repository writes.
- [x] Update durable test strategy/roadmap/evidence and leave exactly one next action.

## Ownership

```yaml
owned_paths:
  - .github/workflows/phase7-production-like-validation.yml
  - scripts/phase7/**
  - docs/testing/E2E_MIGRATION_ROLLBACK_EVIDENCE.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-migration-rollback-validation.md
  - docs/agents/tasks/archive/OTERYN-20260721-e2e-coverage-hardening.md
modules:
  - testing
  - ci
  - release-validation
  - database
dependencies:
  - issue #98
  - existing Phase 7 production-like release and rollback validation
  - issue #91 remains production-only and independent
blockers:
  - none
cross_repository_tasks:
  - none; Canary/login-server repositories remain read-only
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T15:31:00Z
head: fc8488996f582d2e728ecb1bfc3a3fdb2c3b3597
branch: task/OTERYN-20260721-e2e-migration-rollback-validation
pr: 99
status: ready
context_routes:
  - testing
  - architecture
  - database
  - ci-repair
  - agent-governance
owned_paths:
  - .github/workflows/phase7-production-like-validation.yml
  - scripts/phase7/**
  - docs/testing/E2E_MIGRATION_ROLLBACK_EVIDENCE.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-migration-rollback-validation.md
  - docs/agents/tasks/archive/OTERYN-20260721-e2e-coverage-hardening.md
proven:
  - PR 94 merged to main as 26ff602696c597aac0833415b0a47af5d427a52d after all required final-head checks passed
  - issue 98 is the dedicated tracker and PR 99 is the dedicated implementation PR for existing-data upgrade and controlled rollback validation
  - no overlapping open migration rollback validation PR was found at task start
  - existing Phase 7 validation already creates exact-SHA and BASE_SHA release directories and switches /tmp/phase7-current by symlink
  - the new slice extends that same release path instead of creating a competing deployment workflow
  - the representative upgrade database is isolated as oteryn_upgrade and does not mutate the primary Phase 7 validation schema
  - BASE_SHA migrations create the representative pre-upgrade schema before synthetic data is inserted
  - the synthetic representative dataset is one disposable Platform Identity plus one published Platform-owned news row and contains no production data or reusable user credential
  - an in-memory non-secret fingerprint covers representative Identity/news state and is never persisted in durable evidence
  - exact VALIDATION_SHA migrations are applied to the existing BASE_SHA-created dataset before candidate smoke
  - bounded smoke uses health plus public published-news HTTP behavior on candidate rollback and redeploy releases
  - rollback switches the existing /tmp/phase7-current symlink to BASE_SHA while retaining the post-upgrade database and therefore tests old-code compatibility with candidate-migrated persisted state
  - candidate redeploy reruns migrations idempotently and repeats bounded HTTP smoke before returning the current-release symlink to VALIDATION_SHA
  - Phase 7 run 29844031564 job 88679862151 passed the integrated slice on validation SHA 45ce658f54cbbe78652b7e8710e0cd25c7e85a2a with rollback/base 26ff602696c597aac0833415b0a47af5d427a52d
  - run 29844031564 produced artifact phase7-production-like-evidence-29844031564 id 8500578323 digest sha256:d28fc7fc5511bcbcc30eae21842133c30a88a9fdfcd1136893472a88affdfadb
  - the existing-data evidence records base migration count 11 candidate migration count 11 existing-data upgrade PASS candidate smoke PASS rollback-code/post-upgrade-DB PASS rollback smoke PASS redeploy smoke PASS and dataset fingerprint preserved PASS
  - equal 11 to 11 migration counts are expected because PR 99 adds validation infrastructure rather than a schema migration; the harness does not fabricate a migration delta and future migration-bearing candidates use the same base-to-head path
  - the same implementation head passed required CI 29844030857 and Agent Governance 29844030513; Platform DB Outage validation remained independent
  - docs/testing/E2E_MIGRATION_ROLLBACK_EVIDENCE.md records exact-SHA evidence and the limits of the STAGING_PROVEN classification
  - docs/testing/E2E_COVERAGE_ROADMAP.md and docs/architecture/TEST_STRATEGY.md mark the P0 release migration/rollback slice as implemented while keeping P1/P2 work incremental
  - production verification remains issue 91 and no staging result is promoted to PRODUCTION_PROVEN
  - no Canary or login-server repository writes were performed
derived:
  - the slice adds unique evidence beyond clean deployment and backup restore because it proves persisted representative application data survives candidate migration command old-code rollback smoke and candidate redeploy smoke on the same database
  - future backward-incompatible or destructive migrations will fail this path when old-code rollback is unsafe, forcing an explicit rollout/rollback decision instead of silently claiming rollback compatibility
  - because PR 99 contains no production schema delta, the first passing run proves the mechanism and current release compatibility rather than a nonexistent migration transformation
unknown:
  - final required-check outcome on the documentation-updated current PR head
  - final production migration duration lock behavior provider rollback mechanics and production RTO/RPO until directly verified in production
  - compatibility of future destructive migrations until each concrete change is validated
conflicts: []
first_failure:
  marker: none
  evidence: first integrated existing-data upgrade rollback execution passed; no implementation failure required a product or harness workaround
rejected_hypotheses:
  - a second deployment workflow is required
  - production data or a copied production dump is acceptable test input
  - code-symlink rollback alone proves existing-data rollback compatibility
  - a schema delta should be fabricated in PR 99 solely to make the first migration counts differ
  - passing staging migration rollback validation can substitute for production deployment rollback evidence
changed_paths:
  - .github/workflows/phase7-production-like-validation.yml
  - scripts/phase7/validate-existing-data-upgrade.sh
  - docs/testing/E2E_MIGRATION_ROLLBACK_EVIDENCE.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-migration-rollback-validation.md
  - docs/agents/tasks/archive/OTERYN-20260721-e2e-coverage-hardening.md
validation:
  - command: live repository issue PR and Phase 7 workflow inspection
    result: PASS
    evidence: issue 98 and PR 99 dedicated; existing release switch backup restore and rollback mechanisms inspected before implementation
  - command: Phase 7 Production-Like Validation run 29844031564 on 45ce658f54cbbe78652b7e8710e0cd25c7e85a2a
    result: PASS
    evidence: integrated existing-data upgrade rollback interrupted-release and redeploy step passed; both non-secret evidence JSON files uploaded in artifact 8500578323
  - command: CI run 29844030857 on 45ce658f54cbbe78652b7e8710e0cd25c7e85a2a
    result: PASS
    evidence: required repository CI passed
  - command: Agent Governance run 29844030513 on 45ce658f54cbbe78652b7e8710e0cd25c7e85a2a
    result: PASS
    evidence: governance checks passed
blockers:
  - none
next_action: Verify all required checks on the documentation-updated current PR head and merge PR #99 only if the exact-head merge gate remains satisfied.
```

## Notes

This task extends continuous repository/staging validation only. It does not reopen Phase 7 engineering completion and does not authorize production deployment or cross-repository writes. The first evidence run has no schema-count delta because this PR adds the validation mechanism itself; that limitation is explicit rather than hidden.
