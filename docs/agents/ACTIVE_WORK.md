# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

None.

## Proven implementation result

Phase 5 greenfield ownership provisioning and immutable binding are implemented on `main` through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

The original missing Identity-to-exact-Canary-account authorization blocker is resolved for supported greenfield accounts. A future character-create operation must derive the exact Canary `accounts.id` only from the authenticated Identity's ready Platform-owned binding.

The existing `canary` / `oteryn_readonly` connection remains unchanged.

## Current character-create gate

Character-create policy revalidation merged through PR #35 as `35ca943d6051ff27f215b813d51a5ae557cbac40` against Canary `37b41a29c8743d4c976eb7fcb82d684594722aa4`.

Ownership is no longer the blocker. The remaining gate is an explicit Oteryn product decision for:

- character-name normalization, allowed characters/capitalization and reserved names;
- starter-state inputs and persisted state, including vocation, sex/pronoun, town/position, stats, outfit, skills, items and storage/quest/tutorial initialization;
- maximum characters per account and concurrent-limit semantics.

Current Canary schema/load behavior and global login hooks are compatibility evidence only. They do not define a generic product starter kit or authoritative web character-name policy.

Until those product decisions exist, the exact dependent initialization write set and least-privilege character-create grants cannot be finalized.

`CHARACTER CREATION: BLOCKED`

## Recommended next dependency

Create an explicit Oteryn product-policy decision task/ADR for:

1. canonical character-name policy and reserved names;
2. canonical starter-state policy and creation-time choices;
3. account character limit and concurrent-limit semantics.

Do not infer those decisions from SQL defaults, sample characters or incidental login hooks.

Only after that product policy is explicit should a successor operation-contract task finalize the exact Canary transaction, mandatory dependent writes, idempotency/locking behavior and dedicated least-privilege character-create credential.

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
- `OTERYN-20260719-phase5-character-creation-contract` — initial character-create discovery merged through PR #26 as `ab78d6ac3bc674deb0868195563b61a753d95f98`; ownership is now resolved, while product naming/starter/limit policy remains the current gate.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
