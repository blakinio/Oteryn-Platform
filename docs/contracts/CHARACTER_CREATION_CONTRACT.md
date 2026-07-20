# Platform-driven Character Creation Contract

## Status

`APPROVED IMPLEMENTATION SHAPE — CHARACTER SHARED WRITE NOT YET IMPLEMENTED`

This contract defines the only approved Phase 5 operation for creating a greenfield Canary `players` row for an authenticated Oteryn Platform Identity under ADR 0005.

It does not implement the shared write. It fixes the exact authorization, validation, transaction, idempotency, concurrency and least-privilege database boundary that the successor implementation task must satisfy.

`CHARACTER CREATION: BLOCKED` until that implementation and its required tests merge.

## Evidence baseline

### Oteryn Platform

- Ownership model: `1 Platform Identity <-> 1 Canary accounts.id`.
- Greenfield account provisioning and immutable binding are implemented through PR #33 / `d5c319448737ee5badd8ab73967535a5ec9b67d1`.
- ADR 0005 character creation product policy is merged through PR #37 / `c5b8719de51deec6cea6d9270e55416fba1d6472`.
- Authorization must resolve the target Canary account exclusively from the authenticated Identity's ready `identity_canary_accounts.canary_account_id` binding.
- The existing `canary` read-only and `canary_provisioning` account-create database boundaries remain unchanged.

### Canary

- Repository: `blakinio/canary`.
- Current inspected main: `800142e65c2975e57647bf34128ab468532218f0`.
- The one commit after the ADR 0005 evidence pin modifies OAM documentation only; the inspected player schema/load semantics are unchanged.
- Access mode during this task: read-only.

Current proven persistence facts:

- `players.id` is auto-generated;
- `players.name` is unique;
- `players.account_id` references `accounts.id`;
- `players.conditions` is the only inspected starter-relevant required field without a schema default;
- classic skills default to level `10` with zero tries;
- all other omitted fields used by this contract have an explicit current schema default or are nullable;
- no generic `players` insert trigger was proven in the inspected schema;
- player load requires a resolvable account, group and vocation;
- a persisted `(0,0,0)` login position is replaced with the player's temple position;
- an empty `conditions` stream produces no stored conditions and is accepted by the current load path.

## Authorization — REQUIRED

Before opening the character-create Canary transaction:

1. the caller must be an authenticated Platform Identity;
2. the Platform-owned Identity-to-Canary binding must exist and be `ready`;
3. the binding must contain one non-null `canary_account_id`;
4. that exact ID becomes the operation's server-side `account_id`;
5. no browser/client-supplied `account_id`, account name or email value may override or establish authorization.

If the binding is absent, pending or conflict, fail closed before any character-write connection is used.

## Input validation and canonical name

The only user-controlled operation inputs are:

- `name`;
- `vocation`;
- `sex`.

The server must apply ADR 0005 canonicalization and reserved-name rules before opening the shared-write transaction.

The canonical name is the only name passed to Canary.

Allowed vocation IDs are exactly:

`1`, `2`, `3`, `4`, `9`.

Allowed sex values are exactly:

`0`, `1`.

All other creation-time fields are server-selected constants from ADR 0005.

Validation failure returns a deterministic validation result and performs no Canary write.

## Exact starter row

### Explicit INSERT columns

The successor implementation SHALL insert exactly the following `players` columns:

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

No other `players` column is approved for direct insertion by the Platform character-create principal in v1.

### Exact values

The insert values are:

- `name =` canonical validated name;
- `group_id = 1`;
- `account_id =` exact ready bound Canary account ID;
- `level = 8`;
- `vocation =` validated request value in `{1,2,3,4,9}`;
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
- `conditions =` empty non-null binary/string payload;
- `cap = 470`;
- `sex =` validated request value in `{0,1}`;
- `pronoun = 0`;
- `istutorial = 0`;
- every listed classic skill level = `10`;
- every listed classic skill tries value = `0`.

The implementation must not accept these server-selected values from browser input.

### Intentionally omitted columns

All other current `players` columns are omitted from the INSERT and therefore use the exact current Canary schema default or nullable behavior.

This includes unrelated lifecycle/gameplay state such as login timestamps/IP, skull/blessings, balance, stamina/offline-training state, reward/forge/prey/task fields, mount/familiar state and other advanced gameplay values.

This omission is approved only for the current evidence pin. The real MariaDB integration test must assert a representative deterministic default set, including at minimum:

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

The transaction performs no direct writes to:

- `player_items`;
- inbox/store-inbox/depot/reward tables;
- `player_stash`;
- player storage tables;
- quest/tutorial side tables;
- learned-spell tables;
- guild tables;
- `account_sessions`;
- `cluster_sessions`;
- any account credential field.

No incidental `onLogin` script is part of the atomic creation contract.

## Operation identity and idempotency

### Natural operation identity

For v1, the durable operation identity is:

`(authorized Canary accounts.id, canonical players.name)`

A separate client-supplied idempotency key is not required.

This is valid for the current product because character rename is not implemented and the canonical character name is globally unique in Canary.

### Existing-name classification

After locking the authorized account row, the operation queries the exact canonical name.

If no row exists, normal limit-check and insert processing continues.

If a row exists:

- `existing.account_id == authorized account_id` and `existing.deletion == 0`:
  return the existing `players.id` as idempotent/recovered success without modifying the row;
- `existing.account_id != authorized account_id`:
  return deterministic `name_conflict`;
- `existing.account_id == authorized account_id` and `existing.deletion != 0`:
  return deterministic `name_conflict`; a deleted/pending-deletion row is not recovered as active success.

Repeated requests for an already-active same-account canonical name do not change its vocation, sex or other gameplay state. The first successful creation wins; later repeated calls are read-only idempotent success for that operation identity.

No `UPDATE` or ownership reassignment is permitted for retry recovery.

### Ambiguous commit recovery

If the database client reports an ambiguous connection/commit outcome, the Platform must not claim success from the exception alone.

A retry of the same authorized account plus canonical name runs the full operation again. The exact-name classification above recovers a previously committed same-account active player ID, or returns a deterministic conflict if another account owns the name.

This forward-recovery model requires no destructive compensation and no generic player `UPDATE` privilege.

## Exact Canary transaction

Using only the dedicated `canary_character_create` connection:

1. begin a Canary database transaction;
2. lock and verify the exact authorized account row:
   `SELECT id FROM accounts WHERE id = ? FOR UPDATE`;
3. if the row does not exist, roll back and return `account_missing`;
4. query exact canonical name state:
   `SELECT id, name, account_id, deletion FROM players WHERE name = ? LIMIT 1`;
5. classify an existing row using the idempotency/name-conflict rules above;
6. if same-account active recovery succeeds, commit/close the read-only transaction and return that existing player ID without applying the quota check;
7. if no exact-name row exists, count active characters while the account row remains locked:
   `SELECT COUNT(id) FROM players WHERE account_id = ? AND deletion = 0`;
8. if count is `>= 10`, roll back and return `character_limit`;
9. insert exactly the approved starter columns and values;
10. obtain the generated `players.id` from the same connection/insert result;
11. commit;
12. return the created player ID.

The exact-name recovery check occurs before the quota check so retrying the successful creation of the tenth character returns the same player instead of a false limit error.

## Concurrency guarantees

### Same account

Every Platform character-create transaction for one Canary account locks the same `accounts.id` row before name recovery, active count and insert.

Therefore two concurrent creates for one account serialize. When nine active characters exist, at most one of two concurrent new-name requests may commit the tenth active character; the other observes count `10` and returns `character_limit`.

### Global same-name race

`players.name` database uniqueness is the final durable guard.

If two accounts race to insert the same canonical name, only one insert may commit. The losing operation maps the duplicate-key result to `name_conflict` unless a subsequent full retry finds that the same authorized account owns the active row, in which case it is idempotent success.

A pre-insert availability query is not sufficient authorization or uniqueness evidence.

## Deadlock and serialization retry

The implementation may retry the entire Canary transaction up to **3 total attempts** only for database errors explicitly classified as transient deadlock or serialization failures.

Every retry must reuse the same:

- authenticated Platform Identity authorization result;
- bound Canary account ID;
- canonical name;
- validated vocation;
- validated sex.

Permanent validation, ownership, name-conflict, quota and privilege errors are not automatically retried.

After the bounded transient retry budget is exhausted, return deterministic dependency-unavailable/retryable failure and do not claim success unless a subsequent normal operation retry recovers an active same-account canonical name.

## Error/result contract

The implementation must expose bounded operation outcomes equivalent to:

- `created` — new player committed;
- `existing` — same-account active canonical name recovered idempotently;
- `validation_failed` — name/vocation/sex policy failure before shared write;
- `binding_not_ready` — Platform ownership binding absent/pending/conflict;
- `account_missing` — bound Canary account row missing at transaction time;
- `name_conflict` — canonical name belongs to another account or is held by a deleted same-account row;
- `character_limit` — account already has 10 active characters;
- `dependency_unavailable` — bounded transient/database failure with no proven success.

Raw SQL errors, connection strings, credentials or database exception text must not be exposed to the browser or security audit log.

## Dedicated least-privilege database boundary

A third independent database connection/principal SHALL be introduced by the implementation task:

`canary_character_create`

It must not reuse or broaden:

- `canary` / `oteryn_readonly`;
- `canary_provisioning`.

### Approved SELECT surface

Column-level SELECT is approved only for:

- `accounts(id)`;
- `players(id, name, account_id, deletion)`.

These columns are sufficient for:

- `SELECT id ... FOR UPDATE` on the authorized account;
- exact-name recovery/conflict classification;
- `COUNT(id)` with `account_id` and `deletion` predicates.

The implementation's real MariaDB integration test must prove that these column-level grants are sufficient for the exact queries, including `FOR UPDATE`.

### Approved INSERT surface

Column-level INSERT is approved only for the exact `players` columns listed in **Explicit INSERT columns** above.

### Explicitly forbidden privileges

The principal must not have:

- table-level `SELECT` on `accounts` or `players`;
- `SELECT` on `accounts.password`, email, session material or other account columns;
- `UPDATE` or `DELETE` on `players`;
- any `INSERT` column on `players` outside the approved list;
- writes to player items/storage/inbox/depot/reward/spell/guild tables;
- access to `account_sessions` or `cluster_sessions` beyond unrelated existing read credentials;
- DDL;
- `GRANT OPTION`;
- administrative privileges.

A reviewed provisioning SQL template must create/grant this exact surface for deployment. Production credentials remain outside Git.

## Fail-closed privilege verification

The implementation task SHALL add a non-destructive verifier for the effective `canary_character_create` grants.

The verifier must fail when:

- an approved SELECT or INSERT column is missing;
- table-level SELECT/INSERT is present where only column-level access is approved;
- any extra players INSERT column is granted;
- UPDATE/DELETE is granted;
- an unrelated table privilege is present;
- `GRANT OPTION` or administrative capability is present.

The verifier must not require or log database passwords.

## Required implementation tests

The successor implementation task must cover at minimum:

### Platform authorization and validation

1. pending/absent/conflict binding fails before the character-create gateway is invoked;
2. browser/client cannot choose `account_id`;
3. canonicalization examples and invalid ASCII/spacing/length cases;
4. reserved words, phrases and protected brand-prefix cases;
5. only vocations `1,2,3,4,9` and sex `0,1` are accepted;
6. server-selected starter fields cannot be overridden by request input.

### Operation behavior

7. successful create returns one generated player ID and exact canonical name;
8. same-account same canonical active name returns the same player ID without a second insert;
9. same canonical name owned by another account returns `name_conflict`;
10. same-account deleted/pending-deletion same name returns `name_conflict`;
11. missing bound account row returns `account_missing` and writes no player;
12. ten active characters returns `character_limit`;
13. deleted rows do not count toward the active-character quota;
14. retrying the successfully created tenth character returns existing success before quota evaluation;
15. duplicate-key global name race maps to deterministic conflict/recovery behavior;
16. ambiguous-commit simulation can recover the same committed player by authorized account plus canonical name;
17. bounded deadlock/serialization retry never exceeds three attempts and never retries permanent conflicts.

### Real MariaDB integration

18. the dedicated principal can execute exact account `FOR UPDATE`, exact-name SELECT, active COUNT and approved INSERT;
19. inserted row contains every explicit starter value and representative approved schema defaults;
20. empty non-null `conditions` is stored and retrievable as an empty payload;
21. two concurrent creates at account count nine result in exactly one new active row and one limit outcome;
22. two different accounts racing the same name produce exactly one committed player row;
23. the principal cannot read `accounts.password`;
24. the principal cannot select unapproved `players` columns;
25. the principal cannot update/delete players or write unrelated tables;
26. privilege verifier accepts only the exact approved grant set and rejects missing/excessive grants.

SQLite-only or mocked tests are insufficient for the grant, row-locking, limit-race and unique-name-race assertions.

## Runtime/loadability evidence and gate

Current Canary source proves:

- all allowed vocation IDs resolve in the current vocation configuration;
- the player load path consumes the selected core fields;
- empty conditions are accepted;
- `(0,0,0)` uses the player's temple position;
- invalid town handling attempts a valid fallback rather than silently authorizing ownership changes.

The Oteryn implementation test must validate the exact persisted database shape on MariaDB. No Canary code change is currently required by this contract.

If a later runtime/game E2E demonstrates that the selected `town_id = 8` starter or another ADR 0005 invariant is not loadable in the deployed datapack, character creation deployment must fail closed and the exact Canary/datapack correction must be handled as a separately authorized cross-repository task.

## Online and session effects

Character creation does not create or mutate:

- `account_sessions`;
- `cluster_sessions`;
- runtime availability records.

A newly committed character is expected to appear on a subsequent fresh authoritative character-list/login flow. No dynamic refresh of an already-issued character list is claimed.

## Cross-repository decision

No `blakinio/canary` or `opentibiabr/login-server` change is required to implement this bounded database character-create operation under the currently inspected contract.

The separate future Platform-authorized game-login bridge remains required before Platform-originated users can use the authoritative Platform credential model for game login. That work remains outside this character-create operation.

## Decision

`CHARACTER CREATE OPERATION CONTRACT: APPROVED FOR IMPLEMENTATION`

The approved implementation is:

`authenticated Platform Identity -> ready immutable Canary account binding -> canonical validated character request -> dedicated locked Canary transaction -> exact players insert or deterministic idempotent recovery`

Authorization, product policy, transaction ordering, quota concurrency, retry identity and least-privilege surfaces are now explicit.

No character shared write has been implemented by this contract task.

`CHARACTER CREATION: BLOCKED`

The next dependency is the bounded implementation task. It may unblock character creation only after the exact code, dedicated database privilege boundary and required real MariaDB/security/concurrency tests pass.
