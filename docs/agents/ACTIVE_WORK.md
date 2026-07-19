# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase5-platform-account-provisioning-implementation` — PR #33 implements the approved Platform-originated Canary account provisioning + immutable `1 Platform Identity <-> 1 Canary accounts.id` binding slice. Delivery validation is green; final contract/checkpoint validation is in progress before merge.

## Proven implementation result

PR #33 now implements and validates:

- durable Platform pending/ready/conflict provisioning state with one row per Identity, unique Canary account ID and unique immutable provisioning name;
- pending provisioning intent created in the same Platform transaction as Identity registration, before any Canary write;
- dedicated `canary_provisioning` adapter restricted to `accounts(name,password,email,creation)` insert and `accounts(id,name,creation)` recovery reads;
- a non-user random sink credential whose plaintext is never persisted, exposed or used as the Platform Identity password;
- deterministic forward recovery by immutable provisioning name + creation epoch after Canary commit / Platform finalization failure;
- idempotent completed retries, hard conflict handling and database-enforced immutable 1:1 ownership;
- bounded requested/completed/failed/conflict security events without credential material;
- separate provisioning SQL template and effective-grant verifier;
- real MariaDB 11.8 CI coverage for column-level grants, trigger-owned VIP-group creation, password-read denial, duplicate-free retry and committed-account forward recovery.

The existing `canary` / `oteryn_readonly` connection remains unchanged.

Delivery-validation head `9d404bec37410ab1ef5c9954896f544b40963f54` passed CI #474 and Agent Governance #395.

## Recommended next task after PR #33

Once PR #33 is merged and housekeeping is complete, the original Identity-to-Canary ownership-binding blocker is resolved for greenfield accounts.

The next Phase 5 dependency should be a bounded revalidation/decision task for the remaining character-creation contract blockers in `CHARACTER_CREATION_CONTRACT.md`:

- authoritative character-name normalization and reserved-name policy;
- exact starter-state policy, including allowed vocation/sex/pronoun, starting town/position, level/stats/outfit/skills/items/storage/quest state;
- exact least-privilege character-create write connection/grants and operation initialization behavior.

Do not implement character creation until those remaining operation-level policies are explicitly resolved and tested.

## Cross-repository follow-up

No Canary repository change is required for account provisioning or immutable ownership binding itself.

A future separately authorized game-login integration task is required before Platform-originated users can authenticate to the game under the authoritative Platform credential model:

- `opentibiabr/login-server` should gain a Platform-authorized short-lived cryptographic assertion/session exchange bound to exact `accounts.id`, with explicit expiry/replay/session semantics and no sink-password dependency;
- if final requirements demand single-use DB sessions, stronger revocation, direct Platform assertion verification in Canary, or fencing/removal of alternate login paths, those changes belong to a separately authorized `blakinio/canary` task with rollout/backward-compatibility updates to `AUTH_GAME_LOGIN_CONTRACT.md`.

These future changes are durably recorded in `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`. No Canary/login-server repository was modified by PR #33.

## Other queued work

- Existing-account claim/import is out of scope for the greenfield product. Adding it later requires a new explicitly approved migration/claim contract.
- Admin/RBAC identity classification and permissions remain Phase 6. Exceptional privileged transfer/recovery of a binding is unavailable until a dedicated contract and those controls exist.

## Recently completed

- `OTERYN-20260720-phase5-platform-account-provisioning-contract` — approved the bounded Platform-originated Canary account provisioning + immutable 1:1 binding operation contract, merged through PR #31 as `dd60e29eee2ecf6f2053fcf09c4d7d6606c28c76`; no shared write was implemented. The task record is archived unchanged with blob `3c0541e5bfa7e9147915837649dfab3ae798e420` by post-merge housekeeping.
- `OTERYN-20260719-phase5-ownership-binding-dependency-gate` — selected the greenfield authoritative Platform account model and immutable `1 Platform Identity <-> 1 Canary accounts.id` ownership direction, merged through PR #29 as `bb007f5dbe30711b1c951b621506c2cca6834a07`; existing-account claim is out of scope.
- `OTERYN-20260719-phase5-identity-canary-account-binding` — bounded ownership-binding discovery merged through PR #27 as `c683e6b6e37851447aaa0701237750828d6ed23c`.
- `OTERYN-20260719-phase5-character-creation-contract` — initial character-create discovery merged through PR #26 as `ab78d6ac3bc674deb0868195563b61a753d95f98`; character implementation remains blocked by the independent naming/starter-state/write-policy requirements.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
