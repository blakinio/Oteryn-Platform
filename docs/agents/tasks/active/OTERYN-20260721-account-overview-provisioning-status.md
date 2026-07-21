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

- [ ] Authenticated `Account Overview` route exists and is the primary account-shell landing entry.
- [ ] Overview derives provisioning/binding state only from Platform-owned `identity_canary_accounts` persistence.
- [ ] Ready, pending/in-progress, recoverable dependency-failure and hard-conflict states have explicit safe user-facing presentation.
- [ ] No raw Canary account identifiers, provisioning names, sink credentials, tokens, hashes or internal exception details are exposed.
- [ ] Retry is offered only for existing contract-authorized pending/recoverable provisioning state and reuses the existing idempotent `ProvisionCanaryAccount` action.
- [ ] Conflict state remains fail-closed and provides support/operator guidance without self-service rebind, unlink or replacement-account creation.
- [ ] Account Overview links coherently to MFA/security, password change and character creation when permitted.
- [ ] Public and identity account navigation expose Account Overview as the authenticated account entry point.
- [ ] Unauthenticated access is denied by existing authentication middleware.
- [ ] Focused feature tests cover ready, pending, recoverable failure, conflict, retry success/failure, missing binding safe state and authorization denial.
- [ ] Production-like browser acceptance covers desktop/mobile overview/status variants and no new measured overflow/accessibility blocker is introduced.
- [ ] Aggregate broader Visual/UX launch gate is reclassified only from exact-SHA evidence; production-only verification remains separate.

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
  - scripts/acceptance/visual-acceptance.js
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

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T13:00:00+02:00
head: 66fcb23f4ac64f56c8d563517a2c678ac4607f35
branch: task/OTERYN-20260721-account-overview-provisioning-status
pr: pending
status: implementing
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
  - scripts/acceptance/visual-acceptance.js
  - docs/acceptance/VISUAL_UX_ACCEPTANCE_MATRIX.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-account-overview-provisioning-status.md
  - docs/agents/tasks/archive/OTERYN-20260721-account-overview-provisioning-status.md
proven:
  - Issue #81 is open and requires a separate authenticated Account Overview plus provisioning-status surface backed by authoritative existing Platform state.
  - Platform-owned identity_canary_accounts state implements pending, ready and conflict lifecycle with bounded failure metadata; pending dependency failure is contract-authorized for idempotent retry using the same provisioning intent.
  - Ready binding is the only state that authorizes user-scoped Canary operations; pending and conflict states fail closed.
  - Existing contracts forbid self-service unlink, rebind, transfer, replacement-account creation and browser-selected Canary account ownership.
derived:
  - The smallest compliant implementation can read only Platform-owned binding state and invoke the existing ProvisionCanaryAccount action for authenticated retry of recoverable pending state.
  - A missing binding row must render a safe non-actionable support state rather than inventing ownership or provisioning intent.
unknown:
  - exact current acceptance harness selectors needed for new Account Overview coverage until targeted harness inspection
conflicts: []
first_failure:
  marker: missing launch-scope account surface
  evidence: issue #81 records that no authenticated Account Overview or provisioning-status route/read model is currently delivered
rejected_hypotheses:
  - Read Canary account tables to infer account readiness: rejected because Platform-owned binding persistence is the authoritative ownership and provisioning state.
  - Allow retry from hard conflict: rejected because conflict is fail-closed and self-service rebind or replacement is forbidden.
  - Expose Canary account ID or provisioning name to help support: rejected because issue #81 requires no raw internal identifiers in the user-facing view.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-account-overview-provisioning-status.md
validation:
  - command: live-state preflight
    result: PASS
    evidence: main 66fcb23f4ac64f56c8d563517a2c678ac4607f35; issue #81 open; no open PR matched account overview/provisioning status ownership
blockers: []
next_action: Open the draft implementation PR, then add the authenticated Account Overview read model/controller/routes/views and focused feature tests.
```

## Notes

This task may invoke the existing idempotent provisioning action only for contract-authorized retry. It must not broaden provisioning semantics or expose Canary-internal ownership identifiers.