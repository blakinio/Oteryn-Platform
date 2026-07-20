---
task_id: OTERYN-20260721-functional-acceptance-validation
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
search_first:
  - current main routes, tests and production-like validation workflow for delivered functional surfaces
  - open PRs and active tasks for overlapping owned paths
optional_reads:
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/ROADMAP.md
  - docs/agents/PROJECT_STATE.md
---

# OTERYN-20260721-functional-acceptance-validation

## Goal

Inventory every currently delivered user, administrator and infrastructure-dependent functional surface on `main`; map each surface to unit/feature/integration/production-like staging/negative-path/authorization evidence; identify exact acceptance gaps without inflating existing staging evidence; and prepare the minimal final-production smoke checklist.

## Acceptance criteria

- [ ] Every currently implemented functional surface has a durable matrix row with source/module, expected behavior, test-layer evidence, negative-path evidence, authorization evidence and production-smoke requirement.
- [ ] Deferred/non-implemented features are explicitly separated from delivered functionality; no existing-account claim/import, character deletion or rename is inferred.
- [ ] Existing exact-SHA repository and production-like staging evidence is mapped to the matrix without treating feature tests as live user/admin E2E when they are not.
- [ ] Critical cross-surface Identity/account/character/public and administrator/RBAC/CMS/audit flows are classified accurately for staging E2E coverage.
- [ ] Infrastructure failure paths and security behavior are mapped to exact evidence or left `UNKNOWN`.
- [ ] Any missing critical staging E2E, authorization, failure-path or data-integrity coverage is recorded as the smallest bounded follow-up work, prioritizing security/auth/data integrity.
- [ ] A minimal final-production smoke checklist remains separate from staging validation and does not claim `PRODUCTION_PROVEN` before execution on the final deployment.
- [ ] Current branch validation and GitHub checks are recorded against exact SHAs before readiness.

## Ownership

```yaml
owned_paths:
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - docs/testing/PRODUCTION_SMOKE_CHECKLIST.md
  - docs/agents/tasks/active/OTERYN-20260721-functional-acceptance-validation.md
modules:
  - testing
  - Identity
  - Accounts
  - Characters
  - PublicGameData
  - Admin
  - CMS
  - PlatformOperations
dependencies:
  - main 221a13f6d7fba28ba765d67594a5cce4bf9523c4
  - PR #63 production-like staging evidence
  - PR #65 owns overlapping Phase 7/go-live status documents; this task must not edit those paths
blockers:
  - none
cross_repository_tasks:
  - authoritative Platform game-login bridge remains outside this validation scope and separately authorized if required for launch
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T00:20:00+02:00
head: 221a13f6d7fba28ba765d67594a5cce4bf9523c4
branch: task/OTERYN-20260721-functional-acceptance-validation
pr: none
status: investigating
context_routes:
  - testing
  - security
  - auth-identity
  - accounts-characters
  - public-game-data
  - admin-rbac
  - web-cms
  - database
  - canary-integration
  - agent-governance
owned_paths:
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - docs/testing/PRODUCTION_SMOKE_CHECKLIST.md
  - docs/agents/tasks/active/OTERYN-20260721-functional-acceptance-validation.md
proven:
  - main HEAD is 221a13f6d7fba28ba765d67594a5cce4bf9523c4 after documentation-only PR #64.
  - PR #63 previously proved the controlled production-like environment and full exact-SHA regression suite as STAGING_PROVEN, but its live web smoke is limited to health, headers, cookies, request correlation, structured logging and representative error behavior.
  - The current production-like workflow runs composer test with testing-only SQLite, array mail, array sessions and array cache rather than driving the full critical user/admin flows through the running production-like HTTP application.
  - Open PR #65 owns Phase 7/go-live status documents including ACTIVE_WORK, PROJECT_STATE, ROADMAP and PRODUCTION_LIKE_VALIDATION_EVIDENCE; this task uses disjoint owned paths.
derived:
  - Existing evidence is strong for repository feature/integration coverage and dependency boundaries but is insufficient by itself to classify every critical user/admin flow as live staging E2E proven.
unknown:
  - exact feature-by-feature acceptance gaps after complete source/test inventory
  - whether any critical delivered flow already has a live production-like HTTP E2E harness outside the Phase 7 workflow
conflicts: []
first_failure:
  marker: critical live staging E2E composition
  evidence: Phase 7 workflow executes the critical regression suite under APP_ENV=testing with SQLite and array mail/session/cache, while the running release smoke does not execute registration, password recovery, MFA, account provisioning, character creation, RBAC/CMS or audit flows end-to-end over HTTP
rejected_hypotheses:
  - Passing composer test alone proves production-like user/admin E2E: rejected because the workflow explicitly swaps to testing-only dependencies for that step.
  - Live health/header smoke proves critical business flows: rejected because those requests do not exercise the critical mutation/authentication workflows.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-functional-acceptance-validation.md
validation:
  - command: live GitHub preflight against main, active checkpoint, PR #63 workflow/evidence and open PR ownership
    result: PASS
    evidence: main advanced from the prior Phase 7 checkpoint only by documentation; PR #65 owns overlapping status/evidence paths and this task is path-disjoint
blockers:
  - none
next_action: Complete the source/test inventory for Identity, account provisioning, character creation, public game data, Admin/RBAC/CMS/audit and security/operations, then write the durable acceptance matrix with exact evidence and explicit gaps.
```

## Notes

`docs/agents/ACTIVE_WORK.md` is intentionally not edited while open PR #65 owns that path. This task must not relabel existing `STAGING_PROVEN` evidence as `PRODUCTION_PROVEN`.
