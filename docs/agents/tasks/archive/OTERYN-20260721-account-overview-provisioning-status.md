---
task_id: OTERYN-20260721-account-overview-provisioning-status
required_reads:
  - AGENTS.md
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md
  - docs/acceptance/VISUAL_UX_ACCEPTANCE_MATRIX.md
search_first:
  - app/Accounts/**
  - app/Http/Controllers/**
  - resources/views/identity/**
  - routes/web.php
  - scripts/acceptance/**
optional_reads:
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/TEST_STRATEGY.md
---

# OTERYN-20260721-account-overview-provisioning-status

## Goal

Deliver issue #81 as a bounded authenticated Account Overview plus provisioning-status read model/controller and presentation surface. Use only Platform-owned authoritative binding state, preserve existing provisioning/binding semantics and security boundaries, and close the remaining missing-surface Visual/UX launch gap without changing Canary schema or write contracts.

## Acceptance criteria

- [x] Authenticated `Account Overview` route exists and is the primary account-shell landing entry.
- [x] Overview derives provisioning/binding state only from Platform-owned `identity_canary_accounts` persistence.
- [x] Ready, pending/in-progress, recoverable dependency-failure and hard-conflict states have explicit safe user-facing presentation.
- [x] No raw Canary account identifiers, provisioning names, sink credentials, tokens, hashes or internal exception details are exposed.
- [x] Retry is offered only for existing contract-authorized pending/recoverable provisioning state and reuses the existing idempotent `ProvisionCanaryAccount` action.
- [x] Conflict state remains fail-closed and provides support/operator guidance without self-service rebind, unlink or replacement-account creation.
- [x] Account Overview links coherently to MFA/security, password change and character creation when permitted.
- [x] Public and identity account navigation expose Account Overview as the authenticated account entry point.
- [x] Unauthenticated access is denied by existing authentication middleware.
- [x] Focused feature tests cover ready, pending, recoverable failure, conflict, retry success/failure, missing binding safe state and authorization denial.
- [x] Production-like browser acceptance covers desktop/mobile overview/status variants and no new measured overflow/accessibility blocker is introduced.
- [x] Aggregate broader Visual/UX launch gate is reclassified only from production-like browser evidence; production-only verification remains separate.

## Ownership

```yaml
owned_paths:
  - app/Accounts/ReadModels/**
  - app/Http/Controllers/Accounts/**
  - resources/views/identity/account/**
  - resources/views/identity/layout.blade.php
  - resources/views/game/layout.blade.php
  - routes/web.php
  - tests/Feature/Accounts/**
  - scripts/acceptance/tests/**
  - scripts/acceptance/seed.php
  - scripts/acceptance/seed-account-overview-state.php
  - docs/acceptance/VISUAL_UX_ACCEPTANCE_MATRIX.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-account-overview-provisioning-status.md
  - docs/agents/tasks/archive/OTERYN-20260721-account-overview-provisioning-status.md
modules:
  - AccountsCharacters
  - IdentityPresentation
  - AcceptanceTesting
dependencies:
  - issue #81
  - merged PR #33 Platform Canary account provisioning and immutable binding
  - merged PR #41 character creation through ready binding
  - merged PR #77 delivered-surface UI/UX remediation
blockers: []
cross_repository_tasks: []
```

## Explicit non-goals

- No Canary schema or write-contract changes.
- No provisioning saga, idempotency, binding ownership or conflict semantics changes.
- No self-service unlink, rebind, transfer, existing-account import/claim or replacement-account creation.
- No authentication/session/MFA policy changes.
- No RBAC authorization changes.
- No production deployment or production smoke execution.

## Result

**Issue #81 implementation candidate: PASS.**

**Aggregate Visual / UX Acceptance: PASS for the currently delivered staging-verifiable launch scope.**

**Public launch readiness: NO until the independent Production Go-Live Gate and production-only smoke are complete; any separately authorized game-login bridge required by final launch scope remains independent.**

The Account Overview reads only Platform-owned binding state. It exposes `ready`, `pending`, recoverable dependency interruption, hard conflict and missing/unknown safe states. Only recoverable `pending + dependency_unavailable` exposes retry, and that retry delegates to the existing idempotent `ProvisionCanaryAccount` action using persisted immutable provisioning intent. Conflict and missing/unknown states remain fail-closed.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T13:58:00+02:00
head: 898e4eb1fbf38ec9ec60c7c4e2a4b8ac9d832caf
branch: task/OTERYN-20260721-account-overview-provisioning-status
pr: 86
status: validating
context_routes:
  - accounts-characters
  - auth-identity
  - security
  - testing
  - agent-governance
owned_paths:
  - app/Accounts/ReadModels/**
  - app/Http/Controllers/Accounts/**
  - resources/views/identity/account/**
  - resources/views/identity/layout.blade.php
  - resources/views/game/layout.blade.php
  - routes/web.php
  - tests/Feature/Accounts/**
  - scripts/acceptance/tests/**
  - scripts/acceptance/seed.php
  - scripts/acceptance/seed-account-overview-state.php
  - docs/acceptance/VISUAL_UX_ACCEPTANCE_MATRIX.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-account-overview-provisioning-status.md
  - docs/agents/tasks/archive/OTERYN-20260721-account-overview-provisioning-status.md
proven:
  - Issue #81 requires the authenticated Account Overview and user-facing provisioning-status surface delivered by PR #86.
  - Platform-owned identity_canary_accounts remains the authoritative account binding and provisioning state; the implementation does not infer ownership from Canary data.
  - Ready binding is the only state that exposes character creation; pending, conflict and missing or unknown states fail closed.
  - Recoverable retry is exposed only for pending state with dependency_unavailable and delegates to the existing idempotent ProvisionCanaryAccount action using persisted immutable intent.
  - Hard conflict exposes support guidance only; no self-service unlink, rebind, transfer or replacement-account creation was added.
  - PR #86 implementation head ce98fe22fb57eed0f2971128337304415ba3b59f passed CI run 29827433313, Agent Governance run 29827433334, Phase 7 Production-Like Validation run 29827433316, Platform DB Outage Validation run 29827433319 and browser smoke run 29827433308.
  - Validation-only PR #87 full acceptance run 29827467074 / job 88624016224 passed 13 Playwright tests with zero failures and completed the Visual Accessibility collector successfully.
  - Full evidence artifact acceptance-e2e-29827467074-1 id 8494039432 has digest sha256:0bc455cdc65b8fbafa9796b263627246485269a563d3d703f2303265f371b72b.
  - The full artifact contains 11 dedicated Account Overview screenshots covering ready, pending, recoverable, conflict, missing and retry-success states plus the complete 71-screen exploratory Visual Accessibility set.
  - The 71-screen collector reported 0 status mismatches, 0 document-level overflow surfaces, 0 unlabeled controls, 0 sampled low-contrast surfaces, 0 focus-not-observed interactive surfaces and 0 raw technical-message surfaces.
  - Six collector browser-error surfaces are only expected failed-resource console messages for intentional 403, 404 and 503 responses; pageErrors are empty.
  - Dedicated Account Overview browser coverage verifies that synthetic Canary account IDs and provisioning names are absent from rendered page text.
  - Representative ready, recoverable and conflict Account Overview screenshots were visually inspected on desktop/mobile and show coherent hierarchy, safe action availability and fail-closed guidance.
  - The validation-only branch was created from the PR #86 application tree and differs intentionally only by forcing the full acceptance workflow profile; the subsequent intended-redirect harness correction was mirrored byte-identically on both branches.
derived:
  - The previously missing Account Overview and provisioning-status launch-scope surface is now delivered and supported by production-like browser evidence.
  - With merged PR #77 remediation plus PR #86 candidate evidence, no current staging-verifiable Visual UX launch blocker remains in the acceptance inventory.
  - Aggregate Visual UX Acceptance can be classified PASS independently from the still-pending Production Go-Live Gate.
unknown:
  - final production-only visual and runtime facts against the exact deployed production SHA
  - whether the separately authorized authoritative Platform game-login bridge is required by final public launch scope
conflicts: []
first_failure:
  marker: none
  evidence: the only full-run failure was a stale harness expectation for login redirect; it was corrected to the intended account redirect and the rerun passed full E2E and Visual Accessibility
rejected_hypotheses:
  - Read Canary account tables to infer account readiness: rejected because Platform-owned binding persistence is authoritative.
  - Allow retry from hard conflict: rejected because conflict is fail-closed and self-service rebind or replacement is forbidden.
  - Expose Canary account ID or provisioning name to help support: rejected because issue #81 requires no raw internal identifiers in the user-facing view.
  - Weaken authentication, MFA, RBAC or provisioning contracts to simplify Account Overview: rejected; the implementation reuses existing authorization and provisioning boundaries.
  - Treat the first full-run redirect assertion failure as a product defect: rejected because Laravel correctly preserved the intended /account URL after authentication and the stale harness expected /.
changed_paths:
  - app/Accounts/ReadModels/AccountOverviewReadModel.php
  - app/Http/Controllers/Accounts/AccountOverviewController.php
  - resources/views/identity/account/overview.blade.php
  - resources/views/identity/layout.blade.php
  - resources/views/game/layout.blade.php
  - routes/web.php
  - tests/Feature/Accounts/AccountOverviewTest.php
  - scripts/acceptance/tests/account-overview-acceptance.spec.mjs
  - scripts/acceptance/seed.php
  - scripts/acceptance/seed-account-overview-state.php
  - docs/acceptance/VISUAL_UX_ACCEPTANCE_MATRIX.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-account-overview-provisioning-status.md
validation:
  - command: PR #86 implementation-head CI
    result: PASS
    evidence: run 29827433313 on ce98fe22fb57eed0f2971128337304415ba3b59f
  - command: PR #86 implementation-head Agent Governance
    result: PASS
    evidence: run 29827433334 on ce98fe22fb57eed0f2971128337304415ba3b59f
  - command: PR #86 implementation-head Phase 7 Production-Like Validation
    result: PASS
    evidence: run 29827433316 on ce98fe22fb57eed0f2971128337304415ba3b59f
  - command: PR #86 implementation-head Platform DB Outage Validation
    result: PASS
    evidence: run 29827433319 on ce98fe22fb57eed0f2971128337304415ba3b59f
  - command: PR #86 implementation-head browser smoke
    result: PASS
    evidence: acceptance run 29827433308 on ce98fe22fb57eed0f2971128337304415ba3b59f
  - command: full production-like browser acceptance and Visual Accessibility
    result: PASS
    evidence: validation-only run 29827467074 / job 88624016224 on 81c39f8bde3045eb3240dbe300b6b5c54c7c7cd7; 13 tests passed; artifact 8494039432; 11 Account Overview screenshots; 71 collector screenshots; zero measured visual blockers
blockers: []
next_action: Close validation-only PR #87 without merge, then run current-head checks on PR #86 documentation closure and squash-merge PR #86 if the merge gate remains green.
```

## Notes

The full validation-only branch exists solely because the available connector does not expose workflow dispatch. It must be closed without merge. Production smoke remains separate and no evidence here may be promoted to `PRODUCTION_PROVEN`.
