# OTERYN-20260720 Phase 5 Platform account provisioning implementation

## Goal

Implement the approved `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md` vertical slice: Platform-owned pending/ready provisioning state, immutable `1 Platform Identity <-> 1 Canary accounts.id` binding, dedicated least-privilege `canary_provisioning` connection, idempotent forward-recovery saga, registration integration, audit events and regression coverage. Do not implement character creation/deletion/rename or game-login bridge changes.

## Acceptance criteria

- [x] Add Platform-owned durable provisioning/binding persistence with unique Identity, unique Canary account ID and unique immutable provisioning name constraints.
- [x] Create pending provisioning intent inside the Platform registration transaction before any Canary write.
- [x] Implement a dedicated Canary account provisioner using only `canary_provisioning`, inserting exactly `accounts(name,password,email,creation)` and recovering by exact provisioning name + creation epoch.
- [x] Generate a non-user random sink credential, persist only its SHA-1 compatibility digest in Canary, and never persist/log/expose the plaintext or digest in Platform state/audit.
- [x] Keep existing `canary` / `oteryn_readonly` connection unchanged and add a separate provisioning connection/environment boundary.
- [x] Add reviewed provisioning SQL template and a non-destructive effective-grant verifier for the exact approved column-level INSERT/SELECT surface.
- [x] Make registration attempt provisioning after the Platform transaction commits while preserving pending state on dependency failure.
- [x] Make retries idempotent and forward-recover Canary-committed/Platform-finalization-failed state without deleting Canary accounts.
- [x] Add bounded security audit events for requested/completed/failed/conflict states without secrets.
- [x] Add tests for success, pending failure, retry recovery, committed-Canary forward recovery, duplicate/conflict behavior, binding uniqueness, client non-control of account identifiers and separate connection usage.
- [x] Add privilege-policy tests for exact approved grants and rejection of broader/excessive grants.
- [x] Add real MariaDB CI integration coverage proving column-level grants, trigger side effects, password-read denial and idempotent recovery on the target database family.
- [x] Do not modify Canary/login-server repositories and do not implement character/shared writes beyond the approved account insert.
- [ ] Run formatting, static analysis, full tests, exact-head CI and Agent Governance before merge.

## Ownership

```yaml
owned_paths:
  - .env.example
  - .github/workflows/ci.yml
  - app/Accounts/**
  - app/Audit/SecurityEventRecorder.php
  - app/CanaryIntegration/CanaryAccountProvisioner.php
  - app/CanaryIntegration/CanaryProvisioningDatabasePrivilegeVerifier.php
  - app/Identity/Actions/RegisterIdentity.php
  - app/Providers/AppServiceProvider.php
  - config/database.php
  - database/migrations/**identity_canary**
  - database/provisioning/canary-provisioning.sql.template
  - routes/console.php
  - tests/Feature/Accounts/**
  - tests/Feature/Identity/RegistrationTest.php
  - tests/Unit/CanaryIntegration/CanaryProvisioningDatabasePrivilegeVerifierTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-platform-account-provisioning-implementation.md
modules:
  - Accounts
  - Identity
  - Integration
  - database
  - security
  - testing
  - agent-governance
dependencies:
  - OTERYN-20260720-phase5-platform-account-provisioning-contract
  - docs/contracts/PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md
  - docs/architecture/adr/0004-authoritative-platform-account-ownership.md
blockers:
  - none for repository implementation; production deployment requires out-of-band provisioning of the dedicated DB principal and the future game-login bridge remains separate
cross_repository_tasks:
  - blakinio/canary remains read-only; no repository changes authorized; possible future game-login changes remain recorded in PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md
  - opentibiabr/login-server remains read-only; the future Platform-authorized login exchange is documented but not part of this task
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T01:22:00+02:00
head: a816d1e5b34aa5a7cc3f223ae1f2ef36b75fcd3f
branch: task/OTERYN-20260720-phase5-platform-account-provisioning-implementation
pr: 33
status: validating
context_routes:
  - agent-governance
  - architecture
  - auth-identity
  - accounts-characters
  - canary-integration
  - database
  - security
  - testing
owned_paths:
  - .env.example
  - .github/workflows/ci.yml
  - app/Accounts/**
  - app/Audit/SecurityEventRecorder.php
  - app/CanaryIntegration/CanaryAccountProvisioner.php
  - app/CanaryIntegration/CanaryProvisioningDatabasePrivilegeVerifier.php
  - app/Identity/Actions/RegisterIdentity.php
  - app/Providers/AppServiceProvider.php
  - config/database.php
  - database/migrations/**identity_canary**
  - database/provisioning/canary-provisioning.sql.template
  - routes/console.php
  - tests/Feature/Accounts/**
  - tests/Feature/Identity/RegistrationTest.php
  - tests/Unit/CanaryIntegration/CanaryProvisioningDatabasePrivilegeVerifierTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-platform-account-provisioning-implementation.md
proven:
  - Oteryn Platform main at task start was a525afb7277e4422124f92eaa8dbe2e850349b87, the squash merge of PR #32 account provisioning contract housekeeping.
  - Live open-PR search returned no open Oteryn Platform pull requests before this implementation branch was created; PR #33 is the current task PR.
  - The approved provisioning contract defines exact account insert columns, sink credential strategy, pending-before-write saga, forward recovery, least-privilege connection and required tests.
  - identity_canary_accounts provides one primary-key row per Identity, nullable unique canary_account_id, unique immutable provisioning_name, creation marker and pending/ready/conflict state.
  - RegisterIdentity creates Identity plus pending provisioning intent and requested audit event in one Platform transaction, then attempts Canary provisioning only after that transaction commits.
  - CanaryAccountProvisioner uses only canary_provisioning, writes exactly name/password/email/creation, never reads password, sanitizes dependency exceptions and recovers by exact provisioning name plus creation epoch.
  - ProvisionCanaryAccount serializes Platform finalization with row locks, treats same completed binding as idempotent, rejects conflicting account IDs and persists bounded failure/conflict state.
  - Existing canary read-only connection configuration is unchanged; a separate canary_provisioning connection and environment namespace were added.
  - The provisioning SQL template grants only column-level INSERT(name,password,email,creation) and SELECT(id,name,creation) on accounts.
  - The provisioning privilege verifier rejects table-level, unrelated-table, password-read, missing-column and GRANT OPTION privileges.
  - Registration and saga tests cover generated server-side identifiers, client account_id/provisioning_name non-control, pending dependency failure, retry, idempotent ready state, hard conflict and database-enforced one-Canary-account-per-Identity ownership.
  - Real MariaDB integration coverage is wired into CI and validates effective grants, Canary-compatible account trigger side effects, password-read denial, duplicate-free retry and forward recovery from a previously committed external account.
  - No blakinio/canary or opentibiabr/login-server repository was modified.
derived:
  - Once PR #33 passes final validation and merges, the Identity-to-exact-Canary-account ownership binding prerequisite for user-scoped authorization will be implemented for greenfield accounts.
  - Character creation will still remain blocked by the independent character name/starter-state/character-write contract blockers, not by missing account ownership binding.
unknown:
  - exact final game-session TTL, replay/single-use and revocation design for the future Platform-authorized login bridge
  - whether the final game-login design requires Canary changes beyond existing DB-backed account_sessions consumption
conflicts:
  - current native Canary and external login-server reusable-password paths are not the target Platform credential authority; generated undisclosed sink credentials keep those paths unavailable to normal Platform users until the dedicated login bridge exists
first_failure:
  marker: none
  evidence: implementation is awaiting exact-head CI, static analysis, full tests and Agent Governance
rejected_hypotheses:
  - Reuse Platform Identity password in accounts.password: rejected by the approved contract because it reintroduces shared reusable credential authority and hash compatibility is not proven.
  - Broaden existing canary read-only credential: rejected; provisioning uses an independent connection/principal.
  - Let registration rollback when Canary is unavailable: rejected; durable Platform Identity plus pending provisioning intent is the retryable contract state.
  - Auto-delete a Canary account after Platform finalization failure: rejected; deterministic forward recovery reuses the same persisted provisioning identity.
changed_paths:
  - .env.example
  - .github/workflows/ci.yml
  - app/Accounts/Actions/ProvisionCanaryAccount.php
  - app/Accounts/Contracts/CanaryAccountProvisioningGateway.php
  - app/Accounts/Exceptions/CanaryAccountProvisioningConflict.php
  - app/Accounts/Exceptions/CanaryAccountProvisioningException.php
  - app/Accounts/Exceptions/CanaryAccountProvisioningUnavailable.php
  - app/Accounts/Models/IdentityCanaryAccount.php
  - app/Audit/SecurityEventRecorder.php
  - app/CanaryIntegration/CanaryAccountProvisioner.php
  - app/CanaryIntegration/CanaryProvisioningDatabasePrivilegeVerifier.php
  - app/Identity/Actions/RegisterIdentity.php
  - app/Providers/AppServiceProvider.php
  - config/database.php
  - database/migrations/2026_07_20_011000_create_identity_canary_accounts_table.php
  - database/provisioning/canary-provisioning.sql.template
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-platform-account-provisioning-implementation.md
  - routes/console.php
  - tests/Feature/Accounts/CanaryProvisioningMariaDbIntegrationTest.php
  - tests/Feature/Accounts/ProvisionCanaryAccountTest.php
  - tests/Feature/Identity/RegistrationTest.php
  - tests/Unit/CanaryIntegration/CanaryProvisioningDatabasePrivilegeVerifierTest.php
validation:
  - command: implementation task preflight
    result: PASS
    evidence: main a525afb7277e4422124f92eaa8dbe2e850349b87, no open PRs, predecessor contract merged and archived
  - command: contract implementation review
    result: PASS
    evidence: bounded account insert, immutable binding, sink credential, separate connection, forward recovery, audit and negative ownership invariants implemented without character writes
  - command: real MariaDB CI coverage added
    result: PASS
    evidence: CI now provisions a MariaDB 11.8 service and executes the dedicated integration test when the integration environment variables are present
blockers:
  - none pending repository validation
next_action: Inspect exact-head PR #33 CI and Agent Governance results, fix any formatting/static/runtime/MariaDB failures, then update the binding contract and final checkpoint only after the implementation is proven green.
```

## Notes

This is the first approved Phase 5 shared-write implementation. Its only Canary mutation is the bounded account insert defined by the provisioning contract. Character creation remains blocked until separate character operation blockers are resolved.
