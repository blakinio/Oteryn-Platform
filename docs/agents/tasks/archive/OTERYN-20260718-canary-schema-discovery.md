# OTERYN-20260718 Canary schema discovery

## Status

Completed on 2026-07-18. PR #2 is the delivery PR for the evidence-backed Canary data contract.

## Goal

Establish an evidence-backed data contract between Oteryn Platform and the current `blakinio/canary` repository by inspecting Canary schema/source read-only and documenting exact proven account, player, guild, world/server, online, ban and session-related structures without implementing shared-data write paths.

## Completed deliverables

- pinned Canary evidence to `blakinio/canary` commit `6df7f906ed6f8fef0aa326439a5494bd1e3d523c`;
- proved account/player/guild/ban/session/channel schema and relevant runtime behavior from current source;
- distinguished persistent `channels.id` from transient login-protocol world-list index;
- documented account/player creation triggers, cascades and migration model;
- documented `account_sessions` versus `cluster_sessions` as separate concepts;
- documented current local-process and multichannel status sources without inventing a cluster-wide public online-list authority;
- established explicit public/internal read allowlists and a zero-approved-direct-shared-write boundary;
- recorded a `CONFLICT` between `schema.sql` column `tournament_coins` and `AccountRepositoryDB` column `coins_tournament`;
- updated `docs/contracts/CANARY_DATA_CONTRACT.md` with `PROVEN`, `DERIVED`, `UNKNOWN` and `CONFLICT` findings;
- made no writes to `blakinio/canary`.

## Acceptance criteria result

- PASS — exact Canary SHA pinned.
- PASS — account, player, guild, ban, session and channel schemas documented from source.
- PASS — character ownership/deletion gates and creation prerequisites documented; unresolved product initialization remains `UNKNOWN` rather than guessed.
- PASS — online/status sources documented; cluster-wide public online character source remains explicitly `UNKNOWN`.
- PASS — migration/trigger behavior documented.
- PASS — allowed read boundaries documented.
- PASS — no shared-data write path approved without an operation-level contract.
- PASS — no Canary repository mutation performed.
- PASS — current-head Oteryn CI passed.

## Final context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T22:55:00+02:00
head: 947093d6c94b7d3ce7e56e3425a868e71c03b470
branch: task/OTERYN-20260718-canary-schema-discovery
pr: 2
status: ready
context_routes:
  - agent-governance
  - canary-integration
  - database
  - accounts-characters
  - public-game-data
owned_paths:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/tasks/archive/OTERYN-20260718-canary-schema-discovery.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
proven:
  - Canary discovery evidence is pinned to blakinio/canary commit 6df7f906ed6f8fef0aa326439a5494bd1e3d523c.
  - The pinned schema/source proves the account/player/guild/ban/account-session/channel/cluster-session structures recorded in docs/contracts/CANARY_DATA_CONTRACT.md.
  - Characters are global rows and are repeated across selectable channels; they are not duplicated per channel.
  - channels.id is persistent channel identity, while the modern login protocol writes a transient zero-based world-list index.
  - account_sessions participates in login session lookup; cluster_sessions is Canary-owned cluster online/concurrency defense-in-depth state.
  - Public character/guild/channel reads can use explicit field allowlists documented in the contract.
  - There are zero approved direct shared-data write operations from Oteryn Platform after this discovery.
  - PR #2 changed only task/governance and CANARY_DATA_CONTRACT.md paths within owned_paths.
  - GitHub Actions run 29659897343 passed Composer validation, lockfile dependency installation, Pint and the Laravel test suite on current head.
derived:
  - The next safe security-critical work is bounded authentication/login/session discovery before implementing credential migration or global auth policy.
unknown:
  - Actual deployed production DB shape for the tournament coin column.
  - Legacy players_online writer lifecycle and the approved cluster-wide public online-list source/freshness policy.
  - Product character-creation initialization rules and any future approved shared write operations.
  - End-to-end login-server/password/token/session/revocation behavior, owned by the next auth discovery task.
conflicts:
  - Pinned schema.sql defines accounts.tournament_coins while pinned AccountRepositoryDB expects accounts.coins_tournament.
first_failure:
  marker: SCHEMA_CODE_COLUMN_NAME_CONFLICT
  evidence: schema.sql versus src/account/account_repository_db.cpp at Canary SHA 6df7f906ed6f8fef0aa326439a5494bd1e3d523c; fresh schema seeds db_version 61.
rejected_hypotheses:
  - Players are duplicated per world/channel: rejected by schema and ProtocolLogin source.
  - Protocol worldId equals persistent channels.id: rejected by ProtocolLogin list-index encoding and ChannelContext runtime ID usage.
  - players_online is proven as the sole multichannel online authority: rejected by process-local ProtocolStatus and Redis-backed ChannelRuntimeRegistry evidence.
  - A direct SQL insert is sufficient proof of safe character creation: rejected by runtime semantic dependencies and unresolved product initialization rules.
changed_paths:
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/archive/OTERYN-20260718-canary-schema-discovery.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/PROJECT_STATE.md
validation:
  - command: pinned Canary schema/source review
    result: PASS
    evidence: read-only inspection at Canary SHA 6df7f906ed6f8fef0aa326439a5494bd1e3d523c
  - command: contract consistency review
    result: PASS
    evidence: unresolved facts preserved as UNKNOWN/CONFLICT; no speculative shared write approval
  - command: GitHub Actions CI run 29659897343
    result: PASS
    evidence: composer validate, composer install, composer format:check and composer test all passed
blockers:
  - tournament coin integration remains blocked by the schema/code column conflict
  - no blocker to merging this documentation-only discovery PR
next_action: Create OTERYN-20260718-auth-discovery and prove the current WWW/login-server/Canary credential and game-session flow in docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md without implementing credential migration.
```

## Handover

The authoritative detailed findings are in `docs/contracts/CANARY_DATA_CONTRACT.md`. Continue from the single `next_action` above; do not rediscover the schema facts marked `PROVEN` unless the pinned Canary SHA or deployed schema evidence changes.