# OTERYN-20260720 Phase 5 character product policy

## Goal

Select and durably record the missing Oteryn greenfield product policy required to unblock the character-creation operation contract: canonical character-name rules, one exact starter profile with bounded creation-time choices, and per-account character-limit semantics. This task is product/architecture policy only; it does not implement the Canary character shared write.

## Acceptance criteria

- [ ] Verify current `main`, open PR state and the merged greenfield Identity-to-Canary ownership binding before claiming scope.
- [ ] Select one deterministic canonical character-name policy that is stricter than raw database uniqueness and implementable without Unicode/locale ambiguity.
- [ ] Select an explicit reserved-name/impersonation policy.
- [ ] Select the exact allowed creation-time vocation and sex choices and reject promoted vocations at creation.
- [ ] Select one exact starter persisted-state profile compatible with current Canary schema/load behavior.
- [ ] Select whether starter items, storage, quests or tutorial initialization are part of the initial product operation.
- [ ] Select a hard per-account character limit and define how pending-deletion rows count.
- [ ] Define concurrent-limit semantics required by the successor operation contract.
- [ ] Record the durable product decision as an ADR and update `CHARACTER_CREATION_CONTRACT.md`.
- [ ] Do not modify Canary/login-server repositories and do not implement character creation in this policy task.
- [ ] Run exact-head CI and Agent Governance before merge.

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
  - none; the user explicitly authorized autonomous continuation and the missing items are Oteryn product decisions rather than unknown external behavior
cross_repository_tasks:
  - blakinio/canary remains read-only; no Canary modification is part of this task
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T08:34:49+02:00
head: 7d11f2cca4d10288a1d5011a3bb1e668e5a10d94
branch: task/OTERYN-20260720-phase5-character-product-policy
pr: none
status: investigating
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
  - Oteryn Platform main is 7d11f2cca4d10288a1d5011a3bb1e668e5a10d94, the squash merge of PR #36 character-policy revalidation housekeeping.
  - Live open-PR search returned no open Oteryn Platform pull requests before this task branch was created.
  - Greenfield immutable Platform Identity to exact Canary accounts.id ownership binding is implemented and tested through PR #33.
  - Current character contract says ownership is resolved and the remaining gate is product naming, starter-state and character-limit policy.
  - Current Canary vocation configuration proves base vocation IDs 1 Sorcerer, 2 Druid, 3 Paladin, 4 Knight and 9 Monk, while promoted vocations are separate IDs 5-8 and 10.
  - Current Canary players schema and load path accept a non-null empty conditions blob, and current migration 55 demonstrates a load-shaped level-8 row with experience 4200, health 185, mana 90, cap 470 and town_id 8.
  - Current PlayerLogin compatibility path uses looktype 136 for sex 0 and looktype 128 for sex 1 with colors head 114, body 120, legs 132 and feet 115.
derived:
  - A conservative greenfield starter profile can be selected by Oteryn as product policy without pretending schema defaults were already authoritative product intent.
  - Keeping starter items/storage/quests out of the first create operation minimizes shared-write surface and avoids relying on incidental login hooks.
unknown:
  - whether town_id 8 is the long-term desired product starting town name; this policy may select the durable numeric Canary-owned town identifier and require successor loadability integration validation
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Allow arbitrary Unicode names in the first slice: rejected because the current product does not have a proven normalization/confusable policy and database collation alone is insufficient.
  - Allow promoted vocations at creation: rejected because promotions are distinct game progression states, not base creation choices.
  - Add starter inventory/storage/quest writes by inference: rejected because no mandatory generic initialization contract is proven and the first slice should minimize shared writes.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260720-phase5-character-product-policy.md
validation:
  - command: task preflight
    result: PASS
    evidence: main 7d11f2cca4d10288a1d5011a3bb1e668e5a10d94, no open PRs, predecessor revalidation merged and archived
blockers:
  - none for product-policy decision
next_action: Record ADR 0005 with exact canonical naming, starter profile and per-account character-limit policy, then update the character creation contract and ACTIVE_WORK.
```

## Notes

This task intentionally converts previously explicit product-policy UNKNOWNs into explicit Oteryn decisions. It must not convert unknown Canary behavior into assumptions; the successor operation contract must still validate the exact write shape and loadability against current Canary.
