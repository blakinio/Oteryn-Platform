# OTERYN-20260719 Phase 5 Identity-to-Canary account binding contract

## Goal

Resolve the authorization blocker discovered by the first Phase 5 character-creation contract. Define how one authenticated Oteryn Platform Identity can be durably and unambiguously associated with exactly one Canary `accounts.id` without guessing ownership from non-unique email, trusting browser-supplied account IDs, or silently migrating the still-separate game-login credential model. Approve only an evidence-backed binding/claim boundary or record the exact blocker. Do not implement shared Canary writes in this discovery task.

## Acceptance criteria

- [ ] Archive the merged `OTERYN-20260719-phase5-character-creation-contract` task under `docs/agents/tasks/archive/` with exact historical content and remove the active copy.
- [ ] Verify current Oteryn Platform `main`, open PR state and current Canary `main` before claiming scope.
- [ ] Inspect current Platform Identity persistence, registration/login/session/MFA/recovery boundaries and any existing account-link model.
- [ ] Inspect current Canary account identity keys and current authentication/control-proof paths relevant to proving ownership of one `accounts.id`.
- [ ] Compare viable binding ceremonies: durable Platform-owned mapping established by authoritative account-control proof, new-account creation with immediate binding, existing-account claim, and privileged/manual recovery.
- [ ] Reject any binding rule that relies only on client-supplied account IDs, non-unique email equality, or an unproven password-verification compatibility path.
- [ ] Define one-to-one/cardinality constraints, lifecycle/unlink/rebind rules, concurrency/idempotency semantics, audit requirements and rollback/migration implications for the binding itself.
- [ ] Update or add an operation-specific contract with an approved bounded binding mechanism or the exact blocker and next dependency.
- [ ] Update `ACTIVE_WORK.md` with the current task and next action; do not claim character/account mutation authorization until binding is actually implemented and proven.
- [ ] Do not modify Canary/login-server repositories and do not implement shared Canary account/player writes.
- [ ] Run exact-head CI and Agent Governance before readiness/merge.

## Ownership

```yaml
owned_paths:
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-identity-canary-account-binding.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-character-creation-contract.md
  - docs/agents/tasks/archive/OTERYN-20260719-phase5-character-creation-contract.md
modules:
  - Identity
  - accounts-characters
  - Integration
  - architecture
  - agent-governance
  - security
dependencies:
  - OTERYN-20260719-phase5-character-creation-contract
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
blockers:
  - none for discovery; a safe binding mechanism may remain blocked by current cross-component authentication authority
cross_repository_tasks:
  - blakinio/canary is read-only evidence source; no writes are authorized
  - opentibiabr/login-server may be inspected read-only only if required by the authentication-control proof
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T23:25:00+02:00
head: 8e4d572c4136672866d043e2af989c2bcff9be44
branch: task/OTERYN-20260719-phase5-identity-canary-account-binding
pr: none
status: investigating
context_routes:
  - agent-governance
  - architecture
  - identity-auth
  - accounts-characters
  - canary-integration
  - security
  - database
  - testing
owned_paths:
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-identity-canary-account-binding.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-character-creation-contract.md
  - docs/agents/tasks/archive/OTERYN-20260719-phase5-character-creation-contract.md
proven:
  - PR #26 was squash-merged to main as ab78d6ac3bc674deb0868195563b61a753d95f98 after exact-head CI and Agent Governance passed and the final merge gate remained clean.
  - Live open-PR search after PR #26 merge returned no open pull requests before this successor task was claimed.
  - The merged character-creation contract identifies missing durable Platform Identity to Canary accounts.id ownership binding as the first authorization blocker.
  - The archived predecessor task was copied from exact merged blob c66f27a5a04e272c35e20b58509acccce7cec933 before removal of its active copy.
derived:
  - A Platform-owned durable mapping can represent ownership only after a separate trustworthy account-control proof establishes the initial association.
unknown:
  - whether any current cross-component path can safely prove control of an existing Canary account without introducing credential compatibility or bypass risks
  - whether product policy allows one Identity to own exactly one Canary account or multiple accounts
  - unlink/rebind/recovery policy and privileged break-glass ownership
  - whether new Canary account creation is intended to be the only automatic binding path
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Bind by email equality alone: already rejected by the character-creation contract because Canary accounts.email is non-unique.
  - Trust browser-supplied accounts.id: rejected because client input cannot prove ownership.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260719-phase5-character-creation-contract.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-character-creation-contract.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-identity-canary-account-binding.md
validation:
  - command: successor task claim preflight
    result: PASS
    evidence: main at ab78d6ac3bc674deb0868195563b61a753d95f98 and no open PR before branch creation
blockers:
  - none for discovery
next_action: Open the draft PR, update ACTIVE_WORK to the successor task, then inspect the current account-control proof options and define or reject a safe binding ceremony.
```

## Notes

This task resolves authorization ownership only. It must not be used to bypass the separate credential/game-login authority blockers or to grant character/account mutation capability prematurely.
