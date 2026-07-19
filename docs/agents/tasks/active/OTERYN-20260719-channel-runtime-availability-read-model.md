# OTERYN-20260719 Channel runtime availability read model

## Goal

Implement the approved Phase 4 per-channel runtime availability/count boundary using a dedicated read-only Laravel Redis connection to deterministic Canary runtime keys, while preserving Redis-TTL freshness, complete-snapshot fail-closed semantics, static channel metadata fallback and the existing independent online-character identity contract.

## Acceptance criteria

- [x] Archive the merged `OTERYN-20260719-channel-runtime-availability-discovery` task record under `docs/agents/tasks/archive/` with the exact historical blob unchanged.
- [x] Add a dedicated `canary_runtime` Redis connection/configuration with safe `.env.example` placeholders, no committed secret and no Redis key prefix that would alter Canary runtime keys.
- [x] Use Laravel 13's supported named Redis connection boundary with PhpRedis; do not add or repurpose Platform cache/session Redis state.
- [x] Read only deterministic `cluster:channel:{id}:runtime` keys for enabled channel IDs obtained from the existing Canary `channels` query; never use `KEYS`/`SCAN`.
- [x] Read only the public-needed runtime fields (`channel_id`, `status`, `players_online`) plus Redis TTL for freshness validation; never expose operational instance/node/build/map/data metadata.
- [x] Treat a missing/expired key while Redis is healthy as per-channel runtime `unknown/unavailable`, without synthetic `OFFLINE` or zero players.
- [x] Treat malformed runtime fields or any Redis transport/protocol failure while reading one logical snapshot as whole-snapshot runtime unavailable; do not present partial runtime data as complete.
- [x] Keep configured/static channel metadata available when the runtime dependency is unavailable, but omit/mark runtime status and counts unavailable.
- [x] Preserve explicit runtime states and derive `full` only for `ONLINE` where `max_players > 0` and `players_online >= max_players`.
- [x] Do not read or grant SQL `channel_runtime_status`, do not use process-local `ProtocolStatus`, do not add runtime caching, and do not gate the existing `/online` identity list on runtime availability.
- [x] Add focused unit/feature coverage for healthy ONLINE/full/state reads, missing/expired keys, malformed data, transport failure snapshot discard, no synthetic zero/offline, and public output allowlist.
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
  - tests/Feature/PublicGameData/ServerRuntimeAvailabilityTest.php
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
updated_at: 2026-07-19T20:20:00+02:00
head: 032f8bb5b842972befaa7f0a248cc41b0a68161a
branch: task/OTERYN-20260719-channel-runtime-availability-read-model
pr: 22
status: validating
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
  - tests/Feature/PublicGameData/ServerRuntimeAvailabilityTest.php
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
  - PR #22 is the only current task PR and targets main from the dedicated runtime read-model branch.
  - The merged runtime-discovery task archive is an exact-content rename with zero additions and zero deletions.
  - The implementation uses the dedicated named canary_runtime connection with an empty Redis prefix and safe environment placeholders; no Composer dependency or committed secret was added.
  - Runtime reads are limited to deterministic cluster:channel:{id}:runtime keys, HMGET of channel_id/status/players_online and PTTL; no KEYS/SCAN, Redis writes, session-lease access or SQL channel_runtime_status read was added.
  - Positive TTL plus validated fields produces a runtime value; missing/expired keys produce per-channel unknown; malformed data or any thrown Redis failure discards the complete runtime snapshot while static channel metadata remains renderable.
  - Runtime output exposes only explicit state and players_online; operational instance/node/build/map/data fields are not read or rendered; full is derived only for ONLINE with a positive configured max and count at or above max.
  - Focused unit/feature tests cover deterministic-key reads, missing/expired keys, malformed data, channel mismatch, state allowlist, whole-snapshot discard, explicit runtime states/counts/full, and no synthetic OFFLINE/zero on failure.
  - CI #341 and CI #343 both stopped at composer format:check; static analysis and tests were skipped, so no test-suite failure has yet been proven on this task.
  - Agent Governance #262 and #264 passed on the corresponding pre-documentation heads.
derived:
  - The first proven delivery failure is formatting-only; static-analysis and test outcomes remain unknown until Pint passes.
  - The smallest safe repair is to match the repository Pint preset rather than weaken or bypass formatting checks.
unknown:
  - production Redis host/ACL username/password remain deployment inputs and must not be committed
  - exact static-analysis and full-test outcome on the repaired implementation head remains unknown until CI reaches those steps
conflicts: []
first_failure:
  marker: PINT_FORMAT_CHECK
  evidence: GitHub Actions CI #341 and #343 failed at Check formatting; Run static analysis and Run tests were skipped
rejected_hypotheses:
  - The early CI failure was a failing test suite: rejected because CI #341/#343 job steps show formatting failed and both static analysis and tests were skipped.
  - Add Predis solely for this adapter: rejected because Laravel 13 supports PhpRedis directly and the bounded implementation does not need a new Composer dependency.
  - Reuse a generic Platform cache Redis connection: rejected because the contract requires a dedicated Canary runtime credential/connection and Platform currently has no Redis cache/session boundary to reuse.
  - Read SQL channel_runtime_status instead: rejected by the merged discovery contract because the mirror can overstate authoritative availability after Redis failure.
changed_paths:
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
  - tests/Feature/PublicGameData/ServerRuntimeAvailabilityTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/archive/OTERYN-20260719-channel-runtime-availability-discovery.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-read-model.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: post-PR21 main/open-PR state, exact archived discovery blob and approved runtime contract were verified before implementation
  - command: Laravel 13 Redis boundary verification
    result: PASS
    evidence: official Laravel 13 Redis documentation confirms named connections and PhpRedis support; no new Composer dependency is required for the selected boundary
  - command: GitHub Actions CI #341
    result: FAIL
    evidence: Check formatting failed; static analysis and tests were skipped
  - command: GitHub Actions CI #343
    result: FAIL
    evidence: Check formatting failed; static analysis and tests were skipped
  - command: Agent Governance #262 and #264
    result: PASS
    evidence: checkpoint governance passed on the corresponding pre-documentation heads
  - command: local Composer/Pint/PHPStan/tests
    result: NOT_RUN
    evidence: current execution environment cannot resolve github.com and has no usable local Oteryn-Platform checkout; GitHub Actions remains the executable validation source
blockers:
  - none
next_action: Validate the checkpoint head through GitHub Actions, inspect the first failing step if any, and repair only the proven root cause before synchronizing Phase 4 documentation.
```

## Notes

This task implements only the approved read-only runtime surface. It does not authorize Redis writes, Redis key scanning, session-lease access, SQL runtime-mirror reads, Canary DB grant expansion, runtime caching, Canary repository writes or changes to the independent online-character identity contract.
