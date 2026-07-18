# OTERYN-20260718 Game read model

## Goal

Implement evidence-backed, read-only PublicGameData surfaces for highscores, character profiles, guilds and configured channel/server status using the proven Canary data contract. Do not implement Canary mutations or a cluster-wide online character list whose source/freshness contract remains unknown.

## Acceptance criteria

- [ ] Add a dedicated Canary read connection configured independently from the Platform-owned database and document least-privilege read-only credentials.
- [ ] Implement public character profiles using only approved public fields and exclude `deletion != 0`.
- [ ] Implement level highscores using explicit selected columns, deterministic ordering and pagination.
- [ ] Implement public guild details and paginated membership/rank reads without N+1 queries and without exposing guild balance.
- [ ] Implement configured channel/server-status metadata only from approved `channels` fields; do not claim live online state where freshness transport is unproven.
- [ ] Do not implement cluster-wide online character identity/list while its source/freshness contract remains `UNKNOWN`.
- [ ] Keep controllers thin and use dedicated read/query services.
- [ ] Add integration/feature tests using an isolated Canary test connection and prove request paths work when that connection is read-only.
- [ ] Do not expose account IDs, email, password/session data, IPs, conditions or other private/security fields.
- [ ] Use pagination and avoid N+1 query patterns.
- [ ] Do not add caching until explicit staleness expectations are defined.
- [ ] Run CI and record exact results.
- [ ] Complete checkpoint and handover with exactly one concrete `next_action`.

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
  - blakinio/canary remains read-only; no external repository writes
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T23:55:00+02:00
head: 10006f96a954939c50bdd8c9486ba91e937fdf6a
branch: task/OTERYN-20260718-game-read-model
pr: none
status: implementing
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
  - tests/Concerns/**
  - tests/Feature/PublicGameData/**
  - tests/Unit/PublicGameData/**
  - README.md
  - docs/agents/**
proven:
  - Oteryn Platform main is at 10006f96a954939c50bdd8c9486ba91e937fdf6a after auth-discovery merge.
  - No active task and no overlapping open game-read-model PR existed at startup.
  - CANARY_DATA_CONTRACT.md is partially proven and explicitly approves read-only character profile fields, guild fields and channel registry metadata while blocking shared writes.
  - Canary advanced from data-contract SHA 6df7f906ed6f8fef0aa326439a5494bd1e3d523c to current 096f6445b29f69a62f03d391a2c02c4dcee74feb by two commits that changed only docs/agents task/handover files; schema/read-model source did not change.
  - Cluster-wide public online character identity remains UNKNOWN / NOT YET CONTRACTED.
  - Existing Platform source contains no reusable PublicGameData query/service abstraction.
derived:
  - Safe implementation can proceed for read-only active character profiles, basic level highscores, guild details/membership and configured channel metadata.
  - Live online-list implementation must remain out of scope.
  - A dedicated Canary connection plus query-only integration tests provides a stronger guard against accidentally introducing writes than mutation-capable Eloquent models.
unknown:
  - Privileged/group-hidden player filtering policy for highscores.
  - Production cache/freshness requirements.
  - Live multichannel availability transport from ChannelRuntimeRegistry to Platform.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Use players_online as global online list source: rejected because its writer lifecycle/freshness is not proven and the contract explicitly blocks that assumption.
  - Add mutation-capable Eloquent models for shared Canary tables: rejected because this task is read-only and query services reduce accidental write surface.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260718-game-read-model.md
validation:
  - command: startup repository/task/PR verification
    result: PASS
    evidence: Platform main 10006f96a954939c50bdd8c9486ba91e937fdf6a; ACTIVE_WORK has no active task; no matching open PR
  - command: Canary contract freshness comparison
    result: PASS
    evidence: compare 6df7f906ed6f8fef0aa326439a5494bd1e3d523c...096f6445b29f69a62f03d391a2c02c4dcee74feb changed only docs/agents files
blockers:
  - cluster-wide online character list excluded by unresolved source/freshness contract
next_action: Claim owned paths in ACTIVE_WORK, open a draft PR, then implement the dedicated read-only Canary connection and minimal query-service/controller/view/test slices for characters, highscores, guilds and configured channels.
```

## Notes

Highscore privileged/group visibility remains a product-policy `UNKNOWN`; the initial level ranking must not invent group semantics. It may rank all active characters using only approved public fields and document that behavior explicitly.