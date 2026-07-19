# OTERYN-20260719 Phase 5 character creation contract

## Goal

Start Phase 5 with one bounded operation-contract/discovery task for Platform-driven character creation. Verify current Oteryn Platform and Canary source of truth, then prove the exact ownership, authorization, validation, transaction, concurrency, side-effect, compatibility and rollback semantics required for a safe shared `players` write. Approve only the smallest explicit character-create write contract that current evidence supports; otherwise record the exact blocker. Do not implement a shared-data write in this task.

## Acceptance criteria

- [ ] Verify current `blakinio/Oteryn-Platform` `main`, confirm Phase 4 closure is complete, and confirm there is no overlapping active task or open PR before claiming the Phase 5 scope.
- [ ] Verify current `blakinio/canary` `main` revision read-only and revalidate the character-creation evidence against that exact revision rather than relying on the previous contract pin.
- [ ] Prove the primary owner and exact `players` fields/dependent structures touched by one Platform-driven character-create operation.
- [ ] Prove the authorization rule linking the authenticated Platform Identity to the target Canary account without broadening credential/game-login migration scope.
- [ ] Prove character-name validation/normalization/uniqueness semantics and database concurrency behavior for simultaneous creates.
- [ ] Prove allowed creation-time vocation, sex/pronoun, town/position and starter-state rules, including any first-login or dependent-row/item/storage side effects required by the current Oteryn datapack.
- [ ] Define the transaction boundary, locking/idempotency behavior, failure semantics and rollback/retry contract for the operation.
- [ ] Define online/session restrictions and any Canary cache/runtime invalidation requirements relevant to creation.
- [ ] Update `docs/contracts/CANARY_DATA_CONTRACT.md` with evidence pinned to the verified current Canary revision and either an explicitly approved bounded character-create operation or an exact blocker.
- [ ] Update `PROJECT_STATE.md`, `ACTIVE_WORK.md` and `ROADMAP.md` only as required by the Phase 5 discovery result; do not claim a mutation implementation exists.
- [ ] Do not modify Canary/login-server repositories and do not add a Platform shared-write implementation in this discovery task.
- [ ] Run the repository merge gate, including exact-head CI and Agent Governance, before readiness/merge.

## Ownership

```yaml
owned_paths:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/architecture/ROADMAP.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-character-creation-contract.md
modules:
  - accounts-characters
  - Integration
  - architecture
  - agent-governance
  - security
dependencies:
  - OTERYN-20260719-phase4-public-read-closure
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
blockers:
  - none
cross_repository_tasks:
  - blakinio/canary is a read-only evidence source; no writes are authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T22:44:00+02:00
head: UNKNOWN
branch: task/OTERYN-20260719-phase5-character-creation-contract
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
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/architecture/ROADMAP.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-character-creation-contract.md
proven:
  - Oteryn Platform main was verified at 838c11059694b5aa4cfdfb7923fcbbacc7c3e286, the squash merge of PR #25 Phase 4 post-merge closure housekeeping.
  - PROJECT_STATE and ROADMAP mark Phase 4 complete and require the first Phase 5 task to be a bounded operation-contract/discovery task before any shared write.
  - ACTIVE_WORK reports no active task, and live open-PR search found no overlapping Phase 5 character-creation work before this task claim.
  - Current blakinio/canary main was verified at 2b6ae86539640dfc52323e9d5abbde31d6610c5f and is read-only for this task.
  - Current Canary schema still defines players.name as unique, players.account_id as a foreign key to accounts.id, and players.conditions as NOT NULL with no default.
  - Current Canary player loading requires resolvable account, group and vocation state and a valid town fallback; position 0,0,0 falls back to the town temple position.
  - Existing CANARY_DATA_CONTRACT still marks Platform character creation UNKNOWN / NOT APPROVED pending product initialization and concurrency semantics.
derived:
  - Character creation is the highest-leverage first Phase 5 discovery because it is a roadmap deliverable and its unresolved initialization rules are already the explicit shared-write blocker recorded on main.
unknown:
  - authoritative product character-name normalization and reserved-name rules
  - allowed creation-time vocation, sex and pronoun values
  - authoritative starting town and position policy
  - required starting level, stats, outfit, skills, items, storage and quest state
  - whether first-login scripts or other datapack hooks supply mandatory starter side effects
  - exact authenticated Platform Identity to Canary account ownership mapping permitted for this operation
  - simultaneous-create transaction and retry/idempotency contract beyond the database unique constraint
  - whether character creation requires any runtime cache or session invalidation
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Implement character creation immediately from schema defaults: rejected because current contract explicitly marks product initialization and concurrency semantics unproven.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260719-phase5-character-creation-contract.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: Platform main 838c11059694b5aa4cfdfb7923fcbbacc7c3e286, no active task/open overlapping PR, Phase 4 complete, Canary main 2b6ae86539640dfc52323e9d5abbde31d6610c5f
  - command: initial read-only Canary character schema/load revalidation
    result: PASS
    evidence: schema.sql, src/io/iologindata.cpp, src/io/functions/iologindata_load_player.cpp and migration 55 were inspected at current Canary main
blockers:
  - none
next_action: Open the draft PR for this claimed discovery task, then inspect current Canary datapack and account ownership paths to resolve or precisely bound the remaining character-create contract unknowns.
```

## Notes

This is the mandatory first Phase 5 operation-contract/discovery task. It may approve a narrowly defined future Platform character-create mutation only when current source proves the full operation contract. It does not itself grant database write privileges or implement any Canary/shared-data mutation.
