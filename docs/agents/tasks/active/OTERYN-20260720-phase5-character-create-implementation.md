# OTERYN-20260720 Phase 5 character create implementation

## Goal

Implement the approved `CHARACTER_CREATION_CONTRACT.md` vertical slice: authenticated Platform character creation derived from the ready immutable Canary account binding, ADR 0005 canonical name/starter policy, dedicated least-privilege `canary_character_create` connection, exact locked Canary transaction, natural idempotent recovery, deterministic operation results, and real MariaDB privilege/concurrency coverage. Do not modify Canary/login-server repositories.

## Acceptance criteria

- [ ] Add server-side canonical character-name and reserved-name policy implementation with unit coverage.
- [ ] Add exact request validation for name, base vocation `1/2/3/4/9` and sex `0/1`; reject client account/starter-field control.
- [ ] Add authenticated create-character HTTP/form boundary using only the current Identity's ready immutable Canary binding.
- [ ] Add character-create domain action and gateway contract with bounded deterministic result/error mapping.
- [ ] Add a dedicated `canary_character_create` database connection/environment boundary; do not broaden `canary` or `canary_provisioning`.
- [ ] Implement exact account-row `FOR UPDATE`, same-name recovery, active-count limit, 42-column starter INSERT and generated player-ID return.
- [ ] Implement at most three total transaction attempts for recognized deadlock/serialization failures only.
- [ ] Add reviewed SQL provisioning template for exact column-level SELECT/INSERT grants.
- [ ] Add fail-closed effective-grant verifier and console command.
- [ ] Add feature/unit tests for authorization, validation, canonicalization, idempotency, conflicts, limit and request non-control.
- [ ] Add real MariaDB integration coverage for exact grants, `FOR UPDATE`, starter/default row shape, quota race, global name race, idempotent recovery and forbidden privilege denial.
- [ ] Keep Canary/login-server repositories read-only; record any disproven external invariant as a precise blocker instead of changing external code.
- [ ] Update `CHARACTER_CREATION_CONTRACT.md` status to implemented only after exact-head validation is green.
- [ ] Run formatting, PHPStan, full tests, exact-head CI and Agent Governance before merge.

## Ownership

```yaml
owned_paths:
  - .env.example
  - .github/workflows/ci.yml
  - app/Characters/**
  - app/CanaryIntegration/CanaryCharacterCreator.php
  - app/CanaryIntegration/CanaryCharacterCreateDatabasePrivilegeVerifier.php
  - app/Http/Controllers/Characters/**
  - app/Http/Requests/Characters/**
  - app/Providers/AppServiceProvider.php
  - config/database.php
  - database/provisioning/canary-character-create.sql.template
  - resources/views/characters/create.blade.php
  - routes/web.php
  - routes/console.php
  - tests/Feature/Characters/**
  - tests/Unit/Characters/**
  - tests/Unit/CanaryIntegration/CanaryCharacterCreateDatabasePrivilegeVerifierTest.php
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-create-implementation.md
modules:
  - Characters
  - Accounts
  - Identity
  - Integration
  - database
  - security
  - testing
  - agent-governance
dependencies:
  - OTERYN-20260720-phase5-character-create-operation-contract
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/architecture/adr/0005-character-creation-product-policy.md
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
blockers:
  - none for repository implementation; production deployment requires out-of-band provisioning of the dedicated DB principal
cross_repository_tasks:
  - blakinio/canary remains read-only; no code/schema/datapack change is currently authorized or proven necessary
  - opentibiabr/login-server remains read-only; future authoritative game-login bridge remains separate
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T09:12:00+02:00
head: 164824b953600b45603cc05a44071100f52664f4
branch: task/OTERYN-20260720-phase5-character-create-implementation
pr: none
status: implementing
context_routes:
  - agent-governance
  - architecture
  - accounts-characters
  - auth-identity
  - canary-integration
  - database
  - security
  - testing
owned_paths:
  - .env.example
  - .github/workflows/ci.yml
  - app/Characters/**
  - app/CanaryIntegration/CanaryCharacterCreator.php
  - app/CanaryIntegration/CanaryCharacterCreateDatabasePrivilegeVerifier.php
  - app/Http/Controllers/Characters/**
  - app/Http/Requests/Characters/**
  - app/Providers/AppServiceProvider.php
  - config/database.php
  - database/provisioning/canary-character-create.sql.template
  - resources/views/characters/create.blade.php
  - routes/web.php
  - routes/console.php
  - tests/Feature/Characters/**
  - tests/Unit/Characters/**
  - tests/Unit/CanaryIntegration/CanaryCharacterCreateDatabasePrivilegeVerifierTest.php
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-create-implementation.md
proven:
  - Oteryn Platform main is 164824b953600b45603cc05a44071100f52664f4, the squash merge of PR #40 character-create operation-contract housekeeping.
  - Live open-PR search returned no open Oteryn Platform pull requests before this implementation branch was created.
  - PR #39 approved the exact transaction, 42-column insert, natural account/name idempotency, 10-active-character limit, three-attempt transient retry cap and exact least-privilege grant surface.
  - Existing Identity authentication uses Laravel auth and controllers verify `request->user()` is an Identity.
  - Existing account provisioning demonstrates separate gateway interfaces, dedicated database connections, SQL grant templates, grant verifiers and real MariaDB CI service coverage.
derived:
  - The smallest complete product vertical slice is an authenticated `/account/characters/create` form plus POST `/account/characters`, backed by the approved service/gateway and dedicated database principal.
  - The public `/characters` search routes remain separate and must not be overloaded with authenticated account-management semantics.
unknown:
  - whether first-pass code will require minor Pint/PHPStan adjustments after CI
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Expose account_id in the create form: rejected; target ownership comes only from the ready binding.
  - Reuse canary_provisioning: rejected by contract and least-privilege boundary.
  - Implement starter inventory/storage rows: rejected by ADR 0005 and contract.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-create-implementation.md
validation:
  - command: implementation preflight
    result: PASS
    evidence: main 164824b953600b45603cc05a44071100f52664f4, no open PRs, operation contract merged and archived
blockers:
  - none pending implementation and validation
next_action: Open the draft PR, claim ACTIVE_WORK, then implement the character domain/policy/gateway/database boundary before wiring the authenticated HTTP form and required tests.
```

## Notes

This is the second approved Phase 5 shared-write implementation. Its only new Canary mutation is the exact bounded `players` insert from the merged contract. Character deletion/rename remain out of scope.
