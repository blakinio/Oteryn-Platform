# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase5-character-create-implementation` — implements the approved authenticated character-create vertical slice with dedicated `canary_character_create` least-privilege transaction boundary and required real MariaDB privilege/concurrency tests. Character deletion/rename remain out of scope.

## Proven implementation result

Phase 5 greenfield ownership provisioning and immutable binding are implemented on `main` through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

ADR 0005 character creation product policy is merged through PR #37 as `c5b8719de51deec6cea6d9270e55416fba1d6472`.

The implementation-ready character-create operation contract is merged through PR #39 as `660f1790101842772b3bd5b18926b9dc9fc394a7`.

The existing `canary` read-only and `canary_provisioning` account-create connections remain unchanged.

## Active implementation scope

PR successor implementation will add:

- server-side ADR 0005 name canonicalization/reserved-name validation;
- authenticated `/account/characters/create` form and POST `/account/characters` boundary;
- ready immutable Canary binding resolution with no client account-ID control;
- dedicated `canary_character_create` connection and gateway;
- exact account lock -> same-name recovery -> quota count -> starter INSERT transaction;
- bounded transient deadlock/serialization retry;
- exact SQL grant template and fail-closed privilege verifier;
- unit/feature tests and real MariaDB coverage for exact grants, `FOR UPDATE`, idempotency, quota races, global name races and forbidden privileges.

`CHARACTER CREATION: BLOCKED` until this active implementation passes final exact-head validation and merges.

## Cross-repository state

Current Canary evidence pin is `800142e65c2975e57647bf34128ab468532218f0`.

No Canary code change is currently proven necessary. If real integration validation disproves a selected starter/loadability invariant, record the exact external change for a separately authorized task and fail closed.

The future authoritative game-login bridge remains separate work recorded in `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`.

## Other queued work

- Character deletion and rename require separate Phase 5 operation contracts after creation.
- Existing-account claim/import is out of scope for the greenfield product.
- Admin/RBAC identity classification and permissions remain Phase 6.

## Recently completed

- `OTERYN-20260720-phase5-character-create-operation-contract` — approved the exact implementation shape, merged through PR #39 as `660f1790101842772b3bd5b18926b9dc9fc394a7`.
- `OTERYN-20260720-phase5-character-product-policy` — selected ADR 0005 and merged through PR #37 as `c5b8719de51deec6cea6d9270e55416fba1d6472`.
- `OTERYN-20260720-phase5-platform-account-provisioning-implementation` — implemented immutable greenfield account provisioning/binding, merged through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
