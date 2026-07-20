# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

None.

## Proven implementation result

Phase 5 greenfield ownership provisioning and immutable binding are implemented on `main` through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

ADR 0005 character creation product policy is merged through PR #37 as `c5b8719de51deec6cea6d9270e55416fba1d6472`.

The implementation-ready character-create operation contract is merged through PR #39 as `660f1790101842772b3bd5b18926b9dc9fc394a7`.

The existing `canary` read-only and `canary_provisioning` account-create connections remain unchanged.

## Approved character-create implementation shape

- authorize only from the authenticated Identity's ready immutable Canary account binding;
- validate/canonicalize `name`, base `vocation` and `sex` server-side;
- use a third dedicated `canary_character_create` transaction boundary;
- lock the exact bound `accounts.id` row before same-account recovery/quota/insert;
- use `(authorized accounts.id, canonical players.name)` as the v1 natural idempotent create target;
- recover an existing same-account active canonical name read-only;
- treat other-account same name and same-account deleted same name as `name_conflict`;
- enforce maximum 10 `deletion = 0` characters while the account row is locked;
- insert exactly the approved 42 starter columns and no dependent rows;
- retry only transient deadlock/serialization failures, at most 3 total transaction attempts;
- retain database uniqueness on `players.name` as the final global race guard;
- recover ambiguous commit by normal account/name retry, never by UPDATE or destructive compensation.

Approved database privilege target:

- column-level `SELECT` on `accounts(id)`;
- column-level `SELECT` on `players(id,name,account_id,deletion)`;
- column-level `INSERT` only on the exact starter columns defined in `CHARACTER_CREATION_CONTRACT.md`;
- no table-level SELECT, no UPDATE/DELETE, no credential/session/unrelated-table access, DDL or GRANT OPTION.

`CHARACTER CREATION: BLOCKED` only until the successor implementation task passes the required code, real MariaDB privilege, locking, quota-race and name-race tests.

## Recommended next dependency

Implement the approved character-create vertical slice in Oteryn Platform with:

1. server-side character-name canonicalizer and reserved-name validator;
2. authenticated create-character HTTP/service boundary deriving the ready Canary account binding server-side;
3. dedicated `canary_character_create` connection/gateway;
4. exact locked transaction, natural idempotency and deterministic result mapping;
5. reviewed SQL provisioning template and fail-closed effective-grant verifier;
6. feature/unit tests plus real MariaDB integration coverage for exact grants, `FOR UPDATE`, quota races, unique-name races, ambiguous recovery and starter/default row shape.

No Canary code change is currently proven necessary.

## Cross-repository state

Current Canary evidence pin is `800142e65c2975e57647bf34128ab468532218f0`.

If implementation integration tests disprove a selected starter/loadability assumption, record the exact Canary/datapack change for a separately authorized task rather than applying it implicitly.

The future authoritative game-login bridge remains separate work recorded in `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`.

## Other queued work

- Existing-account claim/import is out of scope for the greenfield product.
- Admin/RBAC identity classification and permissions remain Phase 6.

## Recently completed

- `OTERYN-20260720-phase5-character-create-operation-contract` — approved the exact character-create transaction/idempotency/least-privilege implementation shape, merged through PR #39 as `660f1790101842772b3bd5b18926b9dc9fc394a7`; final exact head passed CI #502 and Agent Governance #423. The task record is archived unchanged by post-merge housekeeping.
- `OTERYN-20260720-phase5-character-product-policy` — selected ADR 0005 and merged through PR #37 as `c5b8719de51deec6cea6d9270e55416fba1d6472`.
- `OTERYN-20260720-phase5-character-creation-policy-revalidation` — revalidated the character-create gate after ownership binding, merged through PR #35 as `35ca943d6051ff27f215b813d51a5ae557cbac40`.
- `OTERYN-20260720-phase5-platform-account-provisioning-implementation` — implemented immutable greenfield account provisioning/binding, merged through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
