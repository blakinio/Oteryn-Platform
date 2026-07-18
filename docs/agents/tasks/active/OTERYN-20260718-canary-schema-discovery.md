# OTERYN-20260718 Canary schema discovery

## Goal

Establish an evidence-backed data contract between Oteryn Platform and the current `blakinio/canary` repository by inspecting Canary schema/source read-only and documenting exact proven account, player, guild, world/server, online, ban and session-related structures without implementing shared-data write paths.

## Acceptance criteria

- [x] Pin discovery evidence to the exact current `blakinio/canary` commit SHA inspected.
- [x] Prove the account table/model, primary key, relevant columns, constraints and relationships where source evidence exists.
- [x] Prove the player/character table/model, account ownership relation, required creation fields/defaults, uniqueness/constraints and dependent rows where source evidence exists; retain unresolved product creation rules as `UNKNOWN`.
- [x] Prove guild tables/models and membership/leadership relationships relevant to public reads.
- [x] Prove world/server identifier representation and distinguish persistent channel ID from transient wire world-list index.
- [x] Prove available online-status sources/derivations and retain the cluster-wide public online-list authority as `UNKNOWN` where the evidence does not establish one safe source.
- [x] Prove ban/status structures and runtime expiration behavior where source evidence exists.
- [x] Prove session-related tables/fields where source evidence exists, without expanding into final authentication-flow design.
- [x] Document trigger/migration behavior and schema constraints relevant to Platform reads/writes.
- [x] Classify material conclusions as `PROVEN`, `DERIVED`, `UNKNOWN` or `CONFLICT`.
- [x] Update `docs/contracts/CANARY_DATA_CONTRACT.md` with exact source paths/SHA evidence.
- [x] Do not implement Canary write paths or modify `blakinio/canary`.
- [ ] Complete final CI/diff validation, archive the task and hand over with exactly one concrete `next_action`.

## Ownership

```yaml
owned_paths:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/tasks/active/OTERYN-20260718-canary-schema-discovery.md
  - docs/agents/tasks/archive/OTERYN-20260718-canary-schema-discovery.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
modules:
  - Integration
  - PublicGameData
  - Accounts
  - Characters
dependencies:
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
blockers:
  - tournament-coin integration is blocked by a pinned schema/code column-name conflict
  - all direct shared-data write paths remain unapproved by design
cross_repository_tasks:
  - blakinio/canary read-only schema/source discovery
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T22:48:00+02:00
head: d60a834501dfd68142903a76ac6028f9807208de
branch: task/OTERYN-20260718-canary-schema-discovery
pr: 2
status: validating
context_routes:
  - agent-governance
  - canary-integration
  - database
  - accounts-characters
  - public-game-data
owned_paths:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/tasks/active/OTERYN-20260718-canary-schema-discovery.md
  - docs/agents/tasks/archive/OTERYN-20260718-canary-schema-discovery.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
proven:
  - Phase 1 Laravel bootstrap is complete on main at 7ea6f6d8e1ee1158d7f339d92871751cab800d6a.
  - No active task was claimed at startup and no open Canary schema discovery PR was found.
  - Repository policy permits autonomous writes only to blakinio/Oteryn-Platform; blakinio/canary remained read-only throughout discovery.
  - Draft PR #2 exists from task/OTERYN-20260718-canary-schema-discovery to main.
  - Canary evidence is pinned to blakinio/canary commit 6df7f906ed6f8fef0aa326439a5494bd1e3d523c.
  - schema.sql at the pinned SHA seeds db_version 61 and defines accounts, players, bans, guilds, players_online, account_sessions, channels, channel_runtime_status and cluster_sessions.
  - accounts.id is the primary key and accounts.name is unique; email is indexed but not unique in the proven schema.
  - players.account_id references accounts.id with ON DELETE CASCADE; players.name is unique; players have no channel_id/world_id in the proven schema.
  - account player loading and player preload reject every player row with deletion != 0.
  - player preload/full-load requires resolvable account/group/vocation state and ultimately a valid town; persisted position 0,0,0 falls back to temple position.
  - account_bans/account_ban_history/ip_bans/player_namelocks structures and current runtime lookup/expiration behavior are documented in the contract.
  - guilds.ownerid references players.id and is unique; guild_membership has player_id as its primary key and references guilds and guild_ranks.
  - oncreate_accounts creates default VIP groups; oncreate_guilds creates default ranks; ondelete_players clears matching houses.owner.
  - account_sessions stores id/account_id/expires and is looked up by SHA-256 or legacy SHA-1 of the presented session key.
  - cluster_sessions enforces PRIMARY KEY(account_id) and UNIQUE(player_id); current DbClusterSessionRepository writes it on acquire/heartbeat/release alongside Redis lease handling.
  - channels.id is the persistent channel identifier used by runtime/session structures; ProtocolLogin modern multichannel worldId is a transient zero-based list index and must not be persisted as channels.id.
  - ProtocolStatus derives count/list/single-player online status from the current process g_game() memory.
  - ChannelRuntimeRegistry provides Redis-backed fail-closed fresh per-channel availability/count; the SQL channel_runtime_status table is a diagnostic mirror according to current repository architecture.
  - DatabaseManager applies numbered Lua migrations newer than server_config.db_version in ascending order and advances db_version; migrations 59-61 implement current multichannel schema additions.
  - docs/contracts/CANARY_DATA_CONTRACT.md now contains the pinned evidence inventory, read allowlists, write boundary, PROVEN/DERIVED/UNKNOWN/CONFLICT findings and remaining unknowns.
derived:
  - Character/account identity is cluster-global; selectable channels are routing/runtime context, not duplicate character identity.
  - Public character/guild/channel read models can be built from explicit field allowlists, but cluster-wide online character identity needs a separate source/freshness decision.
  - There are zero approved direct shared-data write operations from Oteryn Platform after this task; operation-level contracts are required before future writes.
  - account_sessions and cluster_sessions are distinct concepts and must never be treated as interchangeable.
unknown:
  - Exact deployed production DB schema/version and whether its tournament coin column matches schema.sql or repository code.
  - Exact writer lifecycle of the legacy players_online MEMORY table.
  - Approved cluster-wide online character identity source and freshness/failure policy.
  - Product rules and dependent initialization required for Platform-driven character creation.
  - Exact non-zero players.deletion value semantics beyond the proven fact that non-zero is excluded from login/list flows.
  - Which shared write operations, if any, Oteryn Platform should eventually own.
conflicts:
  - Fresh pinned schema.sql defines accounts.tournament_coins while pinned AccountRepositoryDB maps CoinType::Tournament to accounts.coins_tournament; schema seeds db_version 61 so a fresh DB will not automatically run a later migration to reconcile the name.
first_failure:
  marker: SCHEMA_CODE_COLUMN_NAME_CONFLICT
  evidence: pinned schema.sql uses tournament_coins; pinned src/account/account_repository_db.cpp uses coins_tournament; fresh schema db_version is 61.
rejected_hypotheses:
  - Players are duplicated per world/channel: rejected by the global players schema and ProtocolLogin behavior that repeats the same character rows across channels.
  - ProtocolLogin worldId is the durable channels.id: rejected because current modern multichannel login writes the filtered-channel vector index as worldId while runtime uses ChannelContext channel ID.
  - players_online can be declared the sole multichannel online authority: rejected because current ProtocolStatus uses process-local g_game(), multichannel availability uses ChannelRuntimeRegistry, and players_online writer lifecycle was not proven.
  - cluster_sessions is only schema-ready and not written by the engine: rejected by pinned DbClusterSessionRepository acquire/heartbeat/release implementation.
  - A successful SQL INSERT is sufficient proof of safe character creation: rejected because player preload/full-load and product initialization rules introduce additional semantic requirements not captured by NOT NULL/default constraints alone.
changed_paths:
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260718-canary-schema-discovery.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
validation:
  - command: startup repository/task/PR verification
    result: PASS
    evidence: main HEAD 7ea6f6d8e1ee1158d7f339d92871751cab800d6a; no overlapping active task or open PR at startup
  - command: pinned Canary source/schema inspection
    result: PASS
    evidence: blakinio/canary SHA 6df7f906ed6f8fef0aa326439a5494bd1e3d523c; schema, migrations and current account/player/guild/ban/session/channel/runtime code inspected read-only
  - command: contract consistency review
    result: PASS
    evidence: every unresolved material integration item is retained as UNKNOWN or CONFLICT; no Canary/shared write path is approved
blockers:
  - tournament coin integration is blocked until the column conflict is resolved against deployed schema and/or Canary source
  - no blocker to merging this documentation-only discovery task
next_action: Verify PR #2 current-head CI and final changed-file scope, then archive the completed discovery task and hand off exactly one next task for AUTH_GAME_LOGIN_CONTRACT discovery.
```

## Notes

All Canary evidence remained read-only. No finding relies on MyAAC or generic TFS schema assumptions.