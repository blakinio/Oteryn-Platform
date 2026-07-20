# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase5-character-product-policy` — selects the missing durable Oteryn product policy for canonical character names/reserved names, one exact greenfield starter profile and per-account character-limit concurrency semantics. Scope is policy/ADR/contract only; no character shared write is implemented in this task.

## Proven implementation result

Phase 5 greenfield ownership provisioning and immutable binding are implemented on `main` through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

The original missing Identity-to-exact-Canary-account authorization blocker is resolved for supported greenfield accounts. A future character-create operation must derive the exact Canary `accounts.id` only from the authenticated Identity's ready Platform-owned binding.

The existing `canary` / `oteryn_readonly` connection remains unchanged.

## Current character-create gate

Character-create policy revalidation merged through PR #35 as `35ca943d6051ff27f215b813d51a5ae557cbac40` against Canary `37b41a29c8743d4c976eb7fcb82d684594722aa4`.

Ownership is no longer the blocker. The active task is now converting the remaining explicit product-policy UNKNOWNs into durable Oteryn decisions without inferring them from SQL defaults or incidental login hooks.

`CHARACTER CREATION: BLOCKED` until the product-policy ADR is merged and a successor operation contract proves the exact transaction/write/grant surface.

## Planned product-policy decisions

The active task will define:

1. deterministic ASCII canonical character-name rules plus reserved-role/system names;
2. one exact starter profile using only current Canary-compatible values, base vocation choices and no inferred starter inventory/storage/quest writes;
3. a hard per-account active-character limit plus same-account concurrency serialization semantics.

After those decisions merge, the next task is a bounded character-create operation contract defining the exact Canary transaction, idempotency/recovery semantics and dedicated least-privilege `canary_character_create` credential.

## Cross-repository follow-up

No Canary repository change is currently proven necessary merely to authorize or persist a character row.

If the selected starter policy requires mandatory game-side initialization that cannot be represented safely as one bounded transactional database write, record and authorize a separate `blakinio/canary`/datapack task before implementation. That task must define the exact initialization owner/hook, idempotent invocation, retry/transaction semantics, rollout order and loadability integration tests.

Separately, the future authoritative game-login integration remains recorded in `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`:

- `opentibiabr/login-server` should gain a Platform-authorized short-lived cryptographic assertion/session exchange bound to exact `accounts.id`;
- potential `blakinio/canary` changes are required only if the final login design needs stronger single-use/revocation/direct assertion/fencing behavior.

## Other queued work

- Existing-account claim/import is out of scope for the greenfield product.
- Admin/RBAC identity classification and permissions remain Phase 6.

## Recently completed

- `OTERYN-20260720-phase5-character-creation-policy-revalidation` — revalidated character creation after ownership binding implementation, merged through PR #35 as `35ca943d6051ff27f215b813d51a5ae557cbac40`; ownership authorization is resolved and the remaining blockers are explicit product naming/starter/character-limit decisions plus the dependent write/grant surface derived from them. The task record is archived unchanged by post-merge housekeeping.
- `OTERYN-20260720-phase5-platform-account-provisioning-implementation` — implemented and validated Platform-originated Canary account provisioning plus immutable 1:1 ownership binding, merged through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`; final ready-head CI #477 and Agent Governance #398 passed. The task record is archived unchanged with blob `2217873d3a777bdb3285861822578898fae74930` by post-merge housekeeping.
- `OTERYN-20260720-phase5-platform-account-provisioning-contract` — approved the bounded provisioning/binding operation contract, merged through PR #31 as `dd60e29eee2ecf6f2053fcf09c4d7d6606c28c76`.
- `OTERYN-20260719-phase5-ownership-binding-dependency-gate` — selected the greenfield authoritative Platform account model and immutable `1 Platform Identity <-> 1 Canary accounts.id` ownership direction, merged through PR #29 as `bb007f5dbe30711b1c951b621506c2cca6834a07`.
- `OTERYN-20260719-phase5-character-creation-contract` — initial character-create discovery merged through PR #26 as `ab78d6ac3bc674deb0868195563b61a753d95f98`; ownership is now resolved, while product naming/starter/limit policy is being selected by the active task.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
