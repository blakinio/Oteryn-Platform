# OTERYN-20260719 Phase 5 character creation contract

## Goal

Start Phase 5 with one bounded operation-contract/discovery task for Platform-driven character creation. Verify current Oteryn Platform and Canary source of truth, then prove the exact ownership, authorization, validation, transaction, concurrency, side-effect, compatibility and rollback semantics required for a safe shared `players` write. Approve only the smallest explicit character-create write contract that current evidence supports; otherwise record the exact blocker. Do not implement a shared-data write in this task.

## Acceptance criteria

- [x] Verify current `blakinio/Oteryn-Platform` `main`, confirm Phase 4 closure is complete, and confirm there is no overlapping active task or open PR before claiming the Phase 5 scope.
- [x] Verify current `blakinio/canary` `main` revision read-only and revalidate the character-creation evidence against that exact revision rather than relying on the previous contract pin.
- [x] Prove the primary owner and bound the exact `players`/dependent-state surface touched by one future Platform-driven character-create operation.
- [x] Resolve the authorization question linking authenticated Platform Identity to the target Canary account by proving the current missing durable binding as an implementation blocker.
- [x] Resolve character-name validation/normalization/uniqueness and simultaneous-create behavior to the maximum supported by current evidence, retaining product normalization/reserved-name policy as an explicit blocker while requiring the DB unique constraint as the final race guard.
- [x] Resolve creation-time vocation, sex/pronoun, town/position and starter-state rules to the maximum supported by current evidence, retaining the unproven product starter-state policy as an explicit blocker rather than treating schema defaults/sample data as product policy.
- [x] Define the safe transaction shape, uniqueness-race handling, failure semantics and rollback requirements; retain idempotency/account-limit/exact-locking details as blocked on the unresolved operation inputs.
- [x] Define currently proven online/session effects and explicitly leave dynamic character-list refresh behavior unclaimed.
- [x] Add `docs/contracts/CHARACTER_CREATION_CONTRACT.md`, pinned to the verified current Canary revision, with the exact decision that no shared character-create write is approved yet.
- [x] Update active-work handoff for the Phase 5 discovery result; leave project-level Phase completion claims unchanged because no mutation capability is implemented.
- [x] Do not modify Canary/login-server repositories and do not add a Platform shared-write implementation in this discovery task.
- [ ] Run the final repository merge gate, including fresh exact-head CI and Agent Governance on this ready checkpoint, before merge.

## Ownership

```yaml
owned_paths:
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
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
  - none for completing this discovery task; the future character-create implementation remains blocked by the contract findings
cross_repository_tasks:
  - blakinio/canary is a read-only evidence source; no writes are authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T23:12:00+02:00
head: f93b52c3a50cce20c0c5e30ba2724a54db394c1a
branch: task/OTERYN-20260719-phase5-character-creation-contract
pr: 26
status: ready
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
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/architecture/ROADMAP.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-character-creation-contract.md
proven:
  - Oteryn Platform main was verified at 838c11059694b5aa4cfdfb7923fcbbacc7c3e286, the squash merge of PR #25 Phase 4 post-merge closure housekeeping.
  - PROJECT_STATE and ROADMAP mark Phase 4 complete and require the first Phase 5 task to be a bounded operation-contract/discovery task before any shared write.
  - ACTIVE_WORK reported no active task, and live open-PR search found no overlapping Phase 5 character-creation work before this task claim.
  - Current blakinio/canary main was verified at 2b6ae86539640dfc52323e9d5abbde31d6610c5f and is read-only for this task.
  - Canary current main is six commits ahead of the previous CANARY_DATA_CONTRACT pin; the compare contains no changes to the inspected schema, account repository, player load/save or vocation paths used by this discovery.
  - Current Canary schema defines players.name as unique, players.account_id as a foreign key to accounts.id, and players.conditions as NOT NULL with no default.
  - Current Canary player loading requires resolvable account, group and vocation state and a valid town fallback; position 0,0,0 falls back to the town temple position.
  - The current Platform Identity model and identities migration contain no durable Canary account identifier or ownership binding.
  - Platform registration creates only Platform-owned Identity and security-audit state and does not create or link a Canary account.
  - Canary accounts.email is indexed but is not unique in the current schema, so email equality alone cannot prove one-to-one Identity ownership of a Canary account.
  - The existing Platform canary SQL connection remains configured as the read-only oteryn_readonly boundary; no dedicated shared-write connection exists.
  - CHARACTER_CREATION_CONTRACT records the operation as BLOCKED and approves no shared write.
  - Delivery-validation head f93b52c3a50cce20c0c5e30ba2724a54db394c1a passed CI run 29703239800 (#393) and Agent Governance run 29703239801 (#314).
  - PR #26 delivery-validation diff is limited to ACTIVE_WORK, the active task record and CHARACTER_CREATION_CONTRACT; branch comparison against main is behind_by=0 and there are no PR comments, reviews or unresolved review threads.
derived:
  - Character creation is a high-leverage first Phase 5 discovery because its authorization and initialization blockers must be solved before any user-scoped create operation can be safe.
  - The next dependency should be a bounded Identity-to-Canary-account ownership-binding contract/discovery task; matching by client-supplied account_id or email alone is not an acceptable authorization substitute.
  - A future write boundary should be a separate least-privilege connection/credential rather than broadening the existing read-only Canary connection.
unknown:
  - authoritative product character-name normalization and reserved-name rules
  - allowed creation-time vocation, sex and pronoun values
  - authoritative starting town and position policy
  - required starting level, stats, outfit, skills, items, storage and quest state
  - whether first-login scripts or other datapack hooks supply mandatory starter side effects
  - lifecycle and migration rules for a future durable Platform Identity to Canary account binding
  - account character-count limit, idempotency semantics and exact locking requirements for future simultaneous creates
  - whether an already-issued external character-list response can be refreshed without a new login/list request
conflicts: []
first_failure:
  marker: CHARACTER_CREATE_AUTHORIZATION_BINDING_MISSING
  evidence: Platform identities persist no Canary account key while Canary accounts.email is non-unique; current evidence cannot authorize a logged-in Identity to one exact accounts.id for a character write.
rejected_hypotheses:
  - Implement character creation immediately from schema defaults: rejected because current evidence does not prove product starter-state and name-policy semantics.
  - Authorize the target account using a client-supplied account_id: rejected because browser input cannot establish account ownership.
  - Bind Identity to Canary account by email equality at mutation time: rejected because Canary accounts.email is not unique and no durable binding lifecycle is defined.
  - Reuse or broaden the existing canary SQL connection for writes: rejected because it is the database-enforced read-only PublicGameData boundary.
changed_paths:
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-phase5-character-creation-contract.md
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: Platform main 838c11059694b5aa4cfdfb7923fcbbacc7c3e286, no active task/open overlapping PR, Phase 4 complete, Canary main 2b6ae86539640dfc52323e9d5abbde31d6610c5f
  - command: read-only Canary operation evidence revalidation
    result: PASS
    evidence: current schema.sql, account_repository_db.cpp, iologindata.cpp, iologindata_load_player.cpp, migration 55 and vocations.xml were inspected; previous contract pin to current main compare does not modify these inspected paths
  - command: Platform authorization/write-boundary inspection
    result: PASS
    evidence: current Identity model, identities migration, registration action and database connection configuration prove no Canary-account binding and an existing read-only Canary SQL boundary
  - command: delivery-validation GitHub Actions CI run 29703239800 (#393)
    result: PASS
    evidence: exact delivery-validation head f93b52c3a50cce20c0c5e30ba2724a54db394c1a completed successfully
  - command: delivery-validation Agent Governance run 29703239801 (#314)
    result: PASS
    evidence: exact delivery-validation head f93b52c3a50cce20c0c5e30ba2724a54db394c1a completed successfully
blockers:
  - none for discovery merge; future character-create implementation is blocked by missing Identity-to-Canary-account binding and unresolved product starter/name policy
next_action: Revalidate CI and Agent Governance on the final ready-checkpoint head, then perform the full PR #26 merge gate and squash-merge only if main divergence, final diff and review state remain clean.
```

## Notes

This is the mandatory first Phase 5 operation-contract/discovery task. The evidence does not authorize character creation yet. The next dependency is an explicit Identity-to-Canary-account ownership-binding contract; character-create implementation also remains blocked until starter-state and name policy are defined.
