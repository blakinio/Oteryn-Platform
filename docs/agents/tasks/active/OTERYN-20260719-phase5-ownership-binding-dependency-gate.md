# OTERYN-20260719 Phase 5 ownership binding dependency gate

## Goal

Revalidate the two explicitly allowed dependency directions for establishing a trustworthy `Platform Identity -> authorized Canary accounts.id` ownership binding, using only current Oteryn Platform, Canary and login-server evidence. Approve a Platform implementation only if all critical assumptions are proven; otherwise document the exact blocker, identify the nearest minimal dependency and keep all user-scoped Canary writes fail-closed.

## Acceptance criteria

- [x] Verify current Oteryn Platform `main`, active task state, open PRs and recent Phase 5 Git/task history before claiming scope.
- [x] Re-read the current Phase 5 ownership-binding, Canary data, auth/game-login and data-ownership contracts.
- [x] Revalidate current Platform Identity persistence, existing account mappings, Canary database connections, privilege boundaries and shared-write infrastructure.
- [x] Revalidate current Canary/login-server account identification and credential verification paths against current repository heads.
- [x] Evaluate exactly two directions: authoritative account-control proof and Platform-originated Canary account creation.
- [x] For account-control proof, verify whether an existing short-lived, single-use, replay-resistant `accounts.id`-bound proof can be obtained and consumed by Platform without creating a game session or duplicating credential verification.
- [x] For Platform-originated account creation, verify whether account-creation fields, credential storage compatibility, transaction/failure semantics, least-privilege write boundary and ownership lifecycle/cardinality are all explicitly approved.
- [x] Record the explicit greenfield product decision that Oteryn Platform is authoritative for user Identity/account lifecycle/credential policy, existing Canary accounts are not migration inputs, and ownership cardinality is immutable 1:1.
- [x] Add a durable ADR for the authoritative Platform account-ownership model and update the ownership-binding contract with unlink/rebind/recovery rules.
- [x] Select Platform-originated Canary account creation as the product direction while retaining the exact account-create/game-login/write-boundary implementation blockers.
- [x] Do not implement character creation/deletion/rename, ownership binding persistence, Canary account creation, credential migration or any shared Canary write.
- [x] Run exact-head CI and Agent Governance before readiness/merge.

## Ownership

```yaml
owned_paths:
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/architecture/adr/0004-authoritative-platform-account-ownership.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-ownership-binding-dependency-gate.md
modules:
  - Identity
  - Accounts
  - Integration
  - accounts-characters
  - architecture
  - agent-governance
  - security
dependencies:
  - OTERYN-20260719-phase5-identity-canary-account-binding
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/architecture/DATA_OWNERSHIP.md
blockers:
  - none for bounded decision/contract delivery; account creation plus binding implementation remains blocked by the operation-level account-create/game-login/write-boundary dependencies recorded below
cross_repository_tasks:
  - blakinio/canary is read-only evidence source; no writes are authorized in this task
  - opentibiabr/login-server is read-only evidence source; no writes are authorized in this task
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T00:26:00+02:00
head: c8b7a5187a2e4c82db13b1c357aded6e0707f00c
branch: task/OTERYN-20260719-phase5-ownership-binding-dependency-gate
pr: 29
status: ready
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
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/architecture/adr/0004-authoritative-platform-account-ownership.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-ownership-binding-dependency-gate.md
proven:
  - Oteryn Platform main is 282173f3eee372bed2cdbebe47aebd8dc5053eea, the squash merge of PR #28 post-merge Phase 5 binding-discovery housekeeping.
  - ACTIVE_WORK reported no active task and required the next work to choose between an authoritative auth-side account-control claim capability and Platform-originated Canary account creation before this task was claimed.
  - Live open-PR search returned no open Oteryn Platform pull requests before this task branch was created; PR #29 is the current task PR.
  - Current Canary main is 183d7224cb5de57585294d72631f37783b93dc89; comparison from the previous ownership-binding evidence pin 2b6ae86539640dfc52323e9d5abbde31d6610c5f contains no account/auth/schema/login-session implementation changes.
  - Current external login-server main remains 2612930de4d97123a397f8f2cd0d5f784094af40.
  - Platform Identity has no Canary account key or durable binding field/model, and the existing canary SQL connection remains the read-only oteryn_readonly boundary.
  - Current login-server password authentication is SHA-1 database matching and successful normal login creates account_sessions game-session state.
  - Current native Canary password authentication accepts Canary custom Argon2 verification and SHA-1 fallback.
  - The greenfield product decision makes Oteryn Platform authoritative for user Identity, account lifecycle and credential policy; existing Canary accounts are not migration/claim inputs.
  - The canonical ownership cardinality is exactly one Platform Identity to one Canary accounts.id and one supported Canary account to one Platform Identity.
  - Self-service unlink, rebind and transfer are not supported; normal recovery restores the same Platform Identity and retains the same immutable binding.
  - ADR 0004 records the authoritative Platform account-ownership model and selects Platform-originated Canary account creation as the primary ownership path.
  - PR #29 delivery-validation head c8b7a5187a2e4c82db13b1c357aded6e0707f00c passed Agent Governance run 29705567523 (#330) and CI run 29705567509 (#409).
derived:
  - Existing-account account-control proof is not required for the primary greenfield Phase 5 model because supported accounts originate in Platform.
  - Ownership can be established without claiming a pre-existing account only if the future Platform-originated account creation operation durably binds the exact created accounts.id to the creating Identity.
  - Current normal login paths still cannot serve as the target Platform-authoritative game-login boundary and require a separately authorized Canary/login-server integration change before the final authority model is complete.
  - Character creation remains blocked until account creation plus immutable ownership binding are implemented and tested.
unknown:
  - exact Canary account creation field/default policy for the product
  - credential representation or non-password game-login transition compatible with the authoritative Platform model
  - exact cross-database transaction/saga/compensation design for Canary account creation plus Platform binding
  - exact least-privilege Canary account-create write grants and connection configuration
  - duplicate, retry, idempotency and race semantics for account provisioning
conflicts:
  - Native Canary and external login-server do not currently share identical credential verification compatibility, which conflicts with the selected future single Platform credential authority until game-login integration is changed.
first_failure:
  marker: PLATFORM_ACCOUNT_AUTHORITY_SELECTED_ACCOUNT_PROVISIONING_CONTRACT_MISSING
  evidence: Greenfield ownership/cardinality/lifecycle are now explicit, but no approved operation-level contract yet defines safe Canary account creation, credential/game-login integration, separate write privileges and partial-failure recovery.
rejected_hypotheses:
  - Reuse normal external login as ownership proof: rejected because it creates a reusable game session and is SHA-1-only.
  - Implement Platform-side Canary password verification: rejected because it duplicates credential authority and violates the selected authoritative Platform model.
  - Claim existing Canary accounts as the primary ownership model: rejected because the product is greenfield and existing accounts are out of scope.
  - Create Canary accounts immediately from schema defaults: rejected because credential/game-login representation, operation semantics and least-privilege write boundary are not approved.
  - Allow unlink or self-service rebind: rejected because the selected 1:1 ownership binding is immutable in normal product operation.
changed_paths:
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/architecture/adr/0004-authoritative-platform-account-ownership.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-ownership-binding-dependency-gate.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: main, ACTIVE_WORK, Phase 5 archived tasks, current contracts, open PR state and current Canary/login-server heads were inspected before task claim
  - command: current Canary revision comparison
    result: PASS
    evidence: Canary 2b6ae86539640dfc52323e9d5abbde31d6610c5f..183d7224cb5de57585294d72631f37783b93dc89 is three commits ahead with no inspected account/auth/schema/login-session implementation changes
  - command: ownership direction decision review
    result: PASS
    evidence: authoritative Platform greenfield model resolves existing-account claim as out of scope and explicitly defines 1:1 cardinality, immutable normal binding, no unlink/rebind and same-Identity recovery
  - command: shared-write safety review
    result: PASS
    evidence: no Canary/login-server repository was modified; no binding persistence, account creation, credential migration or character/shared write was implemented
  - command: delivery-validation Agent Governance run 29705567523 (#330)
    result: PASS
    evidence: exact delivery-validation head c8b7a5187a2e4c82db13b1c357aded6e0707f00c completed successfully
  - command: delivery-validation CI run 29705567509 (#409)
    result: PASS
    evidence: exact delivery-validation head c8b7a5187a2e4c82db13b1c357aded6e0707f00c completed successfully, including formatting, static analysis and tests
blockers:
  - account creation plus binding implementation remains blocked pending a bounded Platform-originated Canary account creation operation contract and compatible game-login authority/write-boundary design
next_action: Revalidate CI and Agent Governance on the final ready-checkpoint head, inspect final diff/review/base divergence, then squash-merge PR #29 only if the merge gate remains clean.
```

## Notes

This task is a decision and dependency gate only. It selects the greenfield authoritative Platform ownership model but must not create a durable binding, Canary account, game session or any user-scoped shared Canary mutation.