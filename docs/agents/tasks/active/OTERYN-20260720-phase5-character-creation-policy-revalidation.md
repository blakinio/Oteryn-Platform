# OTERYN-20260720 Phase 5 character creation policy revalidation

## Goal

Revalidate the remaining character-creation blockers after greenfield Identity-to-Canary ownership binding was implemented. Use current Oteryn Platform and current Canary source/datapack/schema to determine whether character naming, starter-state, initialization side effects, transaction semantics and least-privilege write scope can now be explicitly approved. Do not implement character creation in this task unless every required product/Canary invariant is proven and the operation contract becomes implementation-ready.

## Acceptance criteria

- [ ] Verify current Oteryn Platform main, open PR state and merged ownership-binding implementation before claiming scope.
- [ ] Revalidate current Canary main and compare relevant player/schema/datapack/login-hook paths against the previous character-contract evidence pin.
- [ ] Update the character-creation contract to mark Identity-to-Canary ownership authorization resolved for greenfield ready bindings.
- [ ] Inspect current name validation/canonicalization/reserved-name behavior in Canary and product source; do not infer policy from database uniqueness alone.
- [ ] Inspect current first-login/player-login/datapack initialization behavior, towns, vocations and sample/default player state.
- [ ] Prove or explicitly block the exact initial players field set and mandatory dependent writes.
- [ ] Prove or explicitly block account character-limit, transaction, idempotency, concurrency and retry semantics.
- [ ] Define the narrowest possible dedicated character-create DB privilege surface only if the exact atomic write set is proven.
- [ ] Record any required future Canary changes precisely in durable history; do not modify Canary without separate authorization.
- [ ] Keep character creation fail-closed if any critical product policy or game-side invariant remains unproven.
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
  - none for bounded revalidation; character-create implementation remains conditional on findings
cross_repository_tasks:
  - blakinio/canary is read-only evidence source; any required code/schema/datapack change must be recorded for a separately authorized task
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T02:00:00+02:00
head: UNKNOWN
branch: task/OTERYN-20260720-phase5-character-creation-policy-revalidation
pr: none
status: investigating
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
  - Oteryn Platform main is 92a5aea7c79c15e1daf0f3c9b40c7019b49c6fd8, the squash merge of PR #34 post-merge provisioning implementation housekeeping.
  - Live open-PR search returned no open Oteryn Platform pull requests before this task branch was created.
  - PR #33 implemented and tested the greenfield immutable Platform Identity to exact Canary accounts.id binding; the original ownership authorization blocker is resolved for ready bindings.
derived:
  - Character creation authorization may now resolve the target account only from the authenticated Identity's ready Platform-owned binding.
unknown:
  - authoritative product character-name policy
  - authoritative starter-state and exact required dependent initialization writes
  - character-limit/idempotency/locking policy
  - exact character-create DB privilege allowlist
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Treat schema defaults as product starter policy: rejected by the existing character contract.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-creation-policy-revalidation.md
validation:
  - command: successor task preflight
    result: PASS
    evidence: main 92a5aea7c79c15e1daf0f3c9b40c7019b49c6fd8, no open PRs, ownership binding implemented and predecessor task archived
blockers:
  - none for discovery
next_action: Open the draft PR and inspect current Canary name validation, player schema/load/save, towns/vocations and login/datapack initialization hooks before updating the character-create contract.
```

## Notes

This task must distinguish source-proven game invariants from product decisions. It must not silently convert schema defaults, sample characters or incidental datapack behavior into product policy.
