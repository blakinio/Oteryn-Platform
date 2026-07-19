# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase5-platform-account-provisioning-implementation` — implements the approved Platform-originated Canary account provisioning + immutable `1 Platform Identity <-> 1 Canary accounts.id` binding slice; active on PR #33. Scope is limited to the approved `accounts(name,password,email,creation)` write, Platform-owned provisioning/binding state, dedicated least-privilege `canary_provisioning` boundary, retry/recovery and tests. No character writes or game-login bridge changes are in scope.

## Current implementation state

PR #33 currently implements:

- durable Platform pending/ready/conflict provisioning state with unique Identity, Canary account ID and provisioning-name constraints;
- pending intent creation in the same Platform transaction as Identity registration, before any Canary write;
- a dedicated `canary_provisioning` adapter using only the approved account insert/recovery surface;
- a non-user random sink credential whose plaintext is never persisted/exposed;
- deterministic forward recovery by immutable provisioning name + creation epoch;
- registration-time best-effort provisioning with durable pending retry state on dependency failure;
- bounded requested/completed/failed/conflict security events without credential material;
- a separate provisioning SQL template and effective-grant verifier;
- real MariaDB CI coverage for column-level grants, trigger-owned VIP-group creation, password-read denial and forward recovery after a committed Canary account.

The existing `canary` / `oteryn_readonly` connection remains unchanged.

## Recommended next task after PR #33

Once PR #33 is merged and housekeeping is complete, ownership binding itself will no longer be the character-authorization blocker. The next Phase 5 dependency should revalidate and resolve the remaining character-creation product blockers already recorded in `CHARACTER_CREATION_CONTRACT.md`:

- authoritative character-name normalization/reserved-name policy;
- exact starter-state policy including allowed vocation/sex/pronoun, starting town/position, level/stats/outfit/skills/items/storage/quest state;
- exact least-privilege character-create write connection/grants and operation initialization behavior.

Do not implement character creation until those remaining operation-contract blockers are explicitly resolved.

## Cross-repository follow-up

No Canary repository change is currently required for the account-provisioning write itself.

A future separately authorized game-login integration task is required before Platform-originated users can authenticate to the game under the authoritative Platform credential model. The durable required direction is recorded in `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`:

- `opentibiabr/login-server` should gain a Platform-authorized assertion/session exchange bound to exact `accounts.id`, with explicit expiry/replay/session semantics and no sink-password dependency;
- if final requirements demand single-use DB sessions, stronger revocation, direct Platform assertion verification in Canary, or fencing/removal of alternate login paths, those changes belong to a separately authorized `blakinio/canary` task with rollout/backward-compatibility updates to `AUTH_GAME_LOGIN_CONTRACT.md`.

## Other queued work

- Existing-account claim/import is out of scope for the greenfield product. Adding it later requires a new explicitly approved migration/claim contract.
- Admin/RBAC identity classification and permissions remain Phase 6. Exceptional privileged transfer/recovery of a binding is not available until a dedicated contract and those controls exist.

## Recently completed

- `OTERYN-20260720-phase5-platform-account-provisioning-contract` — approved the bounded Platform-originated Canary account provisioning + immutable 1:1 binding operation contract, merged through PR #31 as `dd60e29eee2ecf6f2053fcf09c4d7d6606c28c76`; no shared write was implemented. The task record is archived unchanged with blob `3c0541e5bfa7e9147915837649dfab3ae798e420` by post-merge housekeeping.
- `OTERYN-20260719-phase5-ownership-binding-dependency-gate` — selected the greenfield authoritative Platform account model and immutable `1 Platform Identity <-> 1 Canary accounts.id` ownership direction, merged through PR #29 as `bb007f5dbe30711b1c951b621506c2cca6834a07`; existing-account claim is out of scope. The task record is archived unchanged with blob `4bb13c70b1ae1793c7ce93d2f0f5c07c11dc6e1b` by post-merge housekeeping.
- `OTERYN-20260719-phase5-identity-canary-account-binding` — bounded Phase 5 ownership-binding discovery, merged through PR #27 as `c683e6b6e37851447aaa0701237750828d6ed23c`; no binding implementation was approved because no current side-effect-free, credential-compatible existing-account claim capability is proven. The task record is archived unchanged with blob `59abf3c86fdca19aa0bd97e90711f13607132f53` by post-merge housekeeping.
- `OTERYN-20260719-phase5-character-creation-contract` — first Phase 5 bounded character-create operation discovery, merged through PR #26 as `ab78d6ac3bc674deb0868195563b61a753d95f98`; no shared write was approved because Identity→Canary account ownership binding and product starter/name policy were unresolved. The task record was archived unchanged by exact blob identity when the successor task started.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
