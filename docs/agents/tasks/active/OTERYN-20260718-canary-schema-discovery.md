# OTERYN-20260718 Canary schema discovery

## Goal

Establish an evidence-backed data contract between Oteryn Platform and the current `blakinio/canary` repository by inspecting Canary schema/source read-only and documenting exact proven account, player, guild, world/server, online, ban and session-related structures without implementing shared-data write paths.

## Acceptance criteria

- [x] Pin discovery evidence to the exact current `blakinio/canary` commit SHA inspected.
- [x] Prove the account table/model, primary key, relevant columns, constraints and relationships where source evidence exists.
- [ ] Prove the player/character table/model, account ownership relation, required creation fields/defaults, uniqueness/constraints and dependent rows where source evidence exists.
- [x] Prove guild tables/models and membership/leadership relationships relevant to public reads.
- [x] Prove world/server identifier representation or retain it as `UNKNOWN` where no authoritative schema/source evidence exists.
- [ ] Prove authoritative online-status storage/derivation where source evidence exists.
- [x] Prove ban/status structures and references where source evidence exists.
- [x] Prove session-related tables/fields where source evidence exists, without expanding into final authentication-flow design.
- [ ] Document trigger/migration behavior and schema constraints relevant to Platform reads/writes.
- [ ] Classify each material conclusion as `PROVEN`, `DERIVED`, `UNKNOWN` or `CONFLICT`.
- [ ] Update `docs/contracts/CANARY_DATA_CONTRACT.md` with exact source paths/SHA evidence.
- [x] Do not implement Canary write paths or modify `blakinio/canary`.
- [ ] Complete checkpoint, validation and handover with exactly one concrete `next_action`.

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
  - none
cross_repository_tasks:
  - blakinio/canary read-only schema/source discovery
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T22:33:00+02:00
head: 574243823df14adb73d71ac467617781f7148f6f
branch: task/OTERYN-20260718-canary-schema-discovery
pr: 2
status: investigating
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
  - Repository policy permits autonomous writes only to blakinio/Oteryn-Platform; blakinio/canary is read-only for this task.
  - Draft PR #2 exists from task/OTERYN-20260718-canary-schema-discovery to main.
  - Canary discovery evidence is pinned to blakinio/canary commit 6df7f906ed6f8fef0aa326439a5494bd1e3d523c.
  - schema.sql at the pinned Canary SHA declares db_version 61 and defines accounts, players, bans, guilds, players_online, account_sessions, channels, channel_runtime_status and cluster_sessions.
  - accounts.id is the primary key; accounts.name is unique; players.account_id references accounts.id with ON DELETE CASCADE; players.name is unique.
  - account_bans and account_ban_history reference accounts; account_bans, account_ban_history and ip_bans use player IDs for banned_by references.
  - guilds.ownerid references players.id and is unique; guild_membership has player_id as its primary key and references guilds and guild_ranks.
  - oncreate_accounts creates three default VIP groups; oncreate_guilds creates leader, vice-leader and member ranks; ondelete_players clears matching house ownership.
  - players_online is an in-memory table keyed by player_id with a foreign key to players.
  - account_sessions stores id, account_id and expires; AccountRepositoryDB loads sessions by SHA-256 or legacy SHA-1 of the presented session key and joins them to accounts.
  - channels is the persistent channel/world registry; players remain global and do not carry a channel/world column in the proven schema.
  - cluster_sessions enforces PRIMARY KEY(account_id) and UNIQUE(player_id), and current DbClusterSessionRepository code writes it on acquire/heartbeat/release alongside the Redis-backed lease layer.
  - DatabaseManager applies numbered Lua migrations newer than server_config.db_version in numeric order and then advances db_version; migrations 59-61 add multichannel tables, per-channel house identity and channel-switch audit consumption tracking.
derived:
  - Character identity and account ownership are cluster-global; channel/world selection is represented by channels and session/runtime data rather than duplicate player rows.
  - Direct Platform writes to shared account/player/guild/session state are not yet approved; discovered Canary writers, triggers, cascades and runtime/session side effects require an explicit operation-level write contract first.
  - account_sessions and cluster_sessions serve different purposes: account_sessions participates in login/session credential lookup, while cluster_sessions is an online concurrency/lease defense-in-depth record.
unknown:
  - Exact runtime lifecycle that inserts/removes players_online rows and whether it is authoritative in multichannel mode versus cluster session/runtime state.
  - Minimal semantically safe field set and dependent-row initialization required for Platform-driven character creation.
  - Whether any existing migration resolves the tournament coin column naming conflict.
  - Which shared fields, if any, Oteryn Platform may safely mutate without a separately approved operation-level contract.
conflicts:
  - schema.sql defines accounts.tournament_coins, while AccountRepositoryDB maps CoinType::Tournament to accounts.coins_tournament; migration evidence has not yet resolved the discrepancy.
first_failure:
  marker: SCHEMA_CODE_COLUMN_NAME_CONFLICT
  evidence: pinned schema.sql uses tournament_coins; pinned src/account/account_repository_db.cpp uses coins_tournament.
rejected_hypotheses:
  - Players are duplicated per world/channel: rejected because the pinned schema has one global players table without channel_id and multichannel architecture/source represents channels separately.
  - cluster_sessions is only schema-ready and not written by the engine: rejected by pinned DbClusterSessionRepository acquire/heartbeat/release implementation and current multichannel architecture correction.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260718-canary-schema-discovery.md
  - docs/agents/ACTIVE_WORK.md
validation:
  - command: startup repository/task/PR verification
    result: PASS
    evidence: main HEAD 7ea6f6d8e1ee1158d7f339d92871751cab800d6a; no overlapping active task or open PR at startup
  - command: pinned Canary source/schema inspection
    result: PASS
    evidence: blakinio/canary SHA 6df7f906ed6f8fef0aa326439a5494bd1e3d523c; schema.sql plus account/session/migration/multichannel source paths inspected read-only
blockers:
  - none
next_action: Resolve the tournament coin schema/code conflict from migration/history evidence and prove players_online lifecycle plus character-creation dependencies before writing the final Canary data contract.
```

## Notes

All Canary evidence must remain read-only. Do not infer schema semantics from MyAAC or generic TFS conventions.