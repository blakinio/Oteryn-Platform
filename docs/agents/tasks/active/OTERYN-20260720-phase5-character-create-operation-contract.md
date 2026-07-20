# OTERYN-20260720 Phase 5 character create operation contract

## Goal

Define the exact implementation-ready operation contract for Oteryn Platform character creation under ADR 0005: final Canary transaction ordering, exact `players` insert columns, same-account locking/limit enforcement, canonical-name retry/idempotency recovery, and a dedicated least-privilege `canary_character_create` database boundary. Do not implement the shared write in this task.

## Acceptance criteria

- [ ] Verify current Oteryn `main`, open PR state and merged ADR 0005/product-policy dependency before claiming scope.
- [ ] Revalidate current Canary `main` and confirm no player schema/load change invalidates the policy evidence pin.
- [ ] Define the exact `players` insert column set that deterministically produces starter profile v1.
- [ ] Define which untouched `players` fields may rely on current schema defaults and why.
- [ ] Define exact account-row lock and active-character count queries for the 10-character limit.
- [ ] Define deterministic global name-conflict handling under the database unique constraint.
- [ ] Define same-account retry/idempotency and ambiguous-commit forward-recovery semantics without reassigning characters.
- [ ] Define deadlock/serialization retry bounds.
- [ ] Define the exact `canary_character_create` column-level SELECT/INSERT privilege surface and forbidden privileges.
- [ ] Define fail-closed deployment privilege verification requirements.
- [ ] Define real MariaDB integration tests required before implementation merge.
- [ ] Record any proven required Canary/datapack change precisely; do not modify external repositories without separate authorization.
- [ ] Update `CHARACTER_CREATION_CONTRACT.md` to implementation-ready only if every critical invariant is proven.
- [ ] Run exact-head CI and Agent Governance before merge.

## Ownership

```yaml
owned_paths:
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-create-operation-contract.md
modules:
  - Characters
  - Accounts
  - Integration
  - database
  - security
  - testing
  - agent-governance
dependencies:
  - OTERYN-20260720-phase5-character-product-policy
  - docs/architecture/adr/0005-character-creation-product-policy.md
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
blockers:
  - none for bounded contract work
cross_repository_tasks:
  - blakinio/canary remains read-only; exact required changes, if any, must be recorded for a separately authorized task
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T08:48:09+02:00
head: 37c3d6b93359c03ee79b44300c5240e3f6c815b2
branch: task/OTERYN-20260720-phase5-character-create-operation-contract
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
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-create-operation-contract.md
proven:
  - Oteryn Platform main is 37c3d6b93359c03ee79b44300c5240e3f6c815b2, the squash merge of PR #38 character product-policy housekeeping.
  - Live open-PR search returned no open Oteryn Platform pull requests before this task branch was created.
  - ADR 0005 is merged and selects canonical name policy, starter profile v1, no dependent starter writes, maximum 10 active characters and same-account account-row locking.
  - Current Canary main is 800142e65c2975e57647bf34128ab468532218f0; the only commit after the policy evidence pin 37b41a29c8743d4c976eb7fcb82d684594722aa4 changes OAM documentation only.
  - Current players schema has one required no-default field, conditions, while selected starter skills have exact defaults 10/0 and many non-product fields have explicit safe zero/current defaults.
  - Current schema has a unique players.name constraint and an account_id foreign key; no players insert trigger is present in the inspected schema section, while the only nearby create trigger is on accounts.
  - Current player load accepts empty conditions streams and uses temple-position fallback for persisted position 0,0,0.
derived:
  - The selected starter profile can be expressed as one bounded players insert with no dependent-row writes if the contract explicitly writes every product-selected non-default starter field and relies only on revalidated deterministic defaults for unrelated state.
  - Account-row locking plus COUNT(id) on players under the same transaction can serialize the 10-active-character limit for one account.
  - Canonical name plus bound account can serve as a deterministic recovery identity for ambiguous commits if exact same-account/request-field matching is required before returning recovered success.
unknown:
  - exact smallest SELECT column set needed for recovery while preserving least privilege
  - whether implementation should automatically retry deadlocks or surface retryable unavailability after a bounded attempt count
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Reuse canary_provisioning for character writes: rejected because account provisioning and character creation have different operation-specific privilege surfaces.
  - Treat any same-name row as idempotent success: rejected because a row owned by another account or with mismatched requested creation inputs is a conflict, not recovery.
  - Add a generic players UPDATE privilege for retry recovery: rejected because recovery should read/verify the already-created immutable starter row, not mutate ownership or starter fields.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-create-operation-contract.md
validation:
  - command: contract-task preflight
    result: PASS
    evidence: main 37c3d6b93359c03ee79b44300c5240e3f6c815b2, no open PRs, ADR 0005 merged and predecessor task archived
blockers:
  - none for bounded contract work
next_action: Define the exact transaction, recovery identity and least-privilege grant matrix in CHARACTER_CREATION_CONTRACT.md, then validate the contract against current Canary schema/load semantics.
```

## Notes

This task may approve an implementation shape but must not implement character creation itself. The implementation task must still prove the DB privilege and concurrency behavior on real MariaDB.
