# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

None.

## Recommended next task

Implement the approved **Platform-originated Canary account provisioning + immutable 1:1 binding** vertical slice exactly within `docs/contracts/PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`.

The approved implementation shape is:

- create durable Platform provisioning intent before any Canary write;
- generate an internal immutable `accounts.name` as `op` + 30 random lowercase hexadecimal characters;
- fill required `accounts.password` with a generated non-user sink credential hash whose plaintext is never persisted or exposed;
- insert only `accounts(name, password, email, creation)` through a new dedicated least-privilege `canary_provisioning` connection;
- leave the existing `canary` / `oteryn_readonly` connection unchanged;
- recover partial success forward by the persisted provisioning name + creation epoch rather than deleting a committed Canary account;
- persist the exact returned/recovered `accounts.id` as the immutable Platform-owned binding;
- treat an Identity as game-account-ready only after the binding is durable.

Character creation and every other user-scoped Canary mutation remain blocked until account provisioning plus binding are implemented and tested.

## Cross-repository follow-up

No Canary repository change is currently required for the account-provisioning write itself.

A future separately authorized game-login integration task is required before Platform-originated users can authenticate to the game under the authoritative Platform credential model. The durable required direction is recorded in `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`:

- `opentibiabr/login-server` should gain a Platform-authorized assertion/session exchange bound to exact `accounts.id`, with explicit expiry/replay/session semantics and no sink-password dependency;
- if final requirements demand single-use DB sessions, stronger revocation, direct Platform assertion verification in Canary, or fencing/removal of alternate login paths, those changes belong to a separately authorized `blakinio/canary` task with rollout/backward-compatibility updates to `AUTH_GAME_LOGIN_CONTRACT.md`.

## Other queued work

- Character-create implementation remains independently blocked by product character-name normalization/reserved-name policy and exact starter-state rules even after ownership binding is implemented; its future write path must use a separate least-privilege Canary write credential/connection.
- Existing-account claim/import is out of scope for the greenfield product. Adding it later requires a new explicitly approved migration/claim contract.
- Admin/RBAC identity classification and permissions remain Phase 6. Exceptional privileged transfer/recovery of a binding is not available until a dedicated contract and those controls exist.

## Recently completed

- `OTERYN-20260720-phase5-platform-account-provisioning-contract` — approved the bounded Platform-originated Canary account provisioning + immutable 1:1 binding operation contract, merged through PR #31 as `dd60e29eee2ecf6f2053fcf09c4d7d6606c28c76`; no shared write was implemented. The task record is archived unchanged with blob `3c0541e5bfa7e9147915837649dfab3ae798e420` by post-merge housekeeping.
- `OTERYN-20260719-phase5-ownership-binding-dependency-gate` — selected the greenfield authoritative Platform account model and immutable `1 Platform Identity <-> 1 Canary accounts.id` ownership direction, merged through PR #29 as `bb007f5dbe30711b1c951b621506c2cca6834a07`; existing-account claim is out of scope and Platform-originated Canary account creation is the next bounded dependency. The task record is archived unchanged with blob `4bb13c70b1ae1793c7ce93d2f0f5c07c11dc6e1b` by post-merge housekeeping.
- `OTERYN-20260719-phase5-identity-canary-account-binding` — bounded Phase 5 ownership-binding discovery, merged through PR #27 as `c683e6b6e37851447aaa0701237750828d6ed23c`; no binding implementation was approved because no current side-effect-free, credential-compatible existing-account claim capability is proven. The task record is archived unchanged with blob `59abf3c86fdca19aa0bd97e90711f13607132f53` by post-merge housekeeping.
- `OTERYN-20260719-phase5-character-creation-contract` — first Phase 5 bounded character-create operation discovery, merged through PR #26 as `ab78d6ac3bc674deb0868195563b61a753d95f98`; no shared write was approved because Identity→Canary account ownership binding and product starter/name policy remain unresolved. The task record was archived unchanged by exact blob identity when the successor task started.
- `OTERYN-20260719-phase4-public-read-closure` — Phase 4 public website/read-only game-data closure, including the bounded `/online` pagination fix and regression coverage, squash-merged through PR #23 as `3c52420d35f995338818b6c2c013fa518dc2c0ca`; task record archived unchanged with blob `658b31db4627da388f08054a21dcdca8def63c88` after merge.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
