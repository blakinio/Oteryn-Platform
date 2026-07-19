# OTERYN-20260720 Phase 5 Platform account provisioning implementation

## Goal

Implement the approved `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md` vertical slice: Platform-owned pending/ready provisioning state, immutable `1 Platform Identity <-> 1 Canary accounts.id` binding, dedicated least-privilege `canary_provisioning` connection, idempotent forward-recovery saga, registration integration, audit events and regression coverage. Do not implement character creation/deletion/rename or game-login bridge changes.

## Acceptance criteria

- [ ] Add Platform-owned durable provisioning/binding persistence with unique Identity, unique Canary account ID and unique immutable provisioning name constraints.
- [ ] Create pending provisioning intent inside the Platform registration transaction before any Canary write.
- [ ] Implement a dedicated Canary account provisioner using only `canary_provisioning`, inserting exactly `accounts(name,password,email,creation)` and recovering by exact provisioning name + creation epoch.
- [ ] Generate a non-user random sink credential, persist only its SHA-1 compatibility digest in Canary, and never persist/log/expose the plaintext or digest in Platform state/audit.
- [ ] Keep existing `canary` / `oteryn_readonly` connection unchanged and add a separate provisioning connection/environment boundary.
- [ ] Add reviewed provisioning SQL template and a non-destructive effective-grant verifier for the exact approved column-level INSERT/SELECT surface.
- [ ] Make registration attempt provisioning after the Platform transaction commits while preserving pending state on dependency failure.
- [ ] Make retries idempotent and forward-recover Canary-committed/Platform-finalization-failed state without deleting Canary accounts.
- [ ] Add bounded security audit events for requested/completed/failed/conflict states without secrets.
- [ ] Add tests for success, pending failure, retry recovery, duplicate/conflict behavior, binding uniqueness, client non-control of account identifiers and separate connection usage.
- [ ] Add privilege-policy tests for exact approved grants and rejection of broader/excessive grants.
- [ ] Do not modify Canary/login-server repositories and do not implement character/shared writes beyond the approved account insert.
- [ ] Run formatting, static analysis, full tests, exact-head CI and Agent Governance before merge.

## Ownership

```yaml
owned_paths:
  - app/Accounts/**
  - app/CanaryIntegration/CanaryAccountProvisioner.php
  - app/CanaryIntegration/CanaryProvisioningDatabasePrivilegeVerifier.php
  - app/Audit/SecurityEventRecorder.php
  - app/Identity/Actions/RegisterIdentity.php
  - config/database.php
  - database/migrations/**identity_canary**
  - database/provisioning/canary-provisioning.sql.template
  - routes/console.php
  - tests/Feature/Accounts/**
  - tests/Unit/Accounts/**
  - tests/Unit/CanaryIntegration/CanaryProvisioningDatabasePrivilegeVerifierTest.php
  - tests/Feature/Identity/RegistrationTest.php
  - .env.example
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
  - none for implementation; production deployment still requires out-of-band provisioning of the dedicated DB principal and future game-login bridge remains separate
cross_repository_tasks:
  - blakinio/canary remains read-only; no repository changes authorized
  - opentibiabr/login-server remains read-only; future Platform-authorized login exchange is documented but not part of this task
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T01:05:00+02:00
head: UNKNOWN
branch: task/OTERYN-20260720-phase5-platform-account-provisioning-implementation
pr: none
status: implementing
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
  - app/Accounts/**
  - app/CanaryIntegration/CanaryAccountProvisioner.php
  - app/CanaryIntegration/CanaryProvisioningDatabasePrivilegeVerifier.php
  - app/Audit/SecurityEventRecorder.php
  - app/Identity/Actions/RegisterIdentity.php
  - config/database.php
  - database/migrations/**identity_canary**
  - database/provisioning/canary-provisioning.sql.template
  - routes/console.php
  - tests/Feature/Accounts/**
  - tests/Unit/Accounts/**
  - tests/Unit/CanaryIntegration/CanaryProvisioningDatabasePrivilegeVerifierTest.php
  - tests/Feature/Identity/RegistrationTest.php
  - .env.example
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-platform-account-provisioning-implementation.md
proven:
  - Oteryn Platform main is a525afb7277e4422124f92eaa8dbe2e850349b87, the squash merge of PR #32 account provisioning contract housekeeping.
  - Live open-PR search returned no open Oteryn Platform pull requests before this implementation branch was created.
  - The approved provisioning contract defines exact account insert columns, sink credential strategy, pending-before-write saga, forward recovery, least-privilege connection and required tests.
derived:
  - The implementation can proceed entirely in Oteryn Platform without modifying Canary schema or login-server code.
unknown:
  - exact repository-local test seams needed to simulate cross-database partial failure without weakening production transaction boundaries
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses: []
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260720-phase5-platform-account-provisioning-implementation.md
validation:
  - command: implementation task preflight
    result: PASS
    evidence: main a525afb7277e4422124f92eaa8dbe2e850349b87, no open PRs, predecessor contract merged and archived
blockers:
  - none
next_action: Open a draft PR, inspect exact current migration/test/console/provisioning patterns, then implement the smallest contract-complete vertical slice.
```

## Notes

This task is the first approved Phase 5 shared-write implementation. Its only Canary mutation is the bounded account insert defined by the provisioning contract. Character creation remains blocked.
