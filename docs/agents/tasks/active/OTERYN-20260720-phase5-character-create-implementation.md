# OTERYN-20260720 Phase 5 character create implementation

## Goal

Implement the approved `CHARACTER_CREATION_CONTRACT.md` vertical slice: authenticated Platform character creation derived from the ready immutable Canary account binding, ADR 0005 canonical name/starter policy, dedicated least-privilege `canary_character_create` connection, exact locked Canary transaction, natural idempotent recovery, deterministic operation results, and real MariaDB privilege/concurrency coverage. Do not modify Canary/login-server repositories.

## Acceptance criteria

- [x] Server-side canonical character-name and reserved-name policy with unit coverage.
- [x] Exact request validation for `name`, vocation `1/2/3/4/9` and sex `0/1`; client account/starter-field control rejected.
- [x] Authenticated create-character HTTP/form boundary using only the current Identity's ready immutable Canary binding.
- [x] Character-create domain action and gateway with bounded deterministic result/error mapping.
- [x] Dedicated `canary_character_create` database connection; existing `canary` and `canary_provisioning` remain unchanged.
- [x] Exact account-row `FOR UPDATE`, same-name recovery, active-count limit, 42-column starter INSERT and generated player-ID return.
- [x] At most three total transaction attempts for recognized deadlock/serialization failures only.
- [x] Reviewed SQL provisioning template for exact column-level SELECT/INSERT grants.
- [x] Fail-closed effective-grant verifier and console command.
- [x] Feature/unit coverage for authorization, validation, canonicalization, idempotency, conflicts, limit and request non-control.
- [x] Real MariaDB integration coverage for exact grants, `FOR UPDATE`, starter/default row shape, quota race, global name race, idempotent recovery and forbidden privilege denial.
- [x] Canary/login-server repositories remained read-only; no external code change was required.
- [x] Clean workflow delivery validation passed formatting, PHPStan and full tests.

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
  - none for repository merge
  - production deployment requires out-of-band provisioning and verification of the dedicated DB principal
cross_repository_tasks:
  - blakinio/canary remained read-only; no code/schema/datapack change was required
  - opentibiabr/login-server remained read-only; the future authoritative Platform game-login bridge remains separate
```

## Context checkpoint

```yaml
checkpoint_version: 2
updated_at: 2026-07-20T10:30:00+02:00
head: 27520854e326ada46c19ba1bfcda05fe89de2cab
branch: task/OTERYN-20260720-phase5-character-create-implementation
pr: 41
status: ready
proven:
  - main at task start was 164824b953600b45603cc05a44071100f52664f4.
  - PR #39 approved the exact character-create transaction and least-privilege boundary.
  - CharacterNamePolicy implements ADR 0005 canonicalization, reserved-name and protected-prefix policy.
  - CreateCharacter authorizes only through the authenticated Identity's ready immutable IdentityCanaryAccount binding.
  - The browser cannot choose account_id or starter-state fields.
  - CanaryCharacterCreator uses only canary_character_create and performs account FOR UPDATE -> same-name recovery -> active quota count -> exact 42-column starter INSERT.
  - Same-account active same-name retries recover the existing player ID without mutation; deleted same-account and other-account same-name rows are deterministic conflicts.
  - The gateway retries only recognized transient deadlock/serialization failures with at most three total attempts.
  - The SQL template and effective-grant verifier enforce only accounts(id) SELECT, players(id,name,account_id,deletion) SELECT and approved players INSERT columns.
  - Real MariaDB tests prove the exact grants, FOR UPDATE, starter/default row shape, forbidden privilege denial, quota behavior, same-account 9-to-10 race, global same-name race and committed-row recovery.
  - A race-test harness failure was traced to a forked inherited root PDO and fixed by disconnecting before fork/reconnecting in the parent; production transaction logic was unchanged.
  - Character feature tests now establish sessions through the real POST /login flow so EnsureIdentitySessionIsCurrent validates the same state used in production.
  - Clean head 27520854e326ada46c19ba1bfcda05fe89de2cab passed CI #563 and Agent Governance #484.
  - No blakinio/canary or opentibiabr/login-server repository was modified.
derived:
  - The repository implementation satisfies the approved character-create operation contract.
  - Production enablement still requires provisioning the dedicated principal out-of-band and passing canary:verify-character-create-db-privileges.
unknown:
  - future Platform-authorized game-login bridge protocol remains a separate cross-repository task
conflicts: []
first_failure:
  marker: CHARACTER_FEATURE_TEST_SESSION_STATE_MISMATCH
  evidence: initial feature tests used synthetic actingAs state that did not establish the required web-session generation marker; fixed by exercising the real login flow
rejected_hypotheses:
  - broaden canary or reuse canary_provisioning: rejected by least privilege
  - accept client account_id/starter fields: rejected by ownership and product policy
  - add player UPDATE for retry recovery: rejected; recovery is read-only
  - weaken concurrency tests after fork/PDO failure: rejected; harness fixed and real races retained
changed_paths:
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
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-create-implementation.md
validation:
  - command: CI #563 on clean head 27520854e326ada46c19ba1bfcda05fe89de2cab
    result: PASS
    evidence: Composer validation/install, Pint, PHPStan and full test suite including real MariaDB integration/race coverage
  - command: Agent Governance #484
    result: PASS
    evidence: completed successfully on clean head 27520854e326ada46c19ba1bfcda05fe89de2cab
blockers:
  - none for merge after final documentation-head revalidation
next_action: Revalidate CI/Governance on the documentation-final head, verify divergence/review state, then squash-merge PR #41.
```

## Notes

This is the second approved Phase 5 shared-write implementation. Its only new Canary mutation is the exact bounded `players` insert from the merged contract. Character deletion and rename remain out of scope and require separate operation contracts if the product later chooses to implement them.
