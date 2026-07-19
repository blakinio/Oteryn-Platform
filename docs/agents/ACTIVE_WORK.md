# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260719-mfa-totp-provider-resolution` — Phase 3 T3.4b dependency-resolution-only follow-up for Platform MFA; branch `task/OTERYN-20260719-mfa-totp-provider-resolution`; draft PR #15; task checkpoint status `ready`. Real Composer resolution added `pragmarx/google2fa:^9.0`, locking `pragmarx/google2fa` v9.0.0 and `paragonie/constant_time_encoding` v3.1.3. The ephemeral resolver workflow has been removed. Generated dependency metadata and the documentation checkpoint have passed normal CI and Agent Governance; final exact-head merge-gate revalidation remains. No public MFA routes, challenge/enforcement logic, Admin/RBAC semantics or Canary/login-server changes are in scope.

## Recommended next task

After T3.4b is merged, create a separate Platform web MFA integration task for maintained-provider-backed enrollment confirmation and login challenge/enforcement within the existing custom auth stack. Recovery-code consumption, replay resistance, rate limiting, audit and session semantics must be designed explicitly. Platform web MFA must not be described as a global game-login gate while alternate Canary/login-server paths remain unresolved.

## Other queued work

- `OTERYN-20260719-online-list-read-model` remains a valid independent PublicGameData task to implement the approved cluster-wide online-character read model from `cluster_sessions` plus public player fields. It must enforce `status = 'ONLINE'`, unexpired `expires_at`, `players.deletion = 0`, explicit dependency-failure semantics and the existing query-only Canary boundary; do not use `players_online` or add shared writes.

## Recently completed

- `OTERYN-20260719-mfa-state-foundation` — Phase 3 T3.4a Platform-only encrypted MFA state and internal reset/session-revocation foundation merged through PR #14 as `6109e48d28c305f24a8db56389a433b4a4876750`; task record archived under `docs/agents/tasks/archive/` on the T3.4b branch.
- `OTERYN-20260719-password-recovery-credentials` — Phase 3 T3.3 secure Platform password recovery/change merged through PR #13 as `e1ec8fddd4aedbd847558f223be35212ea11c85f`; task record archived under `docs/agents/tasks/archive/`.
- `OTERYN-20260719-web-login-sessions` — Phase 3 T3.2 secure Platform web login/logout and revocable web sessions merged through PR #12 as `74a72d4acc2f0228a147e3ce71a1542f43e97906`; archived task record under `docs/agents/tasks/archive/`.
- `OTERYN-20260719-identity-core-registration` — Phase 3 T3.1 Platform-owned Identity core and registration merged through PR #11 as `6f48cf97288963c25b0ca97563865f5b3514de3b`; archived task record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-online-status-discovery` — current Canary online/status sources inspected read-only; `players_online` rejected as a multichannel authority; sanitized `cluster_sessions` status/expiry/deletion read contract approved with explicit stale/failure semantics; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-game-read-model` — read-only level highscores, active character profiles, guild details/membership and configured channel metadata implemented with a dedicated Canary connection and query-only integration tests; global online list remains intentionally unimplemented; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-auth-discovery` — current web/login-server/Canary credential and game-session paths mapped; credential migration remains blocked; target authoritative Identity contract documented; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-canary-schema-discovery` — evidence-backed Canary data contract pinned to the then-current Canary SHA; read boundaries proven, direct shared writes remain blocked, one tournament-coin schema/code conflict recorded; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-laravel-bootstrap` — Laravel 13 / PHP 8.5 application foundation, Blade, health route, lockfile, tests and CI; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-platform-architecture-bootstrap` — architecture/governance baseline completed; archive record under `docs/agents/tasks/archive/`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.