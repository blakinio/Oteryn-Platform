# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

| Task | Status | Scope | Record |
|---|---|---|---|
| OTERYN-20260718-auth-discovery | investigating | Read-only proof of current WWW/login-server/Canary credential verification, password compatibility and game-session/token flow; no credential migration or auth implementation | `docs/agents/tasks/active/OTERYN-20260718-auth-discovery.md` |

## Recently completed

- `OTERYN-20260718-canary-schema-discovery` — evidence-backed Canary data contract pinned to Canary SHA `6df7f906ed6f8fef0aa326439a5494bd1e3d523c`; read boundaries proven, direct shared writes remain blocked, one tournament-coin schema/code conflict recorded; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-laravel-bootstrap` — Laravel 13 / PHP 8.5 application foundation, Blade, health route, lockfile, tests and CI; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-platform-architecture-bootstrap` — architecture/governance baseline completed; archive record under `docs/agents/tasks/archive/`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.