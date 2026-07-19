# OTERYN-20260719 Phase 5 ownership binding dependency gate

## Goal

Revalidate the two explicitly allowed dependency directions for establishing a trustworthy `Platform Identity -> authorized Canary accounts.id` ownership binding, using only current Oteryn Platform, Canary and login-server evidence. Approve a Platform implementation only if all critical assumptions are proven; otherwise document the exact blocker, identify the nearest minimal dependency and keep all user-scoped Canary writes fail-closed.

## Acceptance criteria

- [ ] Verify current Oteryn Platform `main`, active task state, open PRs and recent Phase 5 Git/task history before claiming scope.
- [ ] Re-read the current Phase 5 ownership-binding, Canary data, auth/game-login and data-ownership contracts.
- [ ] Revalidate current Platform Identity persistence, existing account mappings, Canary database connections, privilege boundaries and shared-write infrastructure.
- [ ] Revalidate current Canary/login-server account identification and credential verification paths against current repository heads.
- [ ] Evaluate exactly two directions: authoritative account-control proof and Platform-originated Canary account creation.
- [ ] For account-control proof, verify whether an existing short-lived, single-use, replay-resistant `accounts.id`-bound proof can be obtained and consumed by Platform without creating a game session or duplicating credential verification.
- [ ] For Platform-originated account creation, verify whether account-creation fields, credential storage compatibility, transaction/failure semantics, least-privilege write boundary and ownership lifecycle/cardinality are all explicitly approved.
- [ ] If neither direction is ready, update the ownership-binding contract with exact evidence, missing prerequisites, nearest minimal dependency, owning component/repository and separate-authorization requirement.
- [ ] Do not implement character creation/deletion/rename, ownership binding persistence, Canary account creation, credential migration or any shared Canary write.
- [ ] Run exact-head CI and Agent Governance before readiness/merge.

## Ownership

```yaml
owned_paths:
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-ownership-binding-dependency-gate.md
modules:
  - Identity
  - Accounts
  - Integration
  - accounts-characters
  - agent-governance
  - security
dependencies:
  - OTERYN-20260719-phase5-identity-canary-account-binding
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/architecture/DATA_OWNERSHIP.md
blockers:
  - none for bounded discovery; ownership-binding implementation may remain blocked by external authentication capability and unresolved product ownership policy
cross_repository_tasks:
  - blakinio/canary is read-only evidence source; no writes are authorized in this task
  - opentibiabr/login-server is read-only evidence source; no writes are authorized in this task
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T23:00:00+02:00
head: UNKNOWN
branch: task/OTERYN-20260719-phase5-ownership-binding-dependency-gate
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
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-ownership-binding-dependency-gate.md
proven:
  - Oteryn Platform main is 282173f3eee372bed2cdbebe47aebd8dc5053eea, the squash merge of PR #28 post-merge Phase 5 binding-discovery housekeeping.
  - ACTIVE_WORK reports no active task and requires the next work to choose between an authoritative auth-side account-control claim capability and Platform-originated Canary account creation.
  - Live open-PR search returned no open Oteryn Platform pull requests before this task branch was created.
  - Current Canary main is 183d7224cb5de57585294d72631f37783b93dc89; comparison from the previous ownership-binding evidence pin 2b6ae86539640dfc52323e9d5abbde31d6610c5f contains no account/auth/schema/login-session implementation changes.
  - Current external login-server main remains 2612930de4d97123a397f8f2cd0d5f784094af40.
  - Platform Identity has no Canary account key or durable binding field/model, and the existing canary SQL connection remains the read-only oteryn_readonly boundary.
  - Current login-server password authentication is SHA-1 database matching and successful normal login creates account_sessions game-session state.
  - Current native Canary password authentication accepts Canary custom Argon2 verification and SHA-1 fallback.
derived:
  - Existing normal login paths cannot currently serve as the required side-effect-free universal account-control proof boundary.
  - Platform-originated account creation cannot be approved while credential storage compatibility, operation-level account creation semantics, ownership cardinality/lifecycle and a separate least-privilege write credential remain unresolved.
unknown:
  - product ownership cardinality between Platform Identity and Canary accounts
  - unlink, transfer, rebind and recovery policy
  - final authoritative component and transport for a purpose-built account-control proof capability
conflicts:
  - Native Canary and external login-server do not currently share identical credential verification compatibility.
first_failure:
  marker: NEITHER_OWNERSHIP_BINDING_DIRECTION_IMPLEMENTABLE_ON_CURRENT_PLATFORM_ONLY_BOUNDARY
  evidence: Account-control proof requires a missing auth-side capability, while Platform-originated account creation lacks approved credential/account-create/lifecycle contracts and write infrastructure.
rejected_hypotheses:
  - Reuse normal external login as ownership proof: rejected because it creates a reusable game session and is SHA-1-only.
  - Implement Platform-side password verification: rejected because it duplicates credential authority and would require access to credential-sensitive Canary state.
  - Create Canary accounts immediately from schema defaults: rejected because credential compatibility, ownership lifecycle/cardinality, operation semantics and least-privilege write boundary are not approved.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260719-phase5-ownership-binding-dependency-gate.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: main, ACTIVE_WORK, Phase 5 archived tasks, current contracts, open PR state and current Canary/login-server heads were inspected before task claim
blockers:
  - none for discovery; implementation remains blocked pending this task's final direction decision
next_action: Open a draft PR, then update the ownership-binding contract and project handoff with the exact two-direction decision and minimal external dependency.
```

## Notes

This task is a decision and dependency gate only. It must not create a durable binding, Canary account, game session or any user-scoped shared Canary mutation.
