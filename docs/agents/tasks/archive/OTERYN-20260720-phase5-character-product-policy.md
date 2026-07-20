# OTERYN-20260720 Phase 5 character product policy

## Goal

Select and durably record the missing Oteryn greenfield product policy required to unblock the character-creation operation contract: canonical character-name rules, one exact starter profile with bounded creation-time choices, and per-account character-limit semantics. This task is product/architecture policy only; it does not implement the Canary character shared write.

## Acceptance criteria

- [x] Verify current `main`, open PR state and the merged greenfield Identity-to-Canary ownership binding before claiming scope.
- [x] Select one deterministic canonical character-name policy that is stricter than raw database uniqueness and implementable without Unicode/locale ambiguity.
- [x] Select an explicit reserved-name/impersonation policy.
- [x] Select the exact allowed creation-time vocation and sex choices and reject promoted vocations at creation.
- [x] Select one exact starter persisted-state profile compatible with current Canary schema/load behavior.
- [x] Select whether starter items, storage, quests or tutorial initialization are part of the initial product operation.
- [x] Select a hard per-account character limit and define how pending-deletion rows count.
- [x] Define concurrent-limit semantics required by the successor operation contract.
- [x] Record the durable product decision as an ADR and update `CHARACTER_CREATION_CONTRACT.md`.
- [x] Do not modify Canary/login-server repositories and do not implement character creation in this policy task.
- [x] Run exact-head CI and Agent Governance before merge.

## Ownership

```yaml
owned_paths:
  - docs/architecture/adr/0005-character-creation-product-policy.md
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-product-policy.md
modules:
  - Characters
  - Accounts
  - architecture
  - canary-integration
  - security
  - agent-governance
dependencies:
  - OTERYN-20260720-phase5-character-creation-policy-revalidation
  - docs/contracts/IDENTITY_CANARY_ACCOUNT_BINDING_CONTRACT.md
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
blockers:
  - none for merge
cross_repository_tasks:
  - blakinio/canary remains read-only; no Canary modification is part of this task
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T08:43:00+02:00
head: e5c711561388691bb2e0e2e44b42f3c6f5842588
branch: task/OTERYN-20260720-phase5-character-product-policy
pr: 37
status: ready
context_routes:
  - agent-governance
  - architecture
  - accounts-characters
  - canary-integration
  - security
owned_paths:
  - docs/architecture/adr/0005-character-creation-product-policy.md
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-product-policy.md
proven:
  - Oteryn Platform main at task start was 7d11f2cca4d10288a1d5011a3bb1e668e5a10d94, the squash merge of PR #36 character-policy revalidation housekeeping.
  - Live open-PR search returned no open Oteryn Platform pull requests before this task branch was created; PR #37 is the current task PR.
  - Greenfield immutable Platform Identity to exact Canary accounts.id ownership binding is implemented and tested through PR #33.
  - Current Canary vocation configuration proves base vocation IDs 1 Sorcerer, 2 Druid, 3 Paladin, 4 Knight and 9 Monk, while promoted vocations are separate IDs 5-8 and 10.
  - Current Canary players schema/load path accepts an empty non-null conditions stream; migration 55 demonstrates a level-8 load-shaped row with experience 4200, health 185, mana 90, cap 470 and town_id 8.
  - Current PlayerLogin compatibility behavior uses looktype 136 for sex 0 and looktype 128 for sex 1 with colors head 114, body 120, legs 132 and feet 115.
  - ADR 0005 selects deterministic ASCII canonical names, explicit reserved names, base vocation choices 1/2/3/4/9, sex 0/1, pronoun 0, starter profile v1, no starter dependent writes and maximum 10 active characters per account.
  - ADR 0005 requires same-account create concurrency to serialize by locking the exact Canary accounts row before counting active players and inserting.
  - CHARACTER_CREATION_CONTRACT records product policy as resolved and leaves only operation-contract/idempotency/least-privilege/integration proof before implementation.
  - Delivery head e5c711561388691bb2e0e2e44b42f3c6f5842588 passed CI run 29722284712 (#493), including formatting, PHPStan and full tests, and Agent Governance run 29722284656 (#414).
  - No blakinio/canary or opentibiabr/login-server repository was modified.
derived:
  - The successor character-create operation can likely remain Platform-driven with a single bounded players insert if exact current schema defaults and loadability tests confirm the selected starter profile.
  - A third dedicated canary_character_create connection is required; existing canary and canary_provisioning boundaries remain unchanged.
unknown:
  - exact final insert column allowlist after revalidating which starter fields may safely rely on current schema defaults
  - exact ambiguous-commit idempotency/recovery design
  - exact MariaDB grants required by account row locking, active count and insert
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Allow arbitrary Unicode names in the first slice: rejected because normalization/confusable policy is not proven and database collation alone is insufficient.
  - Allow promoted vocations at creation: rejected because promotions are progression state, not initial creation choices.
  - Add starter inventory/storage/quest writes by inference: rejected because no generic mandatory initializer is proven and unnecessary writes enlarge privilege surface.
  - Unlimited characters per account: rejected in favor of a deterministic 10-active-character product limit.
changed_paths:
  - docs/architecture/adr/0005-character-creation-product-policy.md
  - docs/contracts/CHARACTER_CREATION_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-product-policy.md
validation:
  - command: task preflight
    result: PASS
    evidence: main 7d11f2cca4d10288a1d5011a3bb1e668e5a10d94, no open PRs, predecessor revalidation merged and archived
  - command: product-policy evidence review
    result: PASS
    evidence: selected policy stays within current proven Canary vocation/player/load compatibility and explicitly requires successor loadability validation rather than assuming external behavior
  - command: delivery CI run 29722284712 (#493)
    result: PASS
    evidence: exact delivery head e5c711561388691bb2e0e2e44b42f3c6f5842588 passed formatting, PHPStan and full tests
  - command: delivery Agent Governance run 29722284656 (#414)
    result: PASS
    evidence: exact delivery head e5c711561388691bb2e0e2e44b42f3c6f5842588 completed successfully
blockers:
  - none for merge; final exact-head revalidation required after this checkpoint update
next_action: Revalidate CI and Agent Governance on the final checkpoint head, inspect PR #37 divergence/review state, then squash-merge if clean.
```

## Notes

This task converts previously explicit product-policy UNKNOWNs into explicit Oteryn decisions. It does not authorize the character write; the successor operation contract must still prove the exact write shape, idempotency and loadability against current Canary.
