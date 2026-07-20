# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase5-character-product-policy` — PR #37 selects the durable greenfield character creation product policy. Scope is ADR/contract only; no character shared write is implemented in this task.

## Proven implementation result

Phase 5 greenfield ownership provisioning and immutable binding are implemented on `main` through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

The original missing Identity-to-exact-Canary-account authorization blocker is resolved for supported greenfield accounts. A future character-create operation must derive the exact Canary `accounts.id` only from the authenticated Identity's ready Platform-owned binding.

The existing `canary` / `oteryn_readonly` connection remains unchanged.

## Character product policy selected on PR #37

ADR 0005 and the updated character contract select:

- deterministic ASCII-only canonical names: 1-3 words, each 2-15 letters, canonical total length 3-29, title-cased after trimming/collapsing spaces;
- explicit reserved role/system words plus protected Oteryn/Canary brand prefixes;
- creation-time base vocations only: Sorcerer `1`, Druid `2`, Paladin `3`, Knight `4`, Monk `9`;
- creation-time sex `0` or `1`; pronoun is not a first-version input and persists as `0`;
- starter profile v1: level 8, experience 4200, health 185, mana 90, capacity 470, soul 100, town `8`, `(0,0,0)` temple fallback, sex-compatible default look, skills 10/0 and no tutorial state;
- no starter items, storage, quests, spells, depot/inbox/reward/guild/session/runtime dependent writes in v1;
- maximum 10 active (`deletion = 0`) characters per account;
- same-account create transactions must serialize on the exact Canary account row before counting active characters.

`CHARACTER CREATION: BLOCKED` until the successor operation contract proves the exact insert fields, idempotency/ambiguous-commit recovery, account lock/count queries, dedicated least-privilege grants and real MariaDB behavior.

## Recommended next dependency after PR #37

Create a bounded character-create operation-contract task that proves against current Canary:

1. the exact `players` insert column set needed for starter profile v1;
2. loadability of `town_id = 8` plus `(0,0,0)` for every allowed base vocation/sex combination;
3. exact account-row lock and active-character count transaction;
4. duplicate-name and same-account retry/idempotency behavior;
5. exact `canary_character_create` SELECT/INSERT grants and fail-closed privilege verifier;
6. required real MariaDB integration tests for limits, name races, privilege denial and starter-row shape.

Only after that contract is approved should the shared write be implemented.

## Cross-repository follow-up

No Canary repository change is currently proven necessary for the selected starter policy. If operation-contract loadability/integration validation disproves a selected invariant, record the exact required `blakinio/canary` or datapack change for separate authorization rather than changing external code implicitly.

Separately, the future authoritative game-login integration remains recorded in `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`:

- `opentibiabr/login-server` should gain a Platform-authorized short-lived cryptographic assertion/session exchange bound to exact `accounts.id`;
- potential `blakinio/canary` changes are required only if the final login design needs stronger single-use/revocation/direct assertion/fencing behavior.

## Other queued work

- Existing-account claim/import is out of scope for the greenfield product.
- Admin/RBAC identity classification and permissions remain Phase 6.

## Recently completed

- `OTERYN-20260720-phase5-character-creation-policy-revalidation` — revalidated character creation after ownership binding implementation, merged through PR #35 as `35ca943d6051ff27f215b813d51a5ae557cbac40`; ownership authorization is resolved.
- `OTERYN-20260720-phase5-platform-account-provisioning-implementation` — implemented and validated Platform-originated Canary account provisioning plus immutable 1:1 ownership binding, merged through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`; final ready-head CI #477 and Agent Governance #398 passed.
- `OTERYN-20260720-phase5-platform-account-provisioning-contract` — approved the bounded provisioning/binding operation contract, merged through PR #31 as `dd60e29eee2ecf6f2053fcf09c4d7d6606c28c76`.
- `OTERYN-20260719-phase5-ownership-binding-dependency-gate` — selected the greenfield authoritative Platform account model and immutable `1 Platform Identity <-> 1 Canary accounts.id` ownership direction, merged through PR #29 as `bb007f5dbe30711b1c951b621506c2cca6834a07`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
