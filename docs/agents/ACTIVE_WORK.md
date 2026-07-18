# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

No active task is currently claimed.

## Recommended next task

Create `OTERYN-20260718-game-read-model` to implement only evidence-backed read-only highscores, character profiles, guilds and channel/server-status surfaces from `docs/contracts/CANARY_DATA_CONTRACT.md`. Keep cluster-wide online character identity `UNKNOWN` until its source/freshness contract is proven.

## Recently completed

- `OTERYN-20260718-auth-discovery` — current web/login-server/Canary credential and game-session paths mapped; credential migration remains blocked; target authoritative Identity contract documented; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-canary-schema-discovery` — evidence-backed Canary data contract pinned to Canary SHA `6df7f906ed6f8fef0aa326439a5494bd1e3d523c`; read boundaries proven, direct shared writes remain blocked, one tournament-coin schema/code conflict recorded; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-laravel-bootstrap` — Laravel 13 / PHP 8.5 application foundation, Blade, health route, lockfile, tests and CI; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-platform-architecture-bootstrap` — architecture/governance baseline completed; archive record under `docs/agents/tasks/archive/`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.