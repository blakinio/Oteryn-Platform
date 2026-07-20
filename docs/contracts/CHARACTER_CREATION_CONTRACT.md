# Platform-driven Character Creation Contract

## Status

`PRODUCT POLICY SELECTED — OPERATION CONTRACT STILL REQUIRED`

This contract governs any future Oteryn Platform operation that creates a Canary `players` record for an authenticated user.

The original Phase 5 authorization blocker is resolved for supported greenfield accounts: an authenticated Platform Identity may target only the exact Canary `accounts.id` from its ready immutable Platform-owned binding.

ADR 0005 now defines the authoritative Oteryn product policy for character names, the starter profile and the per-account character limit. Character creation remains blocked only until a successor operation contract proves the exact Canary transaction, idempotency/recovery behavior and dedicated least-privilege write boundary.

## Evidence baseline

### Oteryn Platform

- Ownership model: `1 Platform Identity <-> 1 Canary accounts.id`.
- Greenfield account provisioning and immutable binding are implemented through PR #33 / `d5c319448737ee5badd8ab73967535a5ec9b67d1`.
- Authorization for character creation must resolve the target account exclusively from the authenticated Identity's ready `identity_canary_accounts.canary_account_id` binding.
- Client-supplied account IDs, email equality and account-name matching are not ownership proof.
- Product policy is defined by ADR `0005-character-creation-product-policy.md`.

### Canary

- Repository: `blakinio/canary`.
- Current policy-task evidence pin: `37b41a29c8743d4c976eb7fcb82d684594722aa4`.
- Access mode: read-only.
- Current `players` schema has auto-generated `id`, unique `name`, `account_id` foreign key, many gameplay defaults and `conditions MEDIUMBLOB NOT NULL` with no default.
- Current player load requires resolvable account, group and vocation; a stored `(0,0,0)` login position is replaced with the player's temple position.
- Current vocation configuration proves base vocation IDs `1`, `2`, `3`, `4` and `9`, with promoted forms at `5`, `6`, `7`, `8` and `10`.
- Current migration 55 demonstrates a load-shaped level-8 player row using experience `4200`, health `185`, mana `90`, capacity `470`, `town_id = 8` and an empty non-null conditions blob.
- Current `PlayerLogin` compatibility behavior uses looktype `136` for sex `0` and looktype `128` for sex `1`, with default colors head `114`, body `120`, legs `132`, feet `115`.

These Canary facts prove implementation compatibility surfaces. ADR 0005, not the raw schema or sample row, is the product source of truth for the selected starter state.

## Authorization — RESOLVED

A character-create request may proceed past authorization only when:

1. the caller is an authenticated Platform Identity;
2. the Platform-owned binding row is in ready state;
3. the exact target Canary `accounts.id` is the non-null `canary_account_id` stored in that binding;
4. the operation does not accept another account ID as authoritative client input.

The character-create service must derive the account server-side from this binding before opening the Canary shared-write transaction.

## Character-name policy — RESOLVED

ADR 0005 defines the canonical first-version policy.

### Allowed input and canonicalization

- only ASCII letters and ASCII space are accepted;
- leading/trailing spaces are trimmed;
- internal space runs collapse to one space;
- one to three words are allowed;
- every word contains 2 to 15 ASCII letters;
- canonical total length is 3 to 29 characters including spaces;
- each word is stored first-letter uppercase and remaining letters lowercase.

The canonical stored name is the only name submitted to Canary.

Punctuation, apostrophes, hyphens, digits, tabs, emoji and non-ASCII letters are rejected in the first slice.

### Reserved names

After canonicalization, any word exactly equal to one of these protected words is rejected:

`Admin`, `Administrator`, `God`, `Gamemaster`, `GM`, `CM`, `Support`, `Tutor`, `Moderator`, `Staff`, `System`, `Server`, `Oteryn`, `Canary`.

The exact phrases `Game Master` and `Community Manager` are reserved. The first word also must not begin with the brand prefixes `Oteryn` or `Canary`, case-insensitively.

The Platform enforces this policy server-side.

Database uniqueness on `players.name` remains the final insert-time race guard. Any availability preflight is UX only.

## Starter-state policy — RESOLVED

### Creation inputs

The first operation accepts only:

- canonicalizable `name`;
- base `vocation` in `{1,2,3,4,9}`;
- `sex` in `{0,1}`.

Promoted vocations `{5,6,7,8,10}` and vocation `0` are rejected by the Platform creation flow.

`pronoun` is not a first-version input and is persisted as `0`.

### Canonical starter profile v1

The resulting character must persist:

- `group_id = 1`;
- `account_id =` ready bound Canary account ID;
- `level = 8`;
- `experience = 4200`;
- selected base `vocation`;
- `health = 185`;
- `healthmax = 185`;
- `mana = 90`;
- `manamax = 90`;
- `maglevel = 0`;
- `manaspent = 0`;
- `soul = 100`;
- `cap = 470`;
- `town_id = 8`;
- `posx = 0`, `posy = 0`, `posz = 0`;
- empty non-null `conditions` blob;
- selected `sex`;
- `pronoun = 0`;
- `looktype = 136` for sex `0`;
- `looktype = 128` for sex `1`;
- `lookhead = 114`;
- `lookbody = 120`;
- `looklegs = 132`;
- `lookfeet = 115`;
- `lookaddons = 0`;
- `istutorial = 0`;
- classic starter skills at level `10` with zero tries.

The successor operation contract must prove the exact insert column set needed to produce this state deterministically. It may rely on a current schema default only when that exact default is revalidated and covered by integration tests.

### No dependent starter writes in v1

The first create operation initializes no starter inventory, inbox, store inbox, depot, reward, stash, player storage, quest, learned-spell, tutorial, guild, session or runtime rows.

Current global login hooks are not treated as mandatory starter initialization.

Therefore the expected first operation shape is one bounded `players` insert unless live operation-contract revalidation proves that a mandatory dependent write is required for loadability.

## Account character limit — RESOLVED

A supported Platform-owned Canary account may have at most **10 active characters**.

Quota semantics:

- `players.deletion = 0` counts toward the limit;
- `players.deletion != 0` does not count toward the active-character quota;
- a pending-deletion row still reserves its globally unique name until the row no longer exists.

The limit must be checked inside the same Canary transaction as creation.

## Required concurrency shape

For each create:

1. resolve the authenticated Identity's ready binding before the shared-write transaction;
2. begin the dedicated Canary character-create transaction;
3. lock the exact bound `accounts` row using `SELECT ... FOR UPDATE` or another operation-contract-proven equivalent;
4. count that account's `players` rows where `deletion = 0`;
5. return deterministic limit conflict when the count is already `10`;
6. validate canonical-name availability only as a preflight convenience;
7. attempt the insert while retaining `players.name` uniqueness as the final global name race guard;
8. commit only when the entire approved starter state is durable.

This serialization prevents two simultaneous creates for one account from both consuming the final slot.

Different accounts need not share one account lock. Global same-name races are resolved by the database unique constraint.

## Transaction, retry and idempotency requirements still to finalize

The successor operation contract must define exactly:

- the final insert column list;
- whether current defaults for skills and other untouched fields are safe to rely on;
- exact `town_id = 8` loadability for every allowed vocation/sex combination;
- exact account-row locking query and required SELECT privilege;
- exact active-character count query;
- duplicate-name error mapping;
- behavior when the same authenticated account retries the same canonical name;
- recovery after an ambiguous database commit/connection result;
- whether a Platform-owned server-generated idempotency key is required;
- deadlock/serialization retry policy;
- exact dedicated database grants.

No existing character may ever be reassigned to another account as retry recovery.

## Dedicated least-privilege write boundary

The existing connections remain separate and unchanged:

- `canary` / `oteryn_readonly` — read-only PublicGameData;
- `canary_provisioning` — account provisioning only.

Character creation requires a new dedicated operation boundary, conceptually `canary_character_create`.

The operation contract must derive its exact grants from the final transaction. Expected minimum categories are:

- narrow `SELECT` needed to lock/verify the exact account;
- narrow `SELECT` needed to count active players and inspect exact-name recovery/conflict state;
- column-level `INSERT` on `players` for only the approved starter fields.

No `UPDATE`, `DELETE`, account credential access, session writes, guild writes or unrelated player-table writes are approved by this policy contract.

The deployment privilege verifier must fail closed on missing or excessive grants, and real MariaDB integration coverage is required.

## Online/session effects

Current game-world authentication validates that the selected character belongs to the authenticated account and is not deleted/unavailable.

No contract claims that an already-issued character-list response dynamically refreshes after web character creation. A new character becomes available on a subsequent authoritative character-list/login request unless a later integration proves stronger behavior.

Character creation does not create `cluster_sessions` or `account_sessions`.

## Cross-repository change history

No Canary repository change is currently proven necessary for the selected starter policy because ADR 0005 intentionally avoids mandatory game-side starter hooks and dependent gameplay writes.

If operation-contract integration tests prove that `town_id = 8`, empty dependent state or another selected starter invariant cannot load safely, the task must fail closed and record the exact required Canary/datapack change for separate authorization rather than silently changing external code.

The Platform must not depend on an uncontracted incidental `onLogin` side effect.

## Decision

`CHARACTER PRODUCT POLICY IS APPROVED.`

Resolved:

- authenticated Identity -> exact Canary account authorization;
- canonical name and reserved-name policy;
- allowed base vocation/sex creation inputs;
- starter profile v1;
- no starter dependent writes in v1;
- maximum 10 active characters per account;
- account-row serialization requirement for concurrent limit enforcement.

Remaining before implementation:

1. exact character-create transaction and insert column contract;
2. exact idempotency/ambiguous-commit recovery semantics;
3. exact `canary_character_create` least-privilege grants and verifier;
4. real MariaDB integration tests proving starter-row load shape, account-limit locking and unique-name races.

The nearest minimal dependency is a bounded character-create operation-contract task.

`CHARACTER CREATION: BLOCKED`
