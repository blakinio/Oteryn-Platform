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

- [x] Every currently implemented functional surface has a durable matrix row with source/module, expected behavior, test-layer evidence, negative-path evidence, authorization evidence and production-smoke requirement.
- [x] Deferred/non-implemented features are explicitly separated from delivered functionality; no existing-account claim/import, character deletion or rename is inferred.
- [x] Existing exact-SHA repository and production-like staging evidence is mapped to the matrix without treating feature tests as live user/admin E2E when they are not.
- [x] Critical cross-surface Identity/account/character/public and administrator/RBAC/CMS/audit flows are classified accurately for staging E2E coverage.
- [x] Infrastructure failure paths and security behavior are mapped to exact evidence or left `UNKNOWN`.
- [x] Any missing critical staging E2E, authorization, failure-path or data-integrity coverage is recorded as the smallest bounded follow-up work, prioritizing security/auth/data integrity.
- [x] A minimal final-production smoke checklist remains separate from staging validation and does not claim `PRODUCTION_PROVEN` before execution on the final deployment.
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
  - issue #68 live Identity/MFA/session/CSRF staging E2E
  - issue #69 live provisioning/binding/character/public staging E2E
  - issue #70 live admin/RBAC/CMS/audit staging E2E
  - issue #71 Platform DB outage production-like validation
  - issue #72 focused CMS transition and audit-secret regressions
cross_repository_tasks:
  - authoritative Platform game-login bridge remains outside this validation scope and separately authorized if required for launch
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T00:23:00+02:00
head: 51da44230a4a58ced621a8c6fee2ca45d3e66157
branch: task/OTERYN-20260721-functional-acceptance-validation
pr: 66
status: validating
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
  - main baseline is 221a13f6d7fba28ba765d67594a5cce4bf9523c4 after documentation-only PR #64.
  - PR #63 proved the controlled production-like environment and exact-SHA regression/dependency-boundary composition as STAGING_PROVEN, but its running-release HTTP smoke is limited to health, home, login, headers, cookies, request correlation, structured logging and representative 404 error behavior.
  - The Phase 7 regression step swaps to APP_ENV=testing with SQLite, array mail, array sessions and array cache, so it is not a live production-like HTTP E2E proof for critical user/admin business flows.
  - Current Identity, MFA, provisioning/binding, character creation, public-data, RBAC, CMS and audit behavior has strong directly inspected feature/unit/integration coverage recorded in docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md.
  - Provisioning and character-create least-privilege/data-integrity contracts have real MariaDB integration coverage, including idempotency/forward recovery and character concurrency races.
  - The full current delivered functional surface and explicitly deferred non-features are inventoried in docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md.
  - The final production-only smoke boundary is separately recorded in docs/testing/PRODUCTION_SMOKE_CHECKLIST.md and all production items remain UNKNOWN.
  - Follow-up issues #68 through #72 capture the smallest identified acceptance gaps without mixing application fixes into PR #66.
  - Open PR #65 owns Phase 7/go-live status documents including ACTIVE_WORK, PROJECT_STATE, ROADMAP and PRODUCTION_LIKE_VALIDATION_EVIDENCE; PR #66 remains path-disjoint.
derived:
  - Existing evidence is strong for repository feature/integration coverage and dependency boundaries but insufficient to classify every critical user/admin flow as live staging E2E proven.
  - Functional validation cannot be declared fully STAGING_PROVEN until issues #68, #69 and #70 are closed with exact-SHA production-like live-flow evidence; issue #71 is additionally required for the requested Platform DB failure path.
unknown:
  - exact-head PR #66 CI, Agent Governance and production-like validation results after the final documentation commits
conflicts: []
first_failure:
  marker: critical live staging E2E composition
  evidence: Phase 7 workflow executes the critical regression suite under APP_ENV=testing with SQLite and array mail/session/cache, while the running release smoke does not execute registration, password recovery, MFA, account provisioning, character creation, RBAC/CMS or audit flows end-to-end over HTTP
rejected_hypotheses:
  - Passing composer test alone proves production-like user/admin E2E: rejected because the workflow explicitly swaps to testing-only dependencies for that step.
  - Live health/header smoke proves critical business flows: rejected because those requests do not exercise the critical mutation/authentication workflows.
  - Existing strong MariaDB privilege/integration evidence should be repeated as a broad application fix in this inventory PR: rejected; gaps are isolated into bounded follow-up issues.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-functional-acceptance-validation.md
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - docs/testing/PRODUCTION_SMOKE_CHECKLIST.md
validation:
  - command: live GitHub preflight against main, active checkpoint, PR #63 workflow/evidence and open PR ownership
    result: PASS
    evidence: main advanced from the prior Phase 7 checkpoint only by documentation; PR #65 owns overlapping status/evidence paths and PR #66 is path-disjoint
  - command: source/test inventory of current routes and critical Identity, MFA, provisioning, character, public data, RBAC, CMS, audit and operations tests
    result: PASS
    evidence: durable matrix records strongest directly inspected evidence and explicit UNKNOWN gaps without promotion to PRODUCTION_PROVEN
blockers:
  - exact-head GitHub validation pending inspection
next_action: Inspect PR #66 exact-head CI, Agent Governance and production-like validation checks; fix only documentation/governance defects owned by this task, then record final validation status.
```

## Notes

`docs/agents/ACTIVE_WORK.md` is intentionally not edited while open PR #65 owns that path. This task must not relabel existing `STAGING_PROVEN` evidence as `PRODUCTION_PROVEN`.
