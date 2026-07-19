# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260719-identity-core-registration` — Phase 3 T3.1 Platform-owned Identity core and registration; branch `task/OTERYN-20260719-identity-core-registration`; PR #11; task checkpoint status `ready`. Scope is explicitly limited to Platform-owned identity persistence/registration, password hashing/policy, rate limiting and registration audit. It owns no Canary/shared write path and does not implement game-login authorization.

## Recommended next task

After PR #11 is merged, create `OTERYN-20260719-web-login-sessions` as a separate Phase 3 T3.2 task/branch after re-verifying live prerequisites and owned-path overlap. Keep game-login authorization, credential migration and global cross-session revocation outside that task unless the relevant `AUTH_GAME_LOGIN_CONTRACT.md` blockers have been separately resolved.

## Other queued work

- `OTERYN-20260719-online-list-read-model` remains a valid independent PublicGameData task to implement the approved cluster-wide online-character read model from `cluster_sessions` plus public player fields. It must enforce `status = 'ONLINE'`, unexpired `expires_at`, `players.deletion = 0`, explicit dependency-failure semantics and the existing query-only Canary boundary; do not use `players_online` or add shared writes.

## Recently completed

- `OTERYN-20260718-online-status-discovery` — current Canary online/status sources inspected read-only; `players_online` rejected as a multichannel authority; sanitized `cluster_sessions` status/expiry/deletion read contract approved with explicit stale/failure semantics; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-game-read-model` — read-only level highscores, active character profiles, guild details/membership and configured channel metadata implemented with a dedicated Canary connection and query-only integration tests; global online list remains intentionally unimplemented; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-auth-discovery` — current web/login-server/Canary credential and game-session paths mapped; credential migration remains blocked; target authoritative Identity contract documented; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-canary-schema-discovery` — evidence-backed Canary data contract pinned to the then-current Canary SHA; read boundaries proven, direct shared writes remain blocked, one tournament-coin schema/code conflict recorded; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-laravel-bootstrap` — Laravel 13 / PHP 8.5 application foundation, Blade, health route, lockfile, tests and CI; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-platform-architecture-bootstrap` — architecture/governance baseline completed; archive record under `docs/agents/tasks/archive/`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
