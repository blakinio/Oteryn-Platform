# OTERYN-20260720 Phase 5 Platform account provisioning contract

## Goal

Define the smallest evidence-backed operation-level contract for Platform-originated Canary account creation plus immutable `1 Platform Identity <-> 1 Canary accounts.id` ownership binding under the accepted authoritative Platform account model. Approve implementation only if exact account fields, credential/game-login isolation, least-privilege write grants, cross-database failure recovery, idempotency/concurrency and audit invariants are proven. Do not implement Canary account or character writes in this contract task.

## Acceptance criteria

- [ ] Verify current Oteryn Platform `main`, open PR state, active task state and predecessor Phase 5 ownership decision before claiming scope.
- [ ] Revalidate current Platform Identity persistence, registration transaction, database connection boundaries and absence of existing binding/provisioning infrastructure.
- [ ] Revalidate current Canary `accounts` schema, required fields, uniqueness constraints, account-create trigger side effects and current account authentication behavior at current Canary `main`.
- [ ] Revalidate current external login-server credential verification and session creation behavior at current upstream `main`.
- [ ] Define the exact Platform-owned binding/provisioning state model for immutable 1:1 ownership.
- [ ] Define an account credential field strategy that cannot be used as a user reusable password and does not duplicate Canary credential verification.
- [ ] Define the exact dedicated least-privilege Canary provisioning connection/grants without broadening the existing read-only `canary` connection.
- [ ] Define deterministic cross-database saga/retry/compensation semantics that prevent orphan authorization, duplicate accounts and conflicting bindings.
- [ ] Define audit/readiness semantics and negative/race/failure cases required before implementation.
- [ ] Add/update durable contracts and active-work handoff with the exact implementation gate.
- [ ] Do not modify Canary/login-server repositories and do not implement account/character shared writes.
- [ ] Run exact-head CI and Agent Governance before readiness/merge.

## Ownership

```yaml
owned_paths:
  - docs/contracts/PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-platform-account-provisioning-contract.md
modules:
  - Identity
  - Accounts
  - Integration
  - accounts-characters
  - database
  - security
  - agent-governance
dependencies:
  - OTERYN-20260719-phase5-ownership-binding-dependency-gate
  - docs/architecture/adr/0004-authoritative-platform-account-ownership.md
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/architecture/DATA_OWNERSHIP.md
blockers:
  - none for bounded operation-contract discovery; implementation approval depends on findings
cross_repository_tasks:
  - blakinio/canary is read-only evidence source; no writes are authorized in this task
  - opentibiabr/login-server is read-only evidence source; no writes are authorized in this task
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T00:35:00+02:00
head: UNKNOWN
branch: task/OTERYN-20260720-phase5-platform-account-provisioning-contract
pr: none
status: investigating
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
  - docs/contracts/PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-platform-account-provisioning-contract.md
proven:
  - Oteryn Platform main is 3b22f13ded681abf8c01b8e4fa816fdc616c7c15, the squash merge of PR #30 post-merge authoritative ownership housekeeping.
  - Live open-PR search returned no open Oteryn Platform pull requests before this task branch was created.
  - ACTIVE_WORK has no active task and names Platform-originated Canary account creation plus immutable 1:1 ownership binding as the recommended next bounded task.
  - ADR 0004 and the binding contract establish the greenfield authoritative Platform model and exclude existing-account claim/import from Phase 5.
derived:
  - Existing-account account-control proof is no longer a prerequisite for supported greenfield accounts.
unknown:
  - exact safe Canary account insert field set and trigger implications at current Canary main
  - safe non-user credential representation for the required accounts.password column before Platform-authorized game-login integration exists
  - exact dedicated provisioning DB grants
  - deterministic cross-database retry/recovery algorithm and readiness state model
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses: []
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260720-phase5-platform-account-provisioning-contract.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: main 3b22f13ded681abf8c01b8e4fa816fdc616c7c15, no open PRs, ACTIVE_WORK no active task, accepted ADR 0004 ownership direction
blockers:
  - none for discovery
next_action: Open the draft PR, then inspect current Platform registration/binding infrastructure and current Canary/login-server account creation and credential behavior before writing the operation contract.
```

## Notes

This is an operation-contract task only. It may approve a future implementation shape, but it must not itself create Canary accounts, persist ownership bindings or modify game-login behavior.
