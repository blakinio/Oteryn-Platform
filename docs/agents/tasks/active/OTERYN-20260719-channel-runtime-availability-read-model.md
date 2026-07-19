# OTERYN-20260719 Channel runtime availability read model

## Goal

Implement the approved Phase 4 per-channel runtime availability/count boundary using a dedicated read-only Laravel Redis connection to deterministic Canary runtime keys, while preserving Redis-TTL freshness, complete-snapshot fail-closed semantics, static channel metadata fallback and the existing independent online-character identity contract.

## Acceptance criteria

- [x] Archive the merged `OTERYN-20260719-channel-runtime-availability-discovery` task record under `docs/agents/tasks/archive/` with the exact historical blob unchanged.
- [ ] Add a dedicated `canary_runtime` Redis connection/configuration with safe `.env.example` placeholders, no committed secret and no Redis key prefix that would alter Canary runtime keys.
- [ ] Use Laravel 13's supported named Redis connection boundary with PhpRedis; do not add or repurpose Platform cache/session Redis state.
- [ ] Read only deterministic `cluster:channel:{id}:runtime` keys for enabled channel IDs obtained from the existing Canary `channels` query; never use `KEYS`/`SCAN`.
- [ ] Read only the public-needed runtime fields (`channel_id`, `status`, `players_online`) plus Redis TTL for freshness validation; never expose operational instance/node/build/map/data metadata.
- [ ] Treat a missing/expired key while Redis is healthy as per-channel runtime `unknown/unavailable`, without synthetic `OFFLINE` or zero players.
- [ ] Treat malformed runtime fields or any Redis transport/protocol failure while reading one logical snapshot as whole-snapshot runtime unavailable; do not present partial runtime data as complete.
- [ ] Keep configured/static channel metadata available when the runtime dependency is unavailable, but omit/mark runtime status and counts unavailable.
- [ ] Preserve explicit runtime states and derive `full` only for `ONLINE` where `max_players > 0` and `players_online >= max_players`.
- [ ] Do not read or grant SQL `channel_runtime_status`, do not use process-local `ProtocolStatus`, do not add runtime caching, and do not gate the existing `/online` identity list on runtime availability.
- [ ] Add focused unit/feature coverage for healthy ONLINE/full/state reads, missing/expired keys, malformed data, transport failure snapshot discard, no synthetic zero/offline, and public output allowlist.
- [ ] Synchronize contract/state/module/roadmap documentation with the implemented Redis runtime boundary without adding Canary/shared-data writes.
- [ ] Run repository CI and Agent Governance on the delivery-validation head; require a fresh exact-head pass after the final ready checkpoint before merge.

## Ownership

```yaml
owned_paths:
  - .env.example
  - config/database.php
  - app/CanaryIntegration/CanaryRuntimeRedisReader.php
  - app/PublicGameData/CanaryRuntimeStatus.php
  - app/PublicGameData/CanaryRuntimeSnapshot.php
  - app/PublicGameData/CanaryChannelRuntimeService.php
  - app/Http/Controllers/PublicGameData/PublicGameDataController.php
  - resources/views/game/servers.blade.php
  - tests/Unit/CanaryIntegration/CanaryRuntimeRedisReaderTest.php
  - tests/Unit/PublicGameData/CanaryChannelRuntimeServiceTest.php
  - tests/Feature/PublicGameData/PublicGameDataTest.php
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/ROADMAP.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-read-model.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-discovery.md
  - docs/agents/tasks/archive/OTERYN-20260719-channel-runtime-availability-discovery.md
modules:
  - PublicGameData
  - Canary integration
  - public web
  - configuration
  - testing
  - agent-governance
dependencies:
  - OTERYN-20260719-channel-runtime-availability-discovery
  - docs/contracts/CANARY_DATA_CONTRACT.md
blockers:
  - none
cross_repository_tasks:
  - blakinio/canary remains read-only evidence only; no writes are authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T19:10:00+02:00
head: 77fe4d2979098e670ff8a809fb62866bd9494892
branch: task/OTERYN-20260719-channel-runtime-availability-read-model
pr: none
status: investigating
context_routes:
  - agent-governance
  - public-game-data
  - canary-integration
  - security
  - testing
owned_paths:
  - .env.example
  - config/database.php
  - app/CanaryIntegration/CanaryRuntimeRedisReader.php
  - app/PublicGameData/CanaryRuntimeStatus.php
  - app/PublicGameData/CanaryRuntimeSnapshot.php
  - app/PublicGameData/CanaryChannelRuntimeService.php
  - app/Http/Controllers/PublicGameData/PublicGameDataController.php
  - resources/views/game/servers.blade.php
  - tests/Unit/CanaryIntegration/CanaryRuntimeRedisReaderTest.php
  - tests/Unit/PublicGameData/CanaryChannelRuntimeServiceTest.php
  - tests/Feature/PublicGameData/PublicGameDataTest.php
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/ROADMAP.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-read-model.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-discovery.md
  - docs/agents/tasks/archive/OTERYN-20260719-channel-runtime-availability-discovery.md
proven:
  - Main was verified at 1e3a1aaf0f595c60283545a95393da71d8924d51, the squash merge of PR #21, before starting this task.
  - Live GitHub PR search returned no open pull requests after PR #21 merged.
  - The merged runtime-discovery task archive uses blob 1cd05293ab4b9a40272e6034cb1634be000961d4, exactly matching the former active task blob.
  - The approved contract requires deterministic cluster:channel:{id}:runtime reads, positive Redis TTL freshness, complete-snapshot fail-closed semantics, no SQL/ProtocolStatus fallback and no application cache.
  - Enabled/durable channel IDs and max_players already come from the implemented query-only Canary channels read.
  - Current Platform has no existing Redis cache/session dependency that needs to be reused or protected from this integration.
  - Current Laravel 13 documentation supports named Redis connections and Redis::connection(name); PhpRedis is the default/recommended client and does not require adding a Composer package.
  - A dedicated named canary_runtime connection with an empty Redis prefix can therefore preserve Canary's exact runtime key names without changing composer.lock.
derived:
  - The smallest implementation is a transport reader isolated behind a PublicGameData snapshot service, allowing runtime failures to discard the runtime snapshot while still rendering the independently queried static channel metadata.
unknown:
  - production Redis host/ACL username/password remain deployment inputs and must not be committed
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Add Predis solely for this adapter: rejected because Laravel 13 supports PhpRedis directly and the bounded implementation does not need a new Composer dependency.
  - Reuse a generic Platform cache Redis connection: rejected because the contract requires a dedicated Canary runtime credential/connection and Platform currently has no Redis cache/session boundary to reuse.
  - Read SQL channel_runtime_status instead: rejected by the merged discovery contract because the mirror can overstate authoritative availability after Redis failure.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260719-channel-runtime-availability-discovery.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-discovery.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-read-model.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: post-PR21 main/open-PR state, exact archived discovery blob and approved runtime contract were verified before implementation
  - command: Laravel 13 Redis boundary verification
    result: PASS
    evidence: current official Laravel 13 Redis documentation confirms named connections and PhpRedis support; no new Composer dependency is required for the selected boundary
  - command: implementation validation
    result: NOT_RUN
    evidence: implementation has not started yet
blockers:
  - none
next_action: Update ACTIVE_WORK and open the draft PR, then implement the dedicated Redis reader/snapshot service, server projection and focused fail-closed tests.
```

## Notes

This task implements only the approved read-only runtime surface. It does not authorize Redis writes, Redis key scanning, session-lease access, SQL runtime-mirror reads, Canary DB grant expansion, runtime caching, Canary repository writes or changes to the independent online-character identity contract.
