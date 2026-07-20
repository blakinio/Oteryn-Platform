# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

None.

## Proven implementation result

Phase 5 greenfield ownership provisioning and immutable binding are implemented on `main` through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

ADR 0005 character creation product policy is merged through PR #37 as `c5b8719de51deec6cea6d9270e55416fba1d6472`.

The current product policy defines:

- deterministic ASCII canonical names and reserved-name rules;
- base creation vocations `1`, `2`, `3`, `4`, `9` and sex `0`/`1`;
- starter profile v1 at level 8 with the selected fixed stats/look/town/position policy;
- no starter dependent inventory/storage/quest/session writes;
- maximum 10 active (`deletion = 0`) characters per account;
- same-account creation serialization through locking the exact Canary account row before counting active characters.

The existing `canary` read-only and `canary_provisioning` account-create connections remain unchanged.

`CHARACTER CREATION: BLOCKED` only until the exact operation contract and implementation-specific write boundary are approved and tested.

## Recommended next dependency

Create a bounded character-create operation-contract task that proves against current Canary:

1. the exact `players` insert column set needed for starter profile v1;
2. whether any selected starter values may safely rely on current schema defaults;
3. loadability of `town_id = 8` plus `(0,0,0)` for allowed base vocation/sex combinations;
4. exact account-row lock, active-character count and transaction ordering;
5. duplicate-name and same-account retry/idempotency/ambiguous-commit behavior;
6. exact dedicated `canary_character_create` SELECT/INSERT grants and fail-closed privilege verifier requirements;
7. required real MariaDB integration tests for limit races, unique-name races, privilege denial and starter-row shape.

If that operation contract is fully proven without a Canary code change, the next successor task may implement character creation in Oteryn Platform using the dedicated least-privilege connection.

## Cross-repository follow-up

No Canary repository change is currently proven necessary for the selected starter policy. If operation-contract loadability/integration validation disproves a selected invariant, record the exact required `blakinio/canary` or datapack change for separate authorization rather than changing external code implicitly.

Separately, the future authoritative game-login integration remains recorded in `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`:

- `opentibiabr/login-server` should gain a Platform-authorized short-lived cryptographic assertion/session exchange bound to exact `accounts.id`;
- potential `blakinio/canary` changes are required only if the final login design needs stronger single-use/revocation/direct assertion/fencing behavior.

## Other queued work

- Existing-account claim/import is out of scope for the greenfield product.
- Admin/RBAC identity classification and permissions remain Phase 6.

## Recently completed

- `OTERYN-20260720-phase5-character-product-policy` — selected and documented the durable character naming, starter profile and per-account limit policy through ADR 0005, merged through PR #37 as `c5b8719de51deec6cea6d9270e55416fba1d6472`; exact final head passed CI #494 and Agent Governance #415. The task record is archived unchanged by post-merge housekeeping.
- `OTERYN-20260720-phase5-character-creation-policy-revalidation` — revalidated character creation after ownership binding implementation, merged through PR #35 as `35ca943d6051ff27f215b813d51a5ae557cbac40`.
- `OTERYN-20260720-phase5-platform-account-provisioning-implementation` — implemented and validated Platform-originated Canary account provisioning plus immutable 1:1 ownership binding, merged through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`.
- `OTERYN-20260720-phase5-platform-account-provisioning-contract` — approved the bounded provisioning/binding operation contract, merged through PR #31 as `dd60e29eee2ecf6f2053fcf09c4d7d6606c28c76`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
