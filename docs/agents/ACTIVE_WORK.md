# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase5-character-create-implementation` — PR #41. The authenticated greenfield character-create slice is implemented and validated; final documentation-head CI/Governance revalidation and merge remain. Character deletion/rename remain out of scope.

## Proven Phase 5 implementation state

Greenfield ownership provisioning and immutable `1 Platform Identity <-> 1 Canary accounts.id` binding are implemented on `main` through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

ADR 0005 character creation product policy is merged through PR #37 as `c5b8719de51deec6cea6d9270e55416fba1d6472`.

The character-create operation contract is merged through PR #39 as `660f1790101842772b3bd5b18926b9dc9fc394a7`.

PR #41 implements and has validated:

- server-side ADR 0005 canonical-name and reserved-name validation;
- authenticated `/account/characters/create` and `POST /account/characters` boundaries;
- authorization derived exclusively from the authenticated Identity's ready immutable Canary account binding;
- no client control over `account_id` or starter-state fields;
- dedicated `canary_character_create` least-privilege connection;
- account `FOR UPDATE` -> exact-name recovery -> active quota -> exact 42-column starter INSERT transaction;
- natural `(account_id, canonical name)` idempotent recovery without player UPDATE or ownership reassignment;
- maximum 10 active characters under same-account serialization;
- bounded deadlock/serialization retry;
- exact SQL grant template and fail-closed effective-grant verifier;
- unit/feature tests and real MariaDB tests for privileges, starter/default row shape, forbidden access, idempotency, same-account quota race and global same-name race.

The existing `canary` read-only and `canary_provisioning` account-create connections remain unchanged.

Clean implementation head `27520854e326ada46c19ba1bfcda05fe89de2cab` passed CI #563 and Agent Governance #484.

`CHARACTER CREATION: IMPLEMENTED ON PR #41 / MERGE PENDING`

Production enablement requires out-of-band provisioning of the dedicated DB principal and a passing `php artisan canary:verify-character-create-db-privileges` check before enabling the write path.

## Cross-repository state

Current Canary evidence pin for character creation is `800142e65c2975e57647bf34128ab468532218f0`.

No Canary or login-server change was required for the bounded character-create operation. Those repositories remained read-only.

The future authoritative Platform game-login bridge remains separate work recorded in `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md` and must be performed as a separately authorized cross-repository task if/when selected.

## Phase 5 closure candidate

After PR #41 merges, run one bounded Phase 5 closure task against live `main` to verify the roadmap exit gate:

- every implemented shared write has an explicit contract;
- authorization and concurrency invariants are tested;
- no undocumented raw Canary write exists.

Character deletion and rename are optional future lifecycle capabilities and remain forbidden until separately contracted. Existing-account claim/import remains out of scope for the greenfield product.

## Recently completed

- `OTERYN-20260720-phase5-character-create-operation-contract` — PR #39 / `660f1790101842772b3bd5b18926b9dc9fc394a7`.
- `OTERYN-20260720-phase5-character-product-policy` — PR #37 / `c5b8719de51deec6cea6d9270e55416fba1d6472`.
- `OTERYN-20260720-phase5-platform-account-provisioning-implementation` — PR #33 / `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
