# Canary Data Integration Contract

## Status

`PARTIALLY PROVEN — READ CONTRACT AVAILABLE / SHARED WRITES BLOCKED`

This document is evidence-backed against the exact `blakinio/canary` revision recorded below. It defines what Oteryn Platform may safely rely on for read-oriented integration and identifies unresolved or conflicting behavior that blocks direct shared-data mutation.

It does **not** approve authentication/session policy, credential migration, character creation, ban administration, guild mutation, coin mutation, or any other direct shared-data write path.

## Evidence baseline

### Pinned Canary revision

- Repository: `blakinio/canary`
- Branch observed: `main`
- Commit: `6df7f906ed6f8fef0aa326439a5494bd1e3d523c`
- Discovery date: `2026-07-18`
- Access mode: read-only

All `PROVEN` statements below refer to that exact commit unless another source is stated explicitly.

### Primary evidence

| ID | Source | What it proves |
|---|---|---|
| E1 | `schema.sql` | Fresh schema at database version 61; tables, columns, indexes, foreign keys, triggers and multichannel tables. |
| E2 | `src/database/databasemanager.cpp` | Numbered Lua migrations newer than `server_config.db_version` are applied in ascending order and then advance the version. |
| E3 | `data-otservbr-global/migrations/59.lua` | Addition of channels, runtime status, cluster sessions and other multichannel structures. |
| E4 | `data-otservbr-global/migrations/60.lua` | Per-channel house identity migration. |
| E5 | `data-otservbr-global/migrations/61.lua` | `channel_switch_audit.consumed_at` migration. |
| E6 | `src/account/account.cpp` | Account loading/authentication entry points and session-expiry/password behavior used by Canary. |
| E7 | `src/account/account_repository_db.cpp` | Exact account/session/player ownership queries and account persistence fields. |
| E8 | `src/io/iologindata.cpp` | Character ownership/deletion checks and transactional player-save entry point. |
| E9 | `src/io/functions/iologindata_load_player.cpp` | Player preload/full-load semantic requirements for account, group, vocation, town and core fields. |
| E10 | `src/io/ioguild.cpp` | Guild/rank loading and Canary-owned guild balance persistence. |
| E11 | `src/creatures/players/management/ban.cpp` | Runtime account/IP ban expiration behavior and namelock lookup. |
| E12 | `src/server/network/protocol/protocollogin.cpp` | Character/world list behavior, including multichannel repetition of global characters. |
| E13 | `src/server/network/protocol/protocolgame.cpp` | Account ban gate and cluster-session acquire before world placement. |
| E14 | `src/server/network/protocol/protocolstatus.cpp` | Single-process online count/list/status is derived from live `g_game()` memory. |
| E15 | `src/game/multichannel/channel_registry.cpp` | `channels` loading, selectable-channel behavior and bootstrap Channel 1 behavior. |
| E16 | `src/game/multichannel/channel_runtime_registry.hpp` | Redis-backed fresh/fail-closed per-channel runtime availability snapshot. |
| E17 | `src/game/multichannel/cluster_session_repository.hpp` | DB cluster-session repository contract. |
| E18 | `src/game/multichannel/db_cluster_session_repository.cpp` | Current DB acquire/heartbeat/release writes for `cluster_sessions`. |
| E19 | `docs/multichannel/ARCHITECTURE.md` | Current repository-level multichannel ownership model and documented runtime/DB roles, cross-checked against implementation above. |

## Evidence state legend

- `PROVEN` — directly supported by pinned schema/source.
- `DERIVED` — conclusion that follows from proven facts but is not itself an explicit schema/source declaration.
- `UNKNOWN` — not established by the inspected evidence; must not be guessed.
- `CONFLICT` — authoritative source at the pinned revision disagrees with another authoritative source.

## Global ownership boundary

### PROVEN

- Canary persists the game-side account/character/guild/ban/session structures described in this contract.
- `accounts` and `players` are global tables; the proven `players` schema has no `channel_id` or `world_id` column.
- Multichannel operation introduces a separate `channels` registry and runtime/session structures rather than duplicating the character row per channel.
- `ProtocolLogin` repeats the same account character names across selectable channels when multichannel mode is enabled.

### DERIVED

- Oteryn Platform may build read-only integration adapters against the exact proven fields below, provided queries use an explicit allowlist and do not expose private/security fields.
- Direct mutation of these shared structures remains cross-repository integration work even when SQL constraints appear straightforward.

## Accounts

### Table and identity — PROVEN

Table: `accounts`

Primary/business constraints:

- `id` — `INT UNSIGNED AUTO_INCREMENT`, primary key.
- `name` — `VARCHAR(32) NOT NULL`, unique constraint `accounts_unique`.
- `email` — `VARCHAR(255) NOT NULL DEFAULT ''`, indexed but **not unique** in the proven schema.
- `password` — `VARCHAR(255) NOT NULL`, indexed by `accounts_password`.

Other proven columns:

- `premdays`
- `premdays_purchased`
- `lastday`
- `type`
- `coins`
- `coins_transferable`
- `tournament_coins` — see `CONFLICT` below
- `creation`
- `recruiter`
- `house_bid_id`

### Account relationships — PROVEN

- `players.account_id` references `accounts.id` with `ON DELETE CASCADE`.
- `coins_transactions.account_id` references `accounts.id` with `ON DELETE CASCADE`.
- Account VIP structures reference `accounts.id` and cascade on account deletion.
- `account_bans.account_id` and `account_ban_history.account_id` reference `accounts.id`.
- `cluster_sessions.account_id` references `accounts.id` with `ON DELETE CASCADE`.
- `account_house_ownership.account_id` references `accounts.id` with `ON DELETE CASCADE`.
- `account_sessions.account_id` has **no foreign key** to `accounts` in the proven `schema.sql`.

### Account creation trigger — PROVEN

`oncreate_accounts` is an `AFTER INSERT` trigger on `accounts` that inserts three non-customizable rows into `account_vipgroups`:

- `Enemies`
- `Friends`
- `Trading Partner`

### Canary runtime writes — PROVEN

`AccountRepositoryDB::save` updates these fields:

- `type`
- `premdays`
- `lastday`
- `creation`
- `premdays_purchased`
- `house_bid_id`

Coin operations also write account coin columns and `coins_transactions`; multi-coin removal uses a DB transaction and `SELECT ... FOR UPDATE`.

### Account reads allowed to Oteryn Platform

#### PROVEN read candidates

For internal, authorized account integration only:

- `id`
- `name`
- `email`
- `type`
- `premdays`
- `premdays_purchased`
- `lastday`
- `creation`

These are **not automatically public fields**. `email` and account/security state must not be exposed through public game-data endpoints.

#### Security-restricted read fields

The following must remain inside the future Identity/Integration boundary and are not approved for PublicGameData reads:

- `password`
- `account_sessions.*`
- `cluster_sessions.*`
- ban/security state except narrowly authorized use cases

### Account writes allowed to Oteryn Platform

`UNKNOWN / NOT APPROVED`

No direct account-table write is approved by this discovery task. Reasons include:

- authentication/hash compatibility belongs to the separate auth discovery contract;
- account insertion triggers VIP-group side effects;
- Canary itself updates account premium/type/coin state;
- email uniqueness is not enforced by the proven DB schema;
- the tournament coin column conflict below blocks any trustworthy coin write contract.

## Account coin column conflict

### CONFLICT

The pinned revision contains an authoritative schema/code disagreement:

- `schema.sql` defines `accounts.tournament_coins`.
- `AccountRepositoryDB` maps `CoinType::Tournament` to column `coins_tournament`.

The fresh `schema.sql` seeds `server_config.db_version = 61`. `DatabaseManager` only executes migrations with a numeric version **greater** than the current database version, and the latest inspected migration is version 61. Therefore a fresh database created from the pinned schema will not run a later migration that can automatically reconcile this name.

### Consequence

- Tournament-coin reads/writes are **blocked** for Oteryn Platform.
- This conflict should be fixed in Canary through a separately authorized Canary task, or explicitly accommodated by a versioned compatibility contract after the actual deployed database is inspected.
- Oteryn Platform must not guess which column exists in production.

## Characters / players

### Table and ownership — PROVEN

Table: `players`

Core identity constraints:

- `id` — `INT AUTO_INCREMENT`, primary key.
- `name` — `VARCHAR(255) NOT NULL`, unique constraint `players_unique`.
- `account_id` — `INT UNSIGNED NOT NULL DEFAULT 0`, indexed, foreign key to `accounts.id`, `ON DELETE CASCADE`.
- `group_id` — default `1`.
- `deletion` — `BIGINT NOT NULL DEFAULT 0`.

There is no proven `channel_id` / `world_id` field on the player row.

### Core player fields proven in schema/runtime loading

The player loader directly consumes, among others:

- `vocation`
- `group_id`
- `account_id`
- `balance`
- `sex`
- `pronoun`
- `level`
- `soul`
- `cap`
- `mana`, `manamax`, `manaspent`
- `maglevel`
- `health`, `healthmax`
- `posx`, `posy`, `posz`
- `town_id`
- `lastlogin`, `lastlogout`
- `offlinetraining_time`, `offlinetraining_skill`
- `stamina`
- `conditions`
- skill, outfit, blessing and other gameplay fields defined by `schema.sql`

### Character ownership and deletion gate — PROVEN

- Game-world authentication verifies that the selected character name belongs to the authenticated `account_id`.
- Account player loading selects rows by `account_id` and excludes every row where `deletion != 0`.
- Player preload also rejects a row when `deletion != 0`.

### DERIVED

For login/list purposes, `deletion = 0` is the active-character state and any non-zero value is treated as deleted/unavailable.

### UNKNOWN

The exact semantic meaning/unit of the non-zero `deletion` value is not proven by this discovery and must not be assumed to be a timestamp without separate evidence.

### Runtime semantic requirements — PROVEN

Player preload/full-load can fail when:

- `group_id` does not resolve to an existing configured group;
- `account_id` cannot be loaded as an account;
- `vocation` does not resolve to an existing vocation;
- no valid town can ultimately be resolved.

If persisted position is `(0,0,0)`, the loader replaces it with the player's temple position.

### Character creation SQL minimum — PROVEN at schema level only

At the SQL schema level:

- `name` has no default and must be supplied.
- `conditions` is `NOT NULL` with no default and must be supplied.
- `account_id` has a numeric default but is constrained by an FK to `accounts.id`, so a usable character must reference an existing account.
- most other player columns have defaults.

Migration 55 contains one concrete sample-player `INSERT`, but that is sample-data migration evidence, **not** a generic product character-creation contract.

### Character creation — UNKNOWN / NOT APPROVED

A safe Platform character-create operation is not yet proven because the discovery does not establish:

- product name normalization and case-sensitivity rules; the DB collation behavior is not explicitly fixed by the table DDL;
- allowed vocation/sex/pronoun choices for new players;
- authoritative starting town and starting position policy;
- starting level/stats/outfit/skills/items/storage/quest state;
- whether additional dependent rows must be initialized for the Oteryn datapack/product rules;
- concurrency/idempotency behavior for simultaneous creation attempts.

No Platform character-create write path is approved.

### Character hard-delete effects — PROVEN

Many player-owned tables have foreign keys to `players.id` with `ON DELETE CASCADE`, including inventory/depot/inbox/reward and multiple progression tables.

`ondelete_players` is a `BEFORE DELETE` trigger that clears `houses.owner` for houses owned by the deleted player.

### Character hard delete — NOT APPROVED

The existence of cascades does not make direct Platform deletion safe. Character lifecycle policy, soft deletion, house/guild/session/runtime effects and rollback requirements need a dedicated approved operation contract.

### Public character reads approved by this contract

A PublicGameData read model may use an explicit public allowlist such as:

- `players.id`
- `players.name`
- `players.level`
- `players.vocation`
- selected appearance fields when required by the UI
- selected public progression/highscore fields when a feature-specific contract defines them

Mandatory filter:

- exclude `players.deletion != 0` unless an explicitly privileged internal use case needs deleted records.

Do not expose from public character endpoints:

- `account_id`
- `lastip`
- `conditions`
- security/session data
- private comments or other non-public operational fields
- account credentials or email through joins

## Guilds

### Schema — PROVEN

`guilds`:

- `id` primary key
- `name` unique
- `ownerid` unique, FK to `players.id`, `ON DELETE CASCADE`
- `level`
- `creationdata`
- `motd`
- `residence`
- `balance`
- `points`

`guild_ranks`:

- `id` primary key
- `guild_id` FK to `guilds.id`, `ON DELETE CASCADE`
- `name`
- `level`

`guild_membership`:

- `player_id` primary key
- `guild_id` FK to `guilds.id`
- `rank_id` FK to `guild_ranks.id`
- `nick`

Because `player_id` is the primary key, the schema permits at most one `guild_membership` row per player.

`guild_invites` uses composite primary key `(player_id, guild_id)` and references both player and guild.

### Guild creation trigger — PROVEN

`oncreate_guilds` inserts three default ranks:

- `The Leader` — level `3`
- `Vice-Leader` — level `2`
- `Member` — level `1`

The trigger does not itself prove that the owner receives a membership row or a particular rank.

### Guild owner/membership relationship — UNKNOWN

`guilds.ownerid` establishes the owner player, while `guild_membership` establishes membership/rank. The inspected DB constraints do not prove that every owner must simultaneously have a matching membership row or leader rank.

### Canary runtime writes — PROVEN

`IOGuild::saveGuild` writes `guilds.balance`.

### Public guild reads approved by this contract

Read-only public models may use:

- `guilds.id`
- `guilds.name`
- `guilds.ownerid`
- `guilds.level`
- `guilds.creationdata`
- `guilds.motd`
- `guilds.residence`
- `guilds.points`
- `guild_ranks.id/name/level`
- `guild_membership.player_id/guild_id/rank_id/nick`

`guilds.balance` is not approved as a public field by this contract.

### Guild writes allowed to Oteryn Platform

`UNKNOWN / NOT APPROVED`

Creating guilds, assigning membership/ranks, changing ownership or mutating guild balance requires a separate operation-level contract and authorization policy.

## Bans and name locks

### Account bans — PROVEN

`account_bans`:

- primary key `account_id`
- `reason`
- `banned_at`
- `expires_at`
- `banned_by` FK to `players.id`

`account_ban_history` stores historical rows with its own `id` primary key and the corresponding account/reason/time/actor fields.

`IOBan::isAccountBanned` behavior:

- absence of an `account_bans` row means not banned;
- `expires_at = 0` is treated as permanent;
- when a non-zero expiration is in the past, Canary asynchronously inserts the row into `account_ban_history`, asynchronously deletes it from `account_bans`, and treats the account as not banned.

### DERIVED

A read-only Platform ban-status check must evaluate `expires_at`; mere presence of an `account_bans` row can temporarily represent an already-expired ban before Canary's lazy cleanup runs.

### IP bans — PROVEN

`ip_bans` is keyed by `ip` and has `reason`, `banned_at`, `expires_at`, `banned_by`.

Expired IP bans are asynchronously deleted when Canary checks them; no IP-ban history table is proven in the inspected schema.

### Name locks — PROVEN

`player_namelocks.player_id` is unique and references `players.id`. Canary treats presence of a row as namelocked and blocks login before full world entry.

### Ban/name-lock writes allowed to Oteryn Platform

`UNKNOWN / NOT APPROVED`

Ban administration requires dedicated authorization, audit and lifecycle semantics. Direct inserts/deletes are not approved by this contract.

## Sessions

Two different persistent session concepts are proven and must not be conflated.

### `account_sessions` — login/auth session storage

#### PROVEN schema

- `id VARCHAR(191)` primary key
- `account_id INTEGER UNSIGNED NOT NULL`
- `expires BIGINT UNSIGNED NOT NULL`
- no FK to `accounts` in the proven schema

#### PROVEN lookup behavior

`AccountRepositoryDB::loadBySession`:

- hashes the presented session key with SHA-256;
- also computes a legacy SHA-1 hash;
- joins `account_sessions.account_id` to `accounts.id`;
- matches either hashed ID, preferring the SHA-256 match;
- loads `account_sessions.expires`.

`Account::authenticateSession` rejects the session when `sessionExpires < current time`.

#### Scope boundary

Token issuance, TTL policy, single-use behavior, revocation, logout semantics and password-change/reset interaction belong to `AUTH_GAME_LOGIN_CONTRACT.md` and remain outside this data-only discovery.

### `cluster_sessions` — online cluster lease defense-in-depth

#### PROVEN schema

- `account_id` primary key, FK to `accounts.id`
- `player_id` unique, FK to `players.id`
- `channel_id`
- `instance_id`
- `session_id`
- `fencing_token`
- `status` enum: `ACQUIRING`, `ONLINE`, `SAVING`, `DIRTY`, `OFFLINE`
- `acquired_at`
- `last_heartbeat`
- `expires_at`

The proven schema does not define an FK from `cluster_sessions.channel_id` to `channels.id`.

#### PROVEN current DB writer behavior

`DbClusterSessionRepository`:

- acquire clears an inconsistent row for the same `player_id` under a different account, then inserts/upserts an `ONLINE` row;
- heartbeat updates `status`, `fencing_token`, `last_heartbeat` and `expires_at` only when `account_id` and `session_id` match;
- release deletes only when `account_id` and `session_id` match.

`ProtocolGame::login` acquires the cluster session immediately before attempting world placement and releases it if placement fails.

#### DERIVED

`cluster_sessions` is security/concurrency state, not a general-purpose Platform session table. Oteryn Platform must treat it as Canary-owned runtime data unless a future explicit integration operation is approved.

## Worlds / channels / server identifiers

### Persistent channel identity — PROVEN

Table: `channels`

- `id` — primary key; persistent channel identifier used by runtime/session structures
- `name` — unique
- `pvp_type` — enum `no-pvp`, `pvp`, `pvp-enforced`
- `external_host`
- `game_port`
- `status_port`
- `max_players`
- `enabled`
- `sort_order`
- `temple_town_id` — nullable, deliberately no DB FK
- `maintenance`
- `maintenance_message`
- `login_gateway`
- `map_hash`
- `created_at`
- `updated_at`

Unique endpoint constraints exist for `(external_host, game_port)` and `(external_host, status_port)`.

`ChannelRegistry` loads this table. When multichannel is enabled and Channel 1 is missing, `ensureBootstrapChannel` may insert Channel 1 from current server config.

### Character/world model — PROVEN

- A character has one global `players` row.
- In multichannel mode `ProtocolLogin` repeats every character for every selectable channel.
- `ProtocolGame` passes the persistent `ChannelContext` channel ID into cluster-session acquisition.

### Wire world ID distinction — PROVEN

For the modern multichannel character list, `ProtocolLogin` writes a zero-based list index as the protocol `worldId` byte while the persistent registry uses `channels.id`.

### DERIVED

Oteryn Platform must not persist or expose the transient login-list index as the durable channel identifier. Durable references should use `channels.id`.

## Online / status

### Legacy compatibility table — PROVEN

`players_online` exists as:

- `player_id` primary key
- FK to `players.id`, `ON DELETE CASCADE`
- `ENGINE=MEMORY`

### players_online lifecycle — UNKNOWN

The targeted pinned-source inspection did not prove the exact current writer lifecycle that populates/removes `players_online` rows. Therefore this table is **not approved as the sole authoritative online source**, especially for multichannel aggregation.

### Single-process live status — PROVEN

`ProtocolStatus` obtains:

- online count from `g_game().getPlayerStats()`;
- online player list from `g_game().getPlayers()`;
- individual online status from `g_game().getPlayerByName()`.

These are live in-memory values for the current Canary process.

### Multichannel channel availability — PROVEN

`ChannelRuntimeRegistry` is a Redis-backed, fail-closed fresh snapshot of per-channel runtime status. It exposes:

- known/online/full state
- `playersOnline`
- freshness filtering

`ChannelRegistry::getLoginListChannels` uses that runtime registry when enabled; otherwise it falls back to statically selectable channel rows.

`channel_runtime_status` also exists in SQL with per-channel status/count/heartbeat/build/hash fields. Repository architecture documents its DB role as a best-effort diagnostic mirror rather than the primary fast path.

### Global public online list — UNKNOWN / NOT YET CONTRACTED

No single approved Platform read path is proven for a cluster-wide list of online character identities with freshness guarantees.

Candidates have different semantics:

- `players_online` — legacy MEMORY table; current writer lifecycle not proven here;
- process-local `g_game()` — authoritative only inside one process;
- `ChannelRuntimeRegistry` — authoritative for fresh per-channel availability/count but does not expose character identities;
- `cluster_sessions` — persistent account/player/channel lease defense-in-depth, but is runtime security state and may require freshness/status filtering and failure semantics before use as a public read model.

A future PublicGameData task must define the exact online-list source and stale/failure policy instead of guessing.

## Highscores / public character read model

### PROVEN

Character identity and major progression values live globally in `players`, including fields such as `level`, `experience`, `vocation` and skill columns.

### DERIVED

A cluster-wide highscore can be constructed from global player rows without a channel join because character progression is not duplicated per channel.

### Required filter

Exclude `deletion != 0`.

### UNKNOWN

Feature-specific filtering remains to be defined, including:

- privileged/group-hidden players;
- vocation/category rules;
- exact tie ordering;
- query/index strategy for production scale.

The pinned schema has an index on `vocation` but this discovery does not prove dedicated indexes for every future highscore ordering.

## Migration and trigger behavior

### PROVEN migration model

- Fresh `schema.sql` seeds `db_version = 61`.
- `DatabaseManager` scans `${DATA_DIRECTORY}/migrations/`, extracts numeric versions, sorts them, executes migrations with version greater than the current DB version, then updates `server_config.db_version`.
- Migration 59 adds core multichannel registry/session/audit structures.
- Migration 60 adds/rebuilds per-channel house identity.
- Migration 61 adds `channel_switch_audit.consumed_at`.

### PROVEN triggers

- `oncreate_accounts` creates default VIP groups.
- `oncreate_guilds` creates default guild ranks.
- `ondelete_players` clears `houses.owner` for the deleted player.

### Integration consequence — DERIVED

Platform code must not assume a plain row insert/delete is side-effect free. Even when a desired row appears simple, triggers, cascades, runtime writers and migration-version differences can alter the complete operation contract.

## Approved read/write matrix

| Surface | Read status | Write status | Evidence state |
|---|---|---|---|
| Public character profile fields from explicit allowlist | Approved read-only | Not approved | PROVEN/DERIVED |
| Deleted-character filtering via `deletion != 0` | Approved read rule | Not approved | PROVEN |
| Guild public fields/ranks/membership | Approved read-only | Not approved | PROVEN |
| Highscore source from global `players` | Approved conceptually; feature filtering/index plan still required | Not approved | DERIVED/UNKNOWN |
| Channel registry public endpoint metadata | Approved read-only from explicit allowlist | Not approved | PROVEN |
| Per-channel fresh availability/count | Canary runtime semantics proven; Platform transport/cache integration still required | Not approved | PROVEN/UNKNOWN |
| Cluster-wide online character list | Not yet approved | Not approved | UNKNOWN |
| Account internal identity/profile fields | Approved only inside authorized Accounts/Identity integration | Not approved | PROVEN |
| Password/session credential fields | Security-restricted | Not approved | PROVEN + auth contract pending |
| Account/character bans and namelocks | Authorized internal reads only | Not approved | PROVEN |
| `cluster_sessions` | Runtime/security internal read only | Not approved | PROVEN |
| Account coins | Normal/transferable schema/code partly proven; tournament coin path blocked | Not approved | CONFLICT |
| Account creation | Not applicable | Not approved | UNKNOWN |
| Character creation/delete/rename | Not applicable | Not approved | UNKNOWN |
| Guild create/member/owner/rank mutations | Not applicable | Not approved | UNKNOWN |

## Shared write policy

There are currently **zero approved direct shared-data write operations** from Oteryn Platform to Canary-owned/shared tables.

Every future approved write operation must add a versioned operation section containing:

```text
Operation:
Primary owner:
Caller:
Tables/models/fields:
Preconditions:
Authorization:
Validation:
Transaction boundary:
Locking/concurrency:
Runtime/cache side effects:
Failure/rollback behavior:
Compatibility version/evidence:
Tests:
```

A schema-level ability to execute an `INSERT`, `UPDATE` or `DELETE` is not sufficient approval.

## Remaining UNKNOWN items

- exact deployed production database schema/version and whether it contains `tournament_coins` or `coins_tournament`;
- current writer lifecycle of `players_online`;
- approved cluster-wide online character read source and freshness/failure policy;
- exact product rules and dependent initialization for character creation;
- character rename/delete lifecycle and rollback policy;
- operation-level account creation/change contract;
- operation-level guild mutation contract;
- auth/session issuance/revocation behavior owned by `AUTH_GAME_LOGIN_CONTRACT.md`;
- which, if any, shared write operations Oteryn Platform should eventually own.

## Current CONFLICT items

1. `accounts.tournament_coins` in `schema.sql` vs `accounts.coins_tournament` expected by `AccountRepositoryDB`.

This conflict blocks tournament-coin integration and must not be converted into an assumption.

## Safety rules

- `blakinio/canary` remains read-only unless a separately authorized task explicitly permits writes.
- Do not copy MyAAC/TFS schema assumptions into Platform code.
- Prefer explicit read models that select only approved columns.
- Never expose `password`, session IDs/tokens, account email, IP addresses or other security/private fields through public game-data endpoints.
- Do not use `players_online` as the sole multichannel online authority without a proven lifecycle/freshness contract.
- Do not use protocol world-list index as persistent channel identity; use `channels.id`.
- Do not implement shared account/character/guild/ban/session/coin writes until a specific operation section is approved.
- Schema drift or unresolved column conflicts must fail visibly rather than silently falling back to guessed names.

## Next contract dependency

The next bounded discovery should complete `docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md` against the same current Canary/login-server reality before any credential migration or global authentication-policy implementation is approved.