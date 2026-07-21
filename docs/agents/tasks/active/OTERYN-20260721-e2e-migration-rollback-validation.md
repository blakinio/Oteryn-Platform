---
task_id: OTERYN-20260721-e2e-migration-rollback-validation
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/adr/0008-risk-based-continuous-e2e-validation.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
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

- [ ] Reuse the existing Phase 7 release/deployment mechanism; do not create a competing deployment workflow.
- [ ] Establish a deterministic synthetic representative pre-upgrade dataset from repository-owned schema/contracts without production data.
- [ ] Build the representative dataset against the previous known-good `BASE_SHA` schema before applying candidate migrations.
- [ ] Upgrade the dataset with the exact candidate `VALIDATION_SHA` migrations and verify migration/schema/data invariants.
- [ ] Run bounded application smoke against the upgraded representative-data state.
- [ ] Exercise code rollback to `BASE_SHA` only through the existing controlled release-switch mechanism and verify expected old-code compatibility with the post-upgrade database where the migration contract supports it.
- [ ] Verify representative persisted data remains intact through rollback and redeploy of `VALIDATION_SHA`.
- [ ] Run bounded application smoke after rollback and after redeploy where deterministic and safe.
- [ ] Fail closed on migration, data-integrity, rollback-compatibility or smoke failure.
- [ ] Produce durable non-secret exact-SHA evidence with `STAGING_PROVEN` classification only.
- [ ] Keep issue #91 as the independent final production verification tracker and perform no production action.
- [ ] Perform no Canary/login-server repository writes.
- [ ] Update durable test strategy/roadmap/evidence and leave exactly one next action.

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
updated_at: 2026-07-21T15:15:00Z
head: d05d0b2270b5feabb0f64c23b7c60bf87a514276
branch: task/OTERYN-20260721-e2e-migration-rollback-validation
pr: none
status: investigating
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
  - issue 98 is the dedicated tracker for existing-data migration upgrade and controlled rollback validation
  - existing Phase 7 validation already creates exact-SHA and BASE_SHA release directories and switches /tmp/phase7-current by symlink
  - existing Phase 7 validation already validates clean candidate migrations backup restore rollback switch interrupted-release isolation and candidate redeploy
  - existing rollback currently switches old code onto the database after candidate migrations and runs production configuration plus migrate status but does not seed representative existing data or run bounded old-code application smoke
  - production verification remains issue 91 and no staging result may be promoted to PRODUCTION_PROVEN
  - no overlapping open migration rollback validation PR was found at task start
derived:
  - issue 98 should extend the current Phase 7 workflow with an isolated representative upgrade database rather than alter the production-like primary validation database
  - the representative upgrade slice must prove an invariant beyond current clean-deploy and backup-restore checks to avoid duplicate evidence
unknown:
  - smallest stable set of Platform tables/data that can be seeded on BASE_SHA and verified on both BASE_SHA and VALIDATION_SHA without coupling to incidental schema
  - whether existing factories/seeders can be reused directly against the BASE_SHA release or a purpose-built deterministic fixture is needed
  - exact bounded HTTP smoke that can run against both code versions while pointing to the isolated upgrade database
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - a second deployment workflow is required
  - production data or a copied production dump is acceptable test input
  - code-symlink rollback alone proves existing-data rollback compatibility
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260721-e2e-coverage-hardening.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-migration-rollback-validation.md
validation:
  - command: live repository issue PR and Phase 7 workflow inspection
    result: PASS
    evidence: issue 98 open; no overlapping open PR; existing Phase 7 rollback mechanism and evidence boundary inspected
blockers:
  - none
next_action: Identify the smallest representative BASE_SHA dataset and exact cross-version smoke assertions, then implement the isolated upgrade rollback slice in the existing Phase 7 workflow.
```

## Notes

This task extends continuous repository/staging validation only. It does not reopen Phase 7 engineering completion and does not authorize production deployment or cross-repository writes.
