# OTERYN-20260720 Phase 5 character creation policy revalidation

## Goal

Revalidate the remaining character-creation blockers after greenfield Identity-to-Canary ownership binding was implemented. Use current Oteryn Platform and current Canary source/datapack/schema to determine whether character naming, starter-state, initialization side effects, transaction semantics and least-privilege write scope can now be explicitly approved. Do not implement character creation in this task unless every required product/Canary invariant is proven and the operation contract becomes implementation-ready.

## Acceptance criteria

- [x] Verify current Oteryn Platform main, open PR state and merged ownership-binding implementation before claiming scope.
- [x] Revalidate current Canary main and compare relevant player/schema/datapack/login-hook paths against the previous character-contract evidence pin.
- [x] Update the character-creation contract to mark Identity-to-Canary ownership authorization resolved for greenfield ready bindings.
- [x] Inspect current name validation/canonicalization/reserved-name behavior in Canary and product source; do not infer policy from database uniqueness alone.
- [x] Inspect current player load and global login/datapack initialization behavior plus schema defaults relevant to starter state.
- [x] Prove or explicitly block the exact initial players field set and mandatory dependent writes.
- [x] Prove or explicitly block account character-limit, transaction, idempotency, concurrency and retry semantics.
- [x] Define the narrowest possible dedicated character-create DB privilege surface only if the exact atomic write set is proven; otherwise retain it as blocked.
- [x] Record any required future Canary changes precisely in durable history; do not modify Canary without separate authorization.
- [x] Keep character creation fail-closed because critical product policy remains unproven.
- [ ] Run exact-head CI and Agent Governance before merge.

## Ownership

```yaml
owned_paths:
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-creation-policy-revalidation.md
modules:
  - Characters
  - Accounts
  - Integration
  - architecture
  - database
  - security
  - agent-governance
dependencies:
  - OTERYN-20260720-phase5-platform-account-provisioning-implementation
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/architecture/DATA_OWNERSHIP.md
blockers:
  - none for bounded revalidation; character-create implementation is blocked by explicit product policy decisions recorded in the contract
cross_repository_tasks:
  - blakinio/canary is read-only evidence source; no current Canary change is proven necessary, but any future mandatory game-side initialization must be a separately authorized task with exact hook/idempotency/rollout/test contract
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T02:18:00+02:00
head: 5f1c4e2321577707ef5789b885c3899b5b3a0dfe
branch: task/OTERYN-20260720-phase5-character-creation-policy-revalidation
pr: 35
status: validating
context_routes:
  - agent-governance
  - architecture
  - accounts-characters
  - canary-integration
  - database
  - security
  - testing
owned_paths:
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-creation-policy-revalidation.md
proven:
  - Oteryn Platform main at task start was 92a5aea7c79c15e1daf0f3c9b40c7019b49c6fd8, the squash merge of PR #34 post-merge provisioning implementation housekeeping.
  - Live open-PR search returned no open Oteryn Platform pull requests before this task branch was created; PR #35 is the current task PR.
  - PR #33 implemented and tested the greenfield immutable Platform Identity to exact Canary accounts.id binding; character-create authorization can now derive the target account only from the authenticated Identity's ready Platform-owned binding.
  - Current Canary main is 37b41a29c8743d4c976eb7fcb82d684594722aa4; changes since the previous provisioning evidence pin add E2E vocation persistence coverage and do not change the inspected players schema/load/global login-hook semantics.
  - Current players schema requires unique name, existing account_id and non-null conditions, while many gameplay fields have defaults; these defaults are compatibility facts rather than Oteryn starter policy.
  - Current player preload/load requires a resolvable account, group and vocation; town must resolve or current fallback logic applies, and persisted position 0,0,0 falls back to the temple position.
  - Inspected global login event registration and PlayerLogin hooks do not define a generic first-login starter kit or mandatory initialization contract for Platform-created characters.
  - No purpose-built current Canary web character-create/name-policy validator was proven in the inspected source.
  - Current schema enforces exact stored-name uniqueness but does not define Oteryn normalization, allowed characters, reserved names or visually-confusable policy.
  - Current schema does not enforce a maximum number of characters per account.
  - CHARACTER_CREATION_CONTRACT now records ownership authorization as resolved and character implementation as blocked only by product naming/starter/character-limit decisions plus the dependent write/grant surface derived from them.
  - No blakinio/canary repository modification was made.
derived:
  - The exact character-create INSERT/dependent write set cannot be approved before the product starter-state policy is selected.
  - The exact least-privilege character-create DB grants cannot be approved before the mandatory atomic write set is known.
  - A future selected starter policy can remain Platform-driven without Canary changes if all mandatory initialization can be represented safely in one bounded Canary transaction.
  - If mandatory initialization requires a game-side hook, that exact Canary/datapack change must be separately authorized and made idempotent/testable rather than relying on incidental onLogin behavior.
unknown:
  - authoritative Oteryn character-name normalization, allowed-character and reserved-name policy
  - authoritative Oteryn starter-state and creation-time choice policy
  - maximum characters per account and whether deleted/pending characters count toward the limit
  - exact mandatory dependent initialization writes and resulting operation-specific DB grants
conflicts: []
first_failure:
  marker: CHARACTER_CREATE_PRODUCT_POLICY_MISSING
  evidence: ownership is implemented, but current repositories do not select the product naming, starter-state or character-limit rules needed to determine a safe exact character-create transaction and privilege surface.
rejected_hypotheses:
  - Treat schema defaults or sample characters as product starter policy: rejected because they prove compatibility, not product intent.
  - Treat database name uniqueness as the complete name policy: rejected because it does not define normalization, reserved names or allowed user input.
  - Rely on current global onLogin scripts to initialize arbitrary minimal player rows: rejected because the inspected hooks do not prove a generic starter-state contract.
  - Broaden the existing canary or canary_provisioning credentials for character writes: rejected because character creation requires its own exact operation-specific least-privilege boundary after the write set is known.
changed_paths:
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-creation-policy-revalidation.md
validation:
  - command: successor task preflight
    result: PASS
    evidence: main 92a5aea7c79c15e1daf0f3c9b40c7019b49c6fd8, no open PRs, ownership binding implemented and predecessor task archived
  - command: current Canary revision comparison
    result: PASS
    evidence: current main 37b41a29c8743d4c976eb7fcb82d684594722aa4 revalidated; intervening changes do not alter inspected character-create schema/load/login-hook evidence
  - command: character authorization revalidation
    result: PASS
    evidence: ready identity_canary_accounts binding now supplies the exact authenticated Identity to Canary accounts.id authorization boundary
  - command: starter/name policy evidence review
    result: PASS
    evidence: current schema, player load paths and global login hooks distinguish engine compatibility from still-missing product policy without assuming defaults as intent
blockers:
  - character-create implementation remains blocked by explicit Oteryn product decisions for naming, starter state and account character limit
next_action: Run exact-head CI and Agent Governance for PR #35, inspect final diff/review/base divergence, then squash-merge the bounded revalidation if the merge gate remains clean.
```

## Notes

This task resolves the stale ownership blocker in the character-create contract and narrows the remaining gate to explicit product policy. It intentionally does not invent those product decisions from SQL defaults, sample data or incidental datapack behavior.
