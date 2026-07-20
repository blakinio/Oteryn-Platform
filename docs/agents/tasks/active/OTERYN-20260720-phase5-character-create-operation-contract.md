# OTERYN-20260720 Phase 5 character create operation contract

## Goal

Define the exact implementation-ready operation contract for Oteryn Platform character creation under ADR 0005: final Canary transaction ordering, exact `players` insert columns, same-account locking/limit enforcement, canonical-name retry/idempotency recovery, and a dedicated least-privilege `canary_character_create` database boundary. Do not implement the shared write in this task.

## Acceptance criteria

- [x] Verify current Oteryn `main`, open PR state and merged ADR 0005/product-policy dependency before claiming scope.
- [x] Revalidate current Canary `main` and confirm no player schema/load change invalidates the policy evidence pin.
- [x] Define the exact `players` insert column set that deterministically produces starter profile v1.
- [x] Define which untouched `players` fields may rely on current schema defaults and why.
- [x] Define exact account-row lock and active-character count queries for the 10-character limit.
- [x] Define deterministic global name-conflict handling under the database unique constraint.
- [x] Define same-account retry/idempotency and ambiguous-commit forward-recovery semantics without reassigning characters.
- [x] Define deadlock/serialization retry bounds.
- [x] Define the exact `canary_character_create` column-level SELECT/INSERT privilege surface and forbidden privileges.
- [x] Define fail-closed deployment privilege verification requirements.
- [x] Define real MariaDB integration tests required before implementation merge.
- [x] Record any proven required Canary/datapack change precisely; do not modify external repositories without separate authorization.
- [x] Update `CHARACTER_CREATION_CONTRACT.md` to implementation-ready only if every critical invariant is proven.
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
  - blakinio/canary remains read-only; no Canary change is currently proven necessary for the approved v1 operation
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T08:56:00+02:00
head: b05f1b41be8607cdc6ddd38ea3019dbb1c95c607
branch: task/OTERYN-20260720-phase5-character-create-operation-contract
pr: 39
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
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-create-operation-contract.md
proven:
  - Oteryn Platform main at task start was 37c3d6b93359c03ee79b44300c5240e3f6c815b2, the squash merge of PR #38 character product-policy housekeeping.
  - Live open-PR search returned no open Oteryn Platform pull requests before this task branch was created; PR #39 is the current task PR.
  - ADR 0005 is merged and selects canonical name policy, starter profile v1, no dependent starter writes, maximum 10 active characters and same-account account-row locking.
  - Current Canary main is 800142e65c2975e57647bf34128ab468532218f0; the only commit after the policy evidence pin changes OAM documentation only.
  - Current players schema has one required starter-relevant no-default field, conditions; selected classic skills have exact defaults 10/0 and omitted unrelated state has explicit current defaults or nullable semantics.
  - Current schema has unique players.name and players.account_id foreign key; no generic players insert trigger was proven.
  - Current player load accepts empty conditions streams and uses temple-position fallback for persisted position 0,0,0.
  - CHARACTER_CREATION_CONTRACT now approves one exact 42-column players INSERT and no dependent-row writes.
  - The exact transaction locks `accounts.id`, classifies exact canonical-name state, recovers same-account active names idempotently before quota evaluation, counts deletion=0 players, enforces limit 10, then inserts and returns generated player ID.
  - V1 natural operation identity is `(authorized accounts.id, canonical players.name)`; repeated same-account active-name requests are read-only idempotent success and never update vocation, sex, ownership or gameplay state.
  - Different-account same name and same-account deleted same name are deterministic name conflicts.
  - Ambiguous commit recovery reruns the same operation and recovers only a same-account active canonical-name row; no destructive compensation or UPDATE privilege is required.
  - Transient deadlock/serialization errors may retry the whole transaction up to three total attempts; permanent conflicts are never automatically retried.
  - Approved SELECT grants are only accounts(id) and players(id,name,account_id,deletion).
  - Approved INSERT grants are only the exact starter-row columns listed in the contract; UPDATE, DELETE, table-level SELECT, unrelated tables, sessions, credentials, DDL and GRANT OPTION are forbidden.
  - Real MariaDB integration coverage is mandatory for column-level grants, FOR UPDATE, active-limit races, global name races, idempotent recovery, privilege denial and exact starter/default row shape.
  - No blakinio/canary or opentibiabr/login-server code change is currently required by this operation contract.
derived:
  - The successor implementation can remain entirely in Oteryn Platform plus deployment provisioning of a dedicated database principal.
  - A same-account canonical name is the v1 immutable create target; vocation/sex are first-creation inputs and repeated requests must not mutate an existing character.
unknown:
  - none blocking implementation; real MariaDB tests must prove the proposed column-level grants support the exact FOR UPDATE and COUNT queries before merge
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Reuse canary_provisioning for character writes: rejected because account provisioning and character creation require different operation-specific privileges.
  - Use a client-supplied idempotency key as the primary recovery identity: rejected for v1 because bound account plus globally unique canonical name already defines the immutable create target.
  - Compare mutable vocation/sex on every same-name retry: rejected because existing characters may later progress or change profile state; retry recovery is ownership/name based and never mutates the existing row.
  - Add generic players UPDATE privilege for retry recovery: rejected because recovery reads/verifies the active same-account row and performs no mutation.
changed_paths:
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-create-operation-contract.md
validation:
  - command: contract-task preflight
    result: PASS
    evidence: main 37c3d6b93359c03ee79b44300c5240e3f6c815b2, no open PRs, ADR 0005 merged and predecessor task archived
  - command: current Canary schema/load revalidation
    result: PASS
    evidence: current main 800142e65c2975e57647bf34128ab468532218f0 differs from the ADR evidence pin only by OAM documentation and preserves the inspected player schema/load paths
  - command: operation contract review
    result: PASS
    evidence: authorization, exact insert, lock/count ordering, natural idempotency, ambiguous-commit recovery, bounded transient retries, exact grants and required real MariaDB tests are explicit
blockers:
  - none pending repository validation
next_action: Update ACTIVE_WORK with the approved contract result, then run exact-head CI and Agent Governance and merge PR #39 only if the final gate is clean.
```

## Notes

This task approves the implementation shape but does not implement character creation itself. The implementation task must prove the database privilege and concurrency behavior on real MariaDB before character creation can be unblocked.
