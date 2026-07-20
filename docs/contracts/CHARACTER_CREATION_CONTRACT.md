# Platform-driven Character Creation Contract

## Status

`IMPLEMENTED AND VALIDATED ON PR #41 — MERGE PENDING`

This contract defines the only approved Phase 5 operation for creating a greenfield Canary `players` row for an authenticated Oteryn Platform Identity under ADR 0005.

PR #41 implements this operation within the authorization, validation, transaction, idempotency, concurrency and least-privilege boundary below. Clean implementation head `27520854e326ada46c19ba1bfcda05fe89de2cab` passed CI #563 and Agent Governance #484. Final documentation-head validation and merge remain before the implementation is considered present on `main`.

`CHARACTER CREATION: IMPLEMENTED / MERGE PENDING`

## Evidence baseline

### Oteryn Platform

- Ownership model: `1 Platform Identity <-> 1 Canary accounts.id`.
- Greenfield account provisioning and immutable binding are implemented through PR #33 / `d5c319448737ee5badd8ab73967535a5ec9b67d1`.
- ADR 0005 character creation product policy is merged through PR #37 / `c5b8719de51deec6cea6d9270e55416fba1d6472`.
- The implementation-ready operation contract was approved through PR #39 / `660f1790101842772b3bd5b18926b9dc9fc394a7`.
- Authorization resolves the target Canary account exclusively from the authenticated Identity's ready `identity_canary_accounts.canary_account_id` binding.
- Existing `canary` read-only and `canary_provisioning` account-create boundaries remain unchanged.

### Canary

- Repository: `blakinio/canary`.
- Inspected evidence pin: `800142e65c2975e57647bf34128ab468532218f0`.
- Access mode during this task: read-only.
- `players.id` is auto-generated.
- `players.name` is globally unique.
- `players.account_id` references `accounts.id`.
- `players.conditions` is the inspected starter-relevant required field without a schema default.
- Classic skills default to level `10` with zero tries.
- Player load requires a resolvable account, group and vocation.
- Persisted `(0,0,0)` login position is replaced by the player's temple position on the current load path.
- An empty `conditions` stream is accepted by the current load path.

No Canary or login-server repository change is required by this bounded database character-create operation.

## Authorization — REQUIRED AND IMPLEMENTED

Before the character-create Canary transaction is opened:

1. the caller is an authenticated Platform Identity;
2. the Platform-owned Identity-to-Canary binding exists and is `ready`;
3. the binding contains one non-null `canary_account_id`;
4. that exact server-resolved ID becomes the operation `account_id`;
5. no browser/client-supplied account ID, account name or email may establish or override authorization.

If the binding is absent, pending or conflict, the implementation fails closed before invoking the character-write gateway.

## User-controlled inputs

The only user-controlled operation inputs are:

- `name`;
- `vocation`;
- `sex`.

The server applies ADR 0005 canonicalization and reserved-name rules before opening the shared-write transaction. Only the canonical name is passed to Canary.

Allowed vocation IDs are exactly:

`1`, `2`, `3`, `4`, `9`.

Allowed sex values are exactly:

`0`, `1`.

All other creation-time fields are server-selected constants. Client-supplied `account_id`, level, town, position, conditions or other starter-state fields are ignored/rejected as authorization or state inputs.

Validation failure performs no Canary write.

## Exact starter row

The dedicated character-create principal may insert only these `players` columns:

- `name`;
- `group_id`;
- `account_id`;
- `level`;
- `vocation`;
- `health`;
- `healthmax`;
- `experience`;
- `lookbody`;
- `lookfeet`;
- `lookhead`;
- `looklegs`;
- `looktype`;
- `lookaddons`;
- `maglevel`;
- `mana`;
- `manamax`;
- `manaspent`;
- `soul`;
- `town_id`;
- `posx`;
- `posy`;
- `posz`;
- `conditions`;
- `cap`;
- `sex`;
- `pronoun`;
- `istutorial`;
- `skill_fist`;
- `skill_fist_tries`;
- `skill_club`;
- `skill_club_tries`;
- `skill_sword`;
- `skill_sword_tries`;
- `skill_axe`;
- `skill_axe_tries`;
- `skill_dist`;
- `skill_dist_tries`;
- `skill_shielding`;
- `skill_shielding_tries`;
- `skill_fishing`;
- `skill_fishing_tries`.

No other `players` column is approved for direct insertion by this principal in v1.

Exact inserted values are:

- `name =` canonical validated name;
- `group_id = 1`;
- `account_id =` exact ready bound Canary account ID;
- `level = 8`;
- `vocation =` validated value in `{1,2,3,4,9}`;
- `health = 185`;
- `healthmax = 185`;
- `experience = 4200`;
- `lookbody = 120`;
- `lookfeet = 115`;
- `lookhead = 114`;
- `looklegs = 132`;
- `looktype = 136` when `sex = 0`, otherwise `128`;
- `lookaddons = 0`;
- `maglevel = 0`;
- `mana = 90`;
- `manamax = 90`;
- `manaspent = 0`;
- `soul = 100`;
- `town_id = 8`;
- `posx = 0`;
- `posy = 0`;
- `posz = 0`;
- `conditions =` empty non-null payload;
- `cap = 470`;
- `sex =` validated value in `{0,1}`;
- `pronoun = 0`;
- `istutorial = 0`;
- every listed classic skill level = `10`;
- every listed classic skill tries value = `0`.

All other current `players` columns are intentionally omitted and use the current Canary schema default or nullable behavior. Real MariaDB coverage asserts representative relied-upon defaults including:

- `deletion = 0`;
- `save = 1`;
- `balance = 0`;
- `stamina = 2520`;
- `offlinetraining_time = 43200`;
- `offlinetraining_skill = -1`;
- `isreward = 1`;
- `forge_dust_level = 100`.

A future Canary schema change that removes or materially changes a relied-upon default requires contract revalidation before deployment.

## No dependent starter writes

The operation performs no direct writes to:

- `player_items`;
- inbox/store-inbox/depot/reward tables;
- stash or storage tables;
- quest/tutorial side tables;
- learned-spell tables;
- guild tables;
- `account_sessions`;
- `cluster_sessions`;
- account credential fields.

No incidental first-login script is treated as part of the atomic creation contract.

## Operation identity and idempotency

The durable v1 operation identity is:

`(authorized Canary accounts.id, canonical players.name)`

A separate client-supplied idempotency key is not used.

After locking the authorized account row, the operation queries the exact canonical name.

Existing-name classification:

- same authorized account and `deletion = 0` -> return existing `players.id` as idempotent/recovered success without mutation;
- different account -> deterministic `name_conflict`;
- same account with `deletion != 0` -> deterministic `name_conflict`.

Repeated same-account requests for an active canonical name do not change vocation, sex or gameplay state. The first successful creation wins.

No `UPDATE`, ownership reassignment or destructive compensation is permitted for retry recovery.

An ambiguous connection/commit outcome is not treated as success from the exception alone. A normal retry re-enters the complete operation and may recover a previously committed same-account active player by the natural operation identity.

## Exact Canary transaction — IMPLEMENTED

Using only the dedicated `canary_character_create` connection:

1. begin a Canary database transaction;
2. lock and verify the authorized account row with `SELECT id FROM accounts WHERE id = ? FOR UPDATE`;
3. missing account -> roll back and return `account_missing`;
4. query `SELECT id, name, account_id, deletion FROM players WHERE name = ? LIMIT 1`;
5. classify existing exact-name state using the idempotency/conflict rules above;
6. same-account active recovery returns the existing player ID before quota evaluation;
7. if no exact-name row exists, count active characters for the locked account with `deletion = 0`;
8. count `>= 10` -> roll back and return `character_limit`;
9. insert exactly the approved starter columns and values;
10. obtain the generated `players.id` from the same connection;
11. commit;
12. return the created player ID.

The recovery check occurs before the quota check so retrying a successfully created tenth character returns the same player rather than a false limit error.

## Concurrency guarantees — TESTED

### Same account quota race

Every create transaction for one Canary account locks the same `accounts.id` row before recovery, count and insert. Two concurrent creates therefore serialize.

Real MariaDB integration coverage proves that with nine active characters, concurrent distinct-name requests result in exactly one tenth active character and one limit outcome.

### Global same-name race

`players.name` database uniqueness is the final durable guard.

Real MariaDB integration coverage proves that two different accounts racing the same canonical name produce exactly one committed player row. The losing operation resolves to deterministic conflict unless normal same-account recovery applies.

A pre-insert availability query is never treated as durable uniqueness evidence.

## Transient retry

The implementation may retry the whole Canary transaction up to **3 total attempts** only for explicitly recognized deadlock/serialization failures.

Every retry uses the same:

- authenticated Platform Identity authorization result;
- bound Canary account ID;
- canonical name;
- validated vocation;
- validated sex.

Permanent validation, ownership, conflict, quota and privilege errors are not automatically retried.

## Result/error contract

Bounded outcomes are equivalent to:

- `created`;
- `existing`;
- `validation_failed`;
- `binding_not_ready`;
- `account_missing`;
- `name_conflict`;
- `character_limit`;
- `dependency_unavailable`.

Raw SQL errors, credentials, connection strings and database exception text are not exposed to the browser.

## Dedicated least-privilege database boundary — IMPLEMENTED

The operation uses a third independent connection/principal:

`canary_character_create`

It does not reuse or broaden:

- `canary` / `oteryn_readonly`;
- `canary_provisioning`.

Approved SELECT surface is column-level only:

- `accounts(id)`;
- `players(id, name, account_id, deletion)`.

Approved INSERT surface is column-level only for the exact starter columns listed above.

The principal must not have:

- table-level SELECT on `accounts` or `players`;
- access to `accounts.password`, email or session material;
- player UPDATE or DELETE;
- INSERT on unapproved player columns;
- writes to unrelated player/account/session tables;
- DDL;
- `GRANT OPTION`;
- administrative privileges.

A reviewed SQL template defines the deployment grants. Production credentials remain outside Git.

## Fail-closed privilege verification — IMPLEMENTED

`CanaryCharacterCreateDatabasePrivilegeVerifier` inspects effective grants and rejects missing required columns or broader/unapproved privileges.

Deployment verification command:

`php artisan canary:verify-character-create-db-privileges`

The verifier does not require or log the database password.

## Validation evidence

PR #41 includes:

- `CharacterNamePolicy` unit coverage;
- authenticated feature coverage using the real Platform login/session establishment path;
- request non-control tests for `account_id` and starter state;
- ready/pending binding authorization tests;
- deterministic conflict/idempotent-result tests;
- exact grant verifier unit tests;
- real MariaDB tests for exact grants and denied privileges;
- real MariaDB `FOR UPDATE`, active quota and starter/default persisted row tests;
- real MariaDB same-account last-slot race;
- real MariaDB cross-account same-name race;
- committed-row forward recovery coverage.

Clean implementation head `27520854e326ada46c19ba1bfcda05fe89de2cab` passed:

- CI #563: Composer validation/install, Pint, PHPStan and full tests including real MariaDB integration/race coverage;
- Agent Governance #484.

The initial character feature-test 403 was a test-harness mismatch: synthetic `actingAs()` state did not establish the production `identity.web_session_generation` marker required by `EnsureIdentitySessionIsCurrent`. Feature tests now use the real `POST /login` flow, preserving the production security invariant instead of bypassing middleware.

## Deployment gate

Repository implementation readiness does not itself prove production deployment readiness.

Before enabling this write path in an environment:

1. provision the dedicated `canary_character_create` principal out-of-band using the reviewed SQL template;
2. provide its secret through approved deployment secret management, never Git;
3. run `php artisan canary:verify-character-create-db-privileges`;
4. fail closed if verification reports missing or excessive grants;
5. revalidate the contract if the deployed Canary schema materially differs from the evidence pin.

## Cross-repository decision

No `blakinio/canary` or `opentibiabr/login-server` modification was required for this bounded character-create database operation.

The future authoritative Platform game-login bridge remains a separate cross-repository dependency. It must not be inferred as implemented by character creation.

## Decision

`CHARACTER CREATE OPERATION: IMPLEMENTED AND VALIDATED ON PR #41`

`CHARACTER CREATION: MERGE PENDING`

After PR #41 merges, Phase 5 closure must revalidate the roadmap exit gate against live `main`. Character deletion and rename remain optional future lifecycle operations and are forbidden until separately contracted and tested.
