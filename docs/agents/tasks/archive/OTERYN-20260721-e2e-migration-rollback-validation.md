---
task_id: OTERYN-20260721-e2e-migration-rollback-validation
required_reads:
  - AGENTS.md
  - docs/architecture/adr/0008-risk-based-continuous-e2e-validation.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/testing/E2E_MIGRATION_ROLLBACK_EVIDENCE.md
---

# OTERYN-20260721-e2e-migration-rollback-validation

## Goal

Close issue #98 with a bounded P0 representative existing-data upgrade/migration and controlled rollback/redeploy validation slice integrated into the existing Phase 7 production-like release harness.

## Result

Completed and merged through PR #99 as `21d67c7e7edb533f9765ff96417f2ab2fbb1aea8`.

Delivered:

- isolated `oteryn_upgrade` MariaDB validation schema;
- synthetic non-production Identity + published-news representative dataset built on actual `BASE_SHA` migrations;
- exact-candidate migration execution against existing persisted data;
- persisted-data fingerprint validation without persisting the fingerprint or credential hash;
- bounded candidate `/health` and public-news HTTP smoke;
- old-code rollback smoke against the post-upgrade database through the existing `/tmp/phase7-current` release symlink;
- candidate redeploy plus idempotent migration and repeated smoke;
- separate non-secret `phase7-existing-data-upgrade-evidence.json` uploaded alongside existing Phase 7 evidence;
- durable test-strategy, roadmap and evidence updates.

The bootstrap implementation PR itself introduced no Platform schema migration, so its directly measured migration count was `11 -> 11`. This proves the validation mechanism and current base/head compatibility without fabricating a nonexistent schema delta. Future migration-bearing candidates traverse the same required base-to-head path.

## Final evidence

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T15:38:00Z
head: cddaebfb14e1235bcc00ca242ef82a8c49d84e0c
branch: task/OTERYN-20260721-e2e-migration-rollback-validation
pr: 99
status: ready
context_routes:
  - testing
  - architecture
  - database
  - ci-repair
  - agent-governance
proven:
  - issue 98 closed as completed after PR 99 merge
  - PR 99 squash-merged to main as 21d67c7e7edb533f9765ff96417f2ab2fbb1aea8
  - final PR head cddaebfb14e1235bcc00ca242ef82a8c49d84e0c passed CI run 29844629798
  - final PR head passed Agent Governance run 29844629714
  - final PR head passed Platform DB Outage Validation run 29844629691
  - final PR head passed Phase 7 Production-Like Validation run 29844629692 including the integrated existing-data upgrade rollback and redeploy step
  - first implementation evidence run 29844031564 job 88679862151 produced artifact 8500578323 digest sha256:d28fc7fc5511bcbcc30eae21842133c30a88a9fdfcd1136893472a88affdfadb
  - evidence classification remains STAGING_PROVEN only
  - issue 91 remains the independent production-only Production Go-Live Gate tracker
  - no Canary or login-server repository writes were performed
unknown:
  - final production migration duration lock behavior provider rollback mechanics and production RTO/RPO until direct production verification
  - compatibility of each future destructive or backward-incompatible migration until validated for that concrete change
conflicts: []
blockers:
  - none
next_action: Select the next bounded E2E hardening slice only if it adds unique evidence beyond existing browser Phase 7 outage feature and integration coverage; production-only work remains issue #91.
```

## Notes

This task adds repository/staging continuous verification and does not reopen historical Phase 7 completion. No result in this task is `PRODUCTION_PROVEN`.
