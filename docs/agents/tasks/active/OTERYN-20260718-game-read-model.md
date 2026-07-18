# OTERYN-20260718 Game read model

## Goal

Implement evidence-backed, read-only PublicGameData surfaces for highscores, character profiles, guilds and configured channel/server status using the proven Canary data contract. Do not implement Canary mutations or a cluster-wide online character list whose source/freshness contract remains unknown.

## Acceptance criteria

- [x] Add a dedicated Canary read connection configured independently from the Platform-owned database and document least-privilege read-only credentials.
- [x] Implement public character profiles using only approved public fields and exclude `deletion != 0`.
- [x] Implement level highscores using explicit selected columns, deterministic ordering and pagination.
- [x] Implement public guild details and paginated membership/rank reads without N+1 queries and without exposing guild balance.
- [x] Implement configured channel/server-status metadata only from approved `channels` fields; do not claim live online state where freshness transport is unproven.
- [x] Do not implement cluster-wide online character identity/list while its source/freshness contract remains `UNKNOWN`.
- [x] Keep controllers thin and use dedicated read/query services.
- [x] Add integration/feature tests using an isolated Canary test connection and prove request paths work when that connection is read-only.
- [x] Do not expose account IDs, email, password/session data, IPs, conditions or other private/security fields.
- [x] Use pagination and avoid N+1 query patterns.
- [x] Do not add caching until explicit staleness expectations are defined.
- [x] Run CI and record exact results.
- [ ] Complete final diff/CI validation, archive task and hand over with exactly one concrete `next_action`.

## Ownership

```yaml
owned_paths:
  - app/PublicGameData/**
  - app/Http/Controllers/PublicGameData/**
  - config/database.php
  - .env.example
  - routes/web.php
  - resources/views/game/**
  - resources/views/home.blade.php
  - tests/Concerns/**
  - tests/Feature/PublicGameData/**
  - tests/Unit/PublicGameData/**
  - README.md
  - docs/agents/tasks/active/OTERYN-20260718-game-read-model.md
  - docs/agents/tasks/archive/OTERYN-20260718-game-read-model.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
modules:
  - PublicGameData
  - Integration
dependencies:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/architecture/DATA_OWNERSHIP.md
  - Laravel database/query builder
blockers:
  - cluster-wide online character identity remains UNKNOWN and is explicitly excluded
cross_repository_tasks:
  - blakinio/canary remained read-only; no external repository writes
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T23:59:00+02:00
head: c622f826b5007cca0dcdb1880a816f2e7933eb90
branch: task/OTERYN-20260718-game-read-model
pr: 4
status: validating
context_routes:
  - public-game-data
  - database
  - testing
owned_paths:
  - app/PublicGameData/**
  - app/Http/Controllers/PublicGameData/**
  - config/database.php
  - .env.example
  - routes/web.php
  - resources/views/game/**
  - resources/views/home.blade.php
  - tests/Feature/PublicGameData/**
  - README.md
  - docs/agents/**
proven:
  - Oteryn Platform main was 10006f96a954939c50bdd8c9486ba91e937fdf6a at task start after auth-discovery merge.
  - No active task and no overlapping open game-read-model PR existed at startup.
  - CANARY_DATA_CONTRACT.md approves read-only character profile fields, guild fields and channel registry metadata while blocking shared writes.
  - Canary advanced from data-contract SHA 6df7f906ed6f8fef0aa326439a5494bd1e3d523c to current 096f6445b29f69a62f03d391a2c02c4dcee74feb only through docs/agents changes, so schema/read-model source evidence remains current.
  - PR #4 exists from task/OTERYN-20260718-game-read-model to main.
  - Dedicated database connection `canary` uses separate CANARY_DB_* configuration and documentation requires an externally provisioned SELECT-only credential.
  - Public read repository uses Laravel query builder only and contains no mutation methods or Eloquent models for shared Canary tables.
  - GET /highscores selects only id/name/level/vocation, filters deletion=0, orders deterministically by level DESC then name ASC and paginates 50 rows.
  - GET /characters/{name} selects only id/name/level/vocation and returns 404 for deletion != 0.
  - GET /guilds/{name} excludes guild balance, selects approved guild fields, joins membership/player/rank in one paginated query and filters deleted members.
  - GET /servers exposes enabled configured channel ID/name/PvP/max-player/maintenance metadata and explicitly states that live cluster availability is not provided.
  - No cluster-wide online-list route/query was added and neither players_online nor cluster_sessions is queried by PublicGameData.
  - PublicGameData integration tests use an isolated SQLite Canary connection, seed fixtures, enable PRAGMA query_only before requests and all pass in CI.
  - Guild integration test asserts at most three Canary queries for the page, covering one guild query plus paginator count/data queries and preventing per-member N+1 behavior.
  - Guild MOTD rendering is Blade-escaped and tested against a script-tag fixture.
  - Deleted characters are excluded from highscores, character profiles and guild membership views in tests.
  - Guild balance and character account_id are not exposed by tested public views.
  - No application caching was added because freshness/staleness requirements remain undefined.
  - Final changed-file inventory before handover contains only declared owned paths.
  - GitHub Actions run 29660866636 passed Composer validation, lockfile install, Pint and the full Laravel test suite on head c622f826b5007cca0dcdb1880a816f2e7933eb90.
derived:
  - The implemented PublicGameData layer is structurally read-only and can run using a database credential that lacks write privileges.
  - Level highscores currently include all active characters because privileged/group visibility policy remains unknown; no group-ID semantics were invented.
  - Configured channel maintenance state is safe to show, but it must not be described as authoritative live online/offline status.
unknown:
  - Privileged/group-hidden player filtering policy for highscores.
  - Production cache/freshness requirements.
  - Live multichannel availability transport from ChannelRuntimeRegistry to Platform.
  - Cluster-wide online character identity source/freshness contract.
conflicts: []
first_failure:
  marker: PINT_FORMATTING
  evidence: GitHub Actions run 29660808071 failed at Check formatting before tests; empty constructor format was corrected and subsequent run passed.
rejected_hypotheses:
  - Use players_online as global online list source: rejected because its writer lifecycle/freshness is not proven and the contract explicitly blocks that assumption.
  - Add mutation-capable Eloquent models for shared Canary tables: rejected because this task is read-only and query services reduce accidental write surface.
  - Filter privileged characters by assumed group_id values: rejected because group visibility semantics are not proven by the data contract.
  - Cache read models immediately: rejected because acceptable staleness semantics are not yet defined.
changed_paths:
  - .env.example
  - README.md
  - app/Http/Controllers/PublicGameData/PublicGameDataController.php
  - app/PublicGameData/CanaryGameDataRepository.php
  - config/database.php
  - resources/views/game/**
  - resources/views/home.blade.php
  - routes/web.php
  - tests/Feature/PublicGameData/PublicGameDataTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260718-game-read-model.md
validation:
  - command: startup repository/task/PR verification
    result: PASS
    evidence: Platform main 10006f96a954939c50bdd8c9486ba91e937fdf6a; no overlapping active task/PR
  - command: Canary contract freshness comparison
    result: PASS
    evidence: compare 6df7f906ed6f8fef0aa326439a5494bd1e3d523c...096f6445b29f69a62f03d391a2c02c4dcee74feb changed only docs/agents files
  - command: GitHub Actions run 29660808071
    result: FAIL
    evidence: Pint formatting failed before tests; no Composer/lockfile failure
  - command: GitHub Actions run 29660866636
    result: PASS
    evidence: composer validate, composer install, composer format:check and composer test all passed
blockers:
  - cluster-wide online character list remains intentionally excluded until source/freshness contract is proven
  - no blocker to merging the implemented read-only surfaces
next_action: Inspect the final PR #4 diff and current-head CI, archive this task, then create a bounded online-status discovery task to prove one cluster-wide online-character source/freshness contract before adding an online-list route.
```

## Notes

Highscore privileged/group visibility remains a product-policy `UNKNOWN`; the current level ranking intentionally includes all active characters using only approved public fields and does not invent group semantics.