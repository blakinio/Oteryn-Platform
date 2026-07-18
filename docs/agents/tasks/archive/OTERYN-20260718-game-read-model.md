# OTERYN-20260718 Game read model

## Status

Completed on 2026-07-18. PR #4 is the delivery PR for the initial evidence-backed read-only PublicGameData implementation.

## Goal

Implement read-only highscores, character profiles, guilds and configured channel/server metadata from the proven Canary data contract without introducing Canary/shared-data mutations or an unproven cluster-wide online-character list.

## Completed deliverables

- dedicated `canary` database connection configured independently from the Platform-owned database;
- documented requirement for an externally provisioned least-privilege SELECT-only Canary database user;
- read-only query service under `app/PublicGameData/**` using Laravel query builder only;
- thin public controller and Blade routes/views for level highscores, character profiles, guild details and configured channels;
- explicit active-character filtering via `deletion = 0`;
- deterministic paginated level highscores;
- paginated guild membership/rank join without per-member N+1 queries;
- no public guild balance, account ID, email, credential/session, IP or condition exposure;
- configured channel metadata only, with explicit notice that live cluster availability is not shown;
- no `players_online` or `cluster_sessions` PublicGameData query;
- isolated integration tests that enable SQLite `PRAGMA query_only = ON` before endpoint requests;
- XSS regression coverage for escaped guild MOTD;
- no application cache added because freshness/staleness policy remains undefined.

## Acceptance criteria result

- PASS — dedicated Canary connection and least-privilege read-only credential guidance.
- PASS — active character profile allowlist and deleted-character filtering.
- PASS — deterministic paginated level highscores.
- PASS — guild read model excludes balance, filters deleted members and avoids N+1.
- PASS — configured channel metadata only; no unsupported live-status claim.
- PASS — cluster-wide online character list intentionally not implemented.
- PASS — read paths validated against a query-only isolated Canary test connection.
- PASS — private/security fields excluded from tested public output.
- PASS — CI green on implementation/checkpoint head.

## Final context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T00:04:00+02:00
head: 787b6c43237ce5bae7482488751703fa924ba2d3
branch: task/OTERYN-20260718-game-read-model
pr: 4
status: ready
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
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/archive/OTERYN-20260718-game-read-model.md
proven:
  - PublicGameData uses a dedicated Canary connection and query-builder-only read service.
  - GET /highscores, GET /characters/{name}, GET /guilds/{name} and GET /servers are implemented.
  - Highscores, character profiles and guild members filter deletion=0.
  - Highscores are ordered by level DESC then name ASC and paginated at 50 rows.
  - Guild membership uses joined player/rank data and the integration test enforces at most three Canary queries for the page.
  - Guild balance and character account_id are not rendered in tested public responses.
  - Guild MOTD is Blade-escaped and covered by a script-tag regression fixture.
  - Configured channels expose only approved metadata and do not claim authoritative live online state.
  - No global online-list route/query and no PublicGameData access to players_online or cluster_sessions was introduced.
  - Integration tests run endpoint requests after setting the isolated Canary SQLite connection to PRAGMA query_only=ON.
  - Canary schema/read-model evidence remained current because changes after the pinned data-contract SHA affected only Canary agent documentation.
  - GitHub Actions run 29660912032 passed Composer validation, lockfile installation, Pint and the full Laravel test suite on head 787b6c43237ce5bae7482488751703fa924ba2d3.
derived:
  - The implemented surfaces can operate with a database credential that lacks write privileges.
  - Current level highscores intentionally include all active characters because privileged/group visibility policy remains UNKNOWN.
  - Configured channel maintenance state is not equivalent to authoritative live cluster availability.
unknown:
  - Cluster-wide online-character identity source and freshness/failure policy.
  - Live multichannel availability transport from ChannelRuntimeRegistry to Platform.
  - Privileged/group-hidden character filtering policy for public rankings.
  - Production cache/staleness expectations.
conflicts: []
first_failure:
  marker: PINT_FORMATTING
  evidence: GitHub Actions run 29660808071 failed at Pint before tests; constructor formatting was corrected and subsequent runs passed.
rejected_hypotheses:
  - Use players_online as the global online-list source: rejected because its lifecycle/freshness is not proven.
  - Use mutation-capable Eloquent models for Canary tables: rejected to minimize accidental shared-data write surface.
  - Invent privileged character filtering from group_id: rejected because visibility semantics are not proven.
  - Add cache before defining staleness: rejected because freshness expectations are unknown.
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
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/archive/OTERYN-20260718-game-read-model.md
validation:
  - command: Canary contract freshness comparison
    result: PASS
    evidence: changes from 6df7f906ed6f8fef0aa326439a5494bd1e3d523c to 096f6445b29f69a62f03d391a2c02c4dcee74feb were docs/agents only
  - command: GitHub Actions run 29660808071
    result: FAIL
    evidence: Pint formatting only; fixed without weakening validation
  - command: GitHub Actions run 29660866636
    result: PASS
    evidence: Composer validation/install, Pint and tests passed after formatting fix
  - command: GitHub Actions run 29660912032
    result: PASS
    evidence: current implementation/checkpoint head passed Composer validation/install, Pint and tests
blockers:
  - cluster-wide online character list remains blocked until one authoritative source/freshness contract is proven
  - no blocker to merging the implemented read-only surfaces
next_action: Create OTERYN-20260718-online-status-discovery and prove the authoritative cluster-wide online-character identity source, freshness and failure semantics across players_online, cluster_sessions, channel runtime state and process-local status before implementing any online-list route.
```

## Handover

Continue only from the single `next_action` above. Do not infer that `players_online` or `cluster_sessions` is an approved public online-list source until the discovery task proves lifecycle, freshness and failure behavior.