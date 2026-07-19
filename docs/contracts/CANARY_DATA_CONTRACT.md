# Canary Data Integration Contract

## Status

`PARTIALLY PROVEN — READ CONTRACT AVAILABLE / SHARED WRITES BLOCKED`

This document is evidence-backed against the exact `blakinio/canary` revision recorded below. It defines what Oteryn Platform may safely rely on for read-oriented integration and identifies unresolved or conflicting behavior that blocks direct shared-data mutation.

It does **not** approve authentication/session policy, credential migration, character creation, ban administration, guild mutation, coin mutation, or any other direct shared-data write path.

## Evidence baseline

### Pinned Canary revision

- Repository: `blakinio/canary`
- Branch observed: `main`
- Commit: `be7842412beb5d240e76ffd4cd18aacdc3a2dcca`
- Discovery date: `2026-07-19`
- Access mode: read-only

The previous contract baseline was `6df7f906ed6f8fef0aa326439a5494bd1e3d523c`. A direct comparison from that revision to the current pinned revision showed only agent/handover documentation changes, so the source/schema evidence below was not changed by the revision advance. All `PROVEN` statements below refer to the current pinned commit unless another source is stated explicitly.

### Primary evidence

| ID | Source | What it proves |
|---|---|---|
| E1 | `schema.sql` | Fresh schema at database version 61; tables, columns, indexes, foreign keys, triggers and multichannel tables. |
| E2 | `src/database/databasemanager.cpp` | Numbered Lua migrations newer than `server_config.db_version` are applied in ascending order and then advance the version. |
| E3 | `data-otservbr-global/migrations/59.lua` | Addition and exact shape of channels, runtime status, cluster sessions and other multichannel structures. |
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
| E14 | `src/server/network/protocol/protocolstatus.cpp` | Single-process online count/list/status is derived from live process-local `g_game()` memory. |
| E15 | `src/game/multichannel/channel_registry.cpp` | `channels` loading, selectable-channel behavior and bootstrap Channel 1 behavior. |
| E16 | `src/game/multichannel/channel_runtime_registry.hpp` and `channel_runtime_status.hpp` | Redis-backed fresh/fail-closed per-channel runtime availability snapshot and its freshness/state rules. |
| E17 | `src/game/multichannel/cluster_session_repository.hpp` | DB cluster-session repository contract. |
| E18 | `src/game/multichannel/db_cluster_session_repository.cpp` | Current DB acquire/heartbeat/release writes for `cluster_sessions`. |
| E19 | `docs/multichannel/ARCHITECTURE.md` | Current repository-level multichannel ownership model and documented runtime/DB roles, cross-checked against implementation above. |
| E20 | `data/scripts/globalevents/server_initialization.lua` | Every process startup cleanup truncates the shared `players_online` table. |
| E21 | `src/game/game.cpp` and `src/game/game.hpp` | `players_online` periodic writer/pruner lifecycle, local player-map ownership, session heartbeat scheduling and process-local player statistics. |
| E22 | `src/game/multichannel/cluster_runtime.cpp` and `.hpp` | Redis lease acquisition/renewal/release, DB dual-write failure behavior, lease/failure timing and runtime-status publication. |
| E23 | `src/game/multichannel/cluster_session_lookup.cpp` and `.hpp` | Current Canary admin lookup semantics: `ONLINE` filtering without `expires_at`, and DB-query failure collapsing to an empty list for `listOnlinePlayers()`. |
| E24 | `src/game/multichannel/cluster_session_manager.cpp` and `.hpp` | Atomic Redis session lease/fencing lifecycle and owner-only renew/release semantics. |
| E25 | `src/utils/tools.cpp` and `src/game/multichannel/wall_clock.hpp` | Session/runtime timestamps use Unix-epoch system-clock milliseconds and are consumer-comparable wall-clock values. |

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

The following must remain inside the future Identity/Integration boundary and are not approved for direct public exposure:

- `password`
- `account_sessions.*`
- raw `cluster_sessions.*` fields; the only approved public use is the sanitized online-read adapter defined below, which must not expose account/session/lease identifiers
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

`ProtocolGame::login` acquires the Redis cluster lease immediately before attempting world placement. The initial `cluster_sessions` DB acquire write is part of the login gate: if it fails, Canary releases the just-acquired Redis lease and rejects the login. A placement failure also releases the acquired session.

Routine heartbeat DB writes are best-effort after a successful Redis renew. A transient DB heartbeat failure does not disconnect a player whose Redis lease is still valid. Clean logout and Redis-outage force-expiry paths perform best-effort row deletion.

The current default timing is:

- session lease TTL: `30000 ms`;
- heartbeat interval: `5000 ms`;
- Redis failure grace period: `10000 ms`.

Canary requires lease TTL to be greater than the heartbeat interval. `expires_at` is written as Unix-epoch system-clock milliseconds, not as a process-relative monotonic value.

#### DERIVED

`cluster_sessions` is Canary-owned security/concurrency state, not a general-purpose Platform session table. Raw account/session/lease fields remain internal. However, the table is approved below as the backend identity source for one narrowly sanitized PublicGameData online read model because its acquire/heartbeat/expiry lifecycle provides a bounded freshness contract when the consumer applies the required predicates.

A consumer must not depend on physical deletion of expired rows. The inspected schema has no database-native TTL, and an ungraceful process crash can leave an `ONLINE` row physically present after its lease expiry. Whether another separate cleanup path eventually deletes every such orphan remains `UNKNOWN`; correctness must come from expiry filtering, not eventual cleanup.

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

### `players_online` lifecycle — PROVEN / REJECTED AS CLUSTER AUTHORITY

Current Canary behavior is explicit:

- every process startup GlobalEvent calls `cleanupDatabase()`, which executes `TRUNCATE TABLE players_online` against the shared database;
- every process schedules `Game::updatePlayersOnline()` every 10 minutes;
- that function reads only the current process-local `Game::players` map;
- when the local process has players, it `INSERT IGNORE`s their GUIDs and then deletes every `players_online` row whose `player_id` is not in that one local process set;
- when the local process has no players, it clears the table if rows exist.

#### DERIVED consequence

In multi-channel mode, `players_online` has last-process-writer/local-channel semantics: one channel process can prune valid rows belonging to every other channel. No Platform-side freshness filter can repair that identity loss. `players_online` is therefore **not approved** as a cluster-wide online-character source.

### Single-process live status — PROVEN

`ProtocolStatus` obtains:

- online count from `g_game().getPlayerStats()`;
- online player list from `g_game().getPlayers()`;
- individual online status from `g_game().getPlayerByName()`.

`Game::getPlayerStats()` itself groups only the current process-local player map by IP before applying the public-count rules. These are live values for one Canary process and are not a cluster-wide character-identity source.

### Multichannel channel availability — PROVEN

`ChannelRuntimeRegistry` is the Redis-backed fail-closed fast path for per-channel runtime availability:

- publish/read transport failure clears the entire in-process snapshot rather than preserving a partial view;
- `getStatus`/availability methods reject records older than `staleAfterMs`;
- `ClusterRuntime` configures its Redis TTL and local stale cutoff from the session lease TTL;
- the published local `playersOnline` value is the count of locally tracked cluster sessions;
- graceful shutdown publishes `OFFLINE`; crash/unreachable state ages out through heartbeat freshness/Redis TTL.

`channel_runtime_status` is a SQL table keyed by `channel_id` with `instance_id`, `node_id`, `started_at`, `last_heartbeat`, `status`, `players_online` and build/map/data hash diagnostics. `ClusterRuntime` writes it asynchronously as a best-effort mirror after the Redis fast-path publish/refresh. A SQL mirror write failure does not invalidate a healthy Redis runtime path.

#### DERIVED boundary

Fresh per-channel availability/count is a separate concern from online-character identity. A future Platform runtime-availability integration may use a purpose-built Redis transport or a separately contracted SQL freshness read, but SQL `channel_runtime_status` is **not required as a hard gate** for the online-character identity contract below. Requiring that best-effort diagnostic mirror would create an independent false-negative path without tightening the `cluster_sessions.expires_at` identity bound.

### Cluster-wide public online-character list — APPROVED READ CONTRACT

#### Authoritative backend source

Use `cluster_sessions` as the cluster-wide character identity lease source, joined to `players` for public character fields.

Mandatory predicates for every positive online row:

- `cluster_sessions.status = 'ONLINE'`;
- `cluster_sessions.expires_at > read_time_epoch_ms`;
- `players.id = cluster_sessions.player_id`;
- `players.deletion = 0`.

Physical row presence or `status = 'ONLINE'` alone is insufficient. The current Canary `multichannel::listOnlinePlayers()` and `findOnlineChannelForPlayer()` helpers filter only on `status = 'ONLINE'`; those helper semantics are **not approved** as the Platform public freshness contract.

#### Public output allowlist

A sanitized PublicGameData adapter may expose only approved public character fields plus durable channel identity, for example:

- `players.id`;
- `players.name`;
- `players.level`;
- `players.vocation`;
- `cluster_sessions.channel_id` as durable `channels.id`;
- optionally public channel metadata from the already-approved `channels` allowlist.

It must not expose:

- `cluster_sessions.account_id`;
- `instance_id`;
- `session_id`;
- `fencing_token`;
- raw lease timestamps unless a separate public product requirement explicitly approves them;
- any account credential/security/private field.

#### Freshness and stale-data semantics

- A positive row is valid only until its stored `expires_at` boundary.
- With the current default configuration, the lease window is 30 seconds. Therefore, after the last successful DB acquire/heartbeat write, a stale positive caused by an ungraceful process loss is bounded by the remaining lease lifetime, plus any wall-clock skew between writer and reader.
- Routine DB heartbeat writes are best-effort. If Redis remains healthy but DB heartbeat persistence fails for longer than the lease window, expiry filtering intentionally fails closed and may temporarily omit a genuinely online player. This false-negative behavior is preferred to presenting an expired session as online.
- No application cache is approved to extend a positive row beyond the underlying lease-expiry boundary. Any future cache must define an explicit bounded max age that does not mask expiry or dependency failure beyond the contracted freshness window.
- Maximum production wall-clock skew between Canary processes, Platform and database hosts is not proven. Any production freshness SLA must include that skew unless deployment enforces a common bounded time source.

#### Dependency-failure semantics

- Canary database read failure is **dependency unavailable/error**, not evidence that zero characters are online.
- The adapter must not convert a failed query into an empty online list.
- The adapter must not silently fall back to `players_online`, process-local `ProtocolStatus`, or an unbounded stale cache.
- Failure of the separate Redis `ChannelRuntimeRegistry` or SQL `channel_runtime_status` diagnostic path does not invalidate a still-fresh `cluster_sessions` identity row for this contract; runtime availability may be reported separately if/when that feature is integrated.

#### Current Canary helper caveat — PROVEN

`multichannel::listOnlinePlayers()` returns an empty vector when its DB `storeQuery()` returns no result, so its current API cannot distinguish a DB read failure from a genuine empty result. Platform must issue its own read through the dedicated Canary query boundary and preserve dependency failure explicitly.

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
| Cluster-wide online character list | Approved read-only through sanitized `cluster_sessions` + `players` adapter with mandatory status/expiry/deletion filters and explicit dependency failure | Not approved | PROVEN/DERIVED |
| Account internal identity/profile fields | Approved only inside authorized Accounts/Identity integration | Not approved | PROVEN |
| Password/session credential fields | Security-restricted | Not approved | PROVEN + auth contract pending |
| Account/character bans and namelocks | Authorized internal reads only | Not approved | PROVEN |
| Raw `cluster_sessions` data | Runtime/security internal; only the sanitized online-read projection above is approved for PublicGameData | Not approved | PROVEN/DERIVED |
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

## Database privilege boundary for Oteryn Platform

### Required deployment invariant

The dedicated Laravel `canary` connection is read-only by contract **and must also be read-only by database privilege enforcement**. A username such as `oteryn_readonly` or application code that intends to issue only `SELECT` statements is not a security boundary by itself.

Production and production-like deployments must satisfy all of the following:

- use a database credential dedicated to Oteryn Platform for the `canary` connection;
- never reuse the credential used by the Canary game server;
- never configure the `canary` connection with a root, administrator, migration-owner or other broadly privileged database account;
- enforce least privilege at the MySQL/MariaDB server with direct table-level `SELECT` only;
- do not grant `INSERT`, `UPDATE`, `DELETE`, `CREATE`, `ALTER`, `DROP`, `TRIGGER`, `GRANT OPTION`, schema-wide/global privileges or any other privilege not required by the implemented read surface;
- treat role-based or otherwise indirect/unrecognized privilege arrangements as unverified unless the deployment check can deterministically expand and validate them; the current verifier intentionally fails closed instead of assuming they are safe.

### Current implemented table allowlist

The current Oteryn Platform code on this contract revision reads exactly these Canary tables through `app/PublicGameData/CanaryGameDataRepository.php`:

- `players`;
- `guilds`;
- `guild_membership`;
- `guild_ranks`;
- `channels`;
- `cluster_sessions`.

The provisioning artifact under `database/provisioning/canary-readonly.sql.template` and the application verifier must grant/accept exactly this implemented table surface. `cluster_sessions` is now part of the current database credential allowlist because the bounded online-list adapter implements that approved read. Contract approval for any future read still does not justify pre-granting an unused table.

Whenever a change adds another Canary table to the implemented read surface, that same reviewed change must update:

1. the evidence-backed read contract;
2. the database provisioning grants;
3. the privilege-verifier allowlist and regression tests.

Deployment must update the database grants before or atomically with enabling code that requires the new table. Stale excess grants must be removed rather than left in place for convenience.

### Non-destructive verification

Deployments must run `php artisan canary:verify-db-privileges` (or an equivalent deterministic deployment check) against the actual `canary` connection before treating the boundary as enforced. The current command inspects `SHOW GRANTS FOR CURRENT_USER`, never logs raw grant statements, never logs a password, and never attempts a destructive write test. It fails when required table `SELECT` grants are missing or when it sees write/DDL/admin/global/schema-wide/extra-table grants, `GRANT OPTION`, role grants or another grant shape it cannot prove safe.

The exact production MySQL/MariaDB server product/version remains `UNKNOWN` from repository evidence. The committed provisioning and verifier therefore target direct grant syntax common to MySQL/MariaDB (Canary is built with `libmariadb`, while Laravel uses the MySQL driver) and deliberately report unsupported privilege models as unverified rather than claiming enforcement.

## Remaining UNKNOWN items

- exact deployed production database schema/version and whether it contains `tournament_coins` or `coins_tournament`;
- whether a separate cleanup path eventually physically deletes every expired orphaned `cluster_sessions` row; online-read correctness must not depend on it;
- maximum production wall-clock skew relevant to lease-expiry SLA;
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
- Never use `players_online` as cluster-wide online authority; its proven multi-process writer lifecycle destroys cross-channel completeness.
- Never treat `cluster_sessions.status = 'ONLINE'` without `expires_at > read_time` as a fresh online identity result.
- Never convert Canary DB read failure into an empty online list.
- Do not use protocol world-list index as persistent channel identity; use `channels.id`.
- Do not implement shared account/character/guild/ban/session/coin writes until a specific operation section is approved.
- Schema drift or unresolved column conflicts must fail visibly rather than silently falling back to guessed names.

## Next contract dependency

No additional discovery contract is required before a bounded PublicGameData implementation task adds the cluster-wide online-character list defined above. That implementation must preserve the mandatory status/expiry/deletion filters, sanitized output allowlist and explicit dependency-failure semantics from this contract.

Authentication/credential migration remains governed separately by `AUTH_GAME_LOGIN_CONTRACT.md` and is not approved by this online-read contract.
