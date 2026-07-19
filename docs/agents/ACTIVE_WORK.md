# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260719-phase3-identity-closure` — final Phase 3 Identity foundation closure; branch `task/OTERYN-20260719-phase3-identity-closure`; draft PR #17; checkpoint status `ready`. Exact validation head `f007a06c15a7e6198a5fa93945aed1e5fa5bdb6f` passed CI #256 and Agent Governance #177 after the temporary contract-sync workflow was removed. Scope archives merged T3.4c unchanged, adds and tests the reusable `mfa.confirmed` privileged-route gate, formalizes Platform credential isolation and current email-verification policy, defines administrator authentication composition without introducing Admin/RBAC semantics, synchronizes the auth contract current state, and marks Phase 3 complete in ROADMAP/MODULE_CATALOG/PROJECT_STATE. The final ready-checkpoint head must pass CI/Governance again before merge.

## Recommended next task

After PR #17 merges and Phase 3 is fully closed, continue Phase 4 with `OTERYN-20260719-online-list-read-model`, the already approved bounded PublicGameData cluster-wide online-character read model.

## Other queued work

- `OTERYN-20260719-online-list-read-model` remains the next bounded Phase 4 task. It must read `cluster_sessions` plus public player fields through the dedicated query-only Canary boundary, enforce `status = 'ONLINE'`, unexpired `expires_at` and `players.deletion = 0`, preserve explicit dependency-failure semantics, and never use `players_online` or add shared writes.
- Authoritative cross-component credential/game-login migration remains a later separately coordinated programme under `AUTH_GAME_LOGIN_CONTRACT.md`; Phase 3 completion does not authorize shared Canary credential writes.
- Admin/RBAC identity classification and permissions remain Phase 6. Future privileged routes must combine explicit authorization with the Phase 3 `mfa.confirmed` gate rather than an `is_admin` shortcut.

## Recently completed

- `OTERYN-20260719-platform-web-mfa` — Phase 3 T3.4c complete Platform web MFA lifecycle merged through PR #16 as `b1947b2e918b689bac636942ce244492227158bb`; final documentation head `02bb4c37d63c132fca63d4b274af8215210b2fe0` passed CI #242 and Agent Governance #163; task record archived unchanged on the Phase 3 closure branch.
- `OTERYN-20260719-mfa-totp-provider-resolution` — Phase 3 T3.4b real Composer resolution for maintained `pragmarx/google2fa:^9.0` merged through PR #15 as `d4cc4189cbc99f47b4cec69ce198bd5ded43d719`; task record archived under `docs/agents/tasks/archive/`.
- `OTERYN-20260719-mfa-state-foundation` — Phase 3 T3.4a Platform-only encrypted MFA state and internal reset/session-revocation foundation merged through PR #14 as `6109e48d28c305f24a8db56389a433b4a4876750`; task record archived under `docs/agents/tasks/archive/`.
- `OTERYN-20260719-password-recovery-credentials` — Phase 3 T3.3 secure Platform password recovery/change merged through PR #13 as `e1ec8fddd4aedbd847558f223be35212ea11c85f`; task record archived under `docs/agents/tasks/archive/`.
- `OTERYN-20260719-web-login-sessions` — Phase 3 T3.2 secure Platform web login/logout and revocable web sessions merged through PR #12 as `74a72d4acc2f0228a147e3ce71a1542f43e97906`; archived task record under `docs/agents/tasks/archive/`.
- `OTERYN-20260719-identity-core-registration` — Phase 3 T3.1 Platform-owned Identity core and registration merged through PR #11 as `6f48cf97288963c25b0ca97563865f5b3514de3b`; archived task record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-online-status-discovery` — current Canary online/status sources inspected read-only; `players_online` rejected as a multichannel authority; sanitized `cluster_sessions` status/expiry/deletion read contract approved with explicit stale/failure semantics; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-game-read-model` — read-only level highscores, active character profiles, guild details/membership and configured channel metadata implemented with a dedicated Canary connection and query-only integration tests; global online list remains intentionally unimplemented; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-auth-discovery` — current web/login-server/Canary credential and game-session paths mapped; shared credential migration remains blocked; target authoritative Identity contract documented; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-canary-schema-discovery` — evidence-backed Canary data contract pinned to the then-current Canary SHA; read boundaries proven, direct shared writes remain blocked, one tournament-coin schema/code conflict recorded; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-laravel-bootstrap` — Laravel 13 / PHP 8.5 application foundation, Blade, health route, lockfile, tests and CI; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-platform-architecture-bootstrap` — architecture/governance baseline completed; archive record under `docs/agents/tasks/archive/`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
