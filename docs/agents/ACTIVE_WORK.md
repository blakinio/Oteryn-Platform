# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase5-character-create-operation-contract` — PR #39 defines the exact implementation-ready Canary transaction, natural idempotency and dedicated `canary_character_create` least-privilege boundary for ADR 0005. No character shared write is implemented in this contract task.

## Proven implementation result

Phase 5 greenfield ownership provisioning and immutable binding are implemented on `main` through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

ADR 0005 character creation product policy is merged through PR #37 as `c5b8719de51deec6cea6d9270e55416fba1d6472`.

The existing `canary` read-only and `canary_provisioning` account-create connections remain unchanged.

## Character-create contract result on PR #39

The contract now approves this implementation shape:

- authorize only from the authenticated Identity's ready immutable Canary account binding;
- validate/canonicalize `name`, base `vocation` and `sex` server-side;
- use one dedicated `canary_character_create` transaction;
- `SELECT id FROM accounts WHERE id = ? FOR UPDATE` to serialize same-account creates;
- exact-name recovery before quota evaluation;
- v1 natural idempotency target `(authorized accounts.id, canonical players.name)`;
- same-account active same-name request returns the existing player ID without mutation;
- different-account same name or same-account deleted same name returns `name_conflict`;
- count `deletion = 0` characters under the account lock and enforce maximum 10;
- insert exactly the approved 42 starter columns and no dependent rows;
- retry only transient deadlock/serialization failures, with at most 3 total transaction attempts;
- use database uniqueness on `players.name` as the final global race guard;
- recover ambiguous commit by rerunning the same account/name operation, never by UPDATE or destructive compensation.

Approved dedicated DB surface:

- `SELECT` only on `accounts(id)`;
- `SELECT` only on `players(id,name,account_id,deletion)`;
- column-level `INSERT` only on the exact starter columns defined in `CHARACTER_CREATION_CONTRACT.md`;
- no table-level SELECT, no `UPDATE`/`DELETE`, no account credentials, sessions, inventory/storage/guild writes, DDL or `GRANT OPTION`.

Real MariaDB integration tests are mandatory before implementation merge to prove the column grants support `FOR UPDATE`/COUNT, quota races, global name races, idempotent recovery, exact starter/default row shape and forbidden privilege denial.

`CHARACTER CREATION: BLOCKED` until PR #39 is merged and the successor implementation task passes those tests.

## Recommended next dependency after PR #39

Implement the approved character-create vertical slice in Oteryn Platform with:

1. server-side name canonicalizer/reserved-name validator;
2. authenticated character-create service/HTTP boundary deriving the ready account binding server-side;
3. dedicated `canary_character_create` connection and gateway;
4. exact locked transaction and deterministic operation result mapping;
5. reviewed SQL provisioning template and fail-closed effective-grant verifier;
6. unit/feature tests plus real MariaDB locking/privilege/race integration coverage.

No Canary code change is currently proven necessary.

## Cross-repository state

Current Canary evidence pin for this task is `800142e65c2975e57647bf34128ab468532218f0`. The only commit after the ADR 0005 evidence pin changed OAM documentation, not player schema/load behavior.

If implementation integration tests disprove the selected starter/loadability assumptions, record the exact Canary/datapack change for a separately authorized task rather than applying it implicitly.

The future authoritative game-login bridge remains separate work recorded in `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`.

## Other queued work

- Existing-account claim/import is out of scope for the greenfield product.
- Admin/RBAC identity classification and permissions remain Phase 6.

## Recently completed

- `OTERYN-20260720-phase5-character-product-policy` — selected ADR 0005 and merged through PR #37 as `c5b8719de51deec6cea6d9270e55416fba1d6472`.
- `OTERYN-20260720-phase5-character-creation-policy-revalidation` — revalidated the character-create gate after ownership binding, merged through PR #35 as `35ca943d6051ff27f215b813d51a5ae557cbac40`.
- `OTERYN-20260720-phase5-platform-account-provisioning-implementation` — implemented immutable greenfield account provisioning/binding, merged through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
