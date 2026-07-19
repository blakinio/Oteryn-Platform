# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260719-public-news-read-model` — bounded Phase 4 Platform-owned CMS public-news read model; branch `task/OTERYN-20260719-public-news-read-model`; draft PR #20; checkpoint status `validating`. Scope is limited to Platform-owned `news_posts` persistence, published-only public list/detail reads, deterministic pagination/order, escaped plain-text rendering and focused tests. No authoring, Admin/RBAC, uploads, rich HTML, Canary access or shared-data writes are authorized. Stable exact-head CI and Agent Governance validation is required before readiness.

## Recommended next task

Complete and merge the current public-news read slice first. After merge, re-evaluate remaining Phase 4 work against live roadmap/project state, especially the separate unresolved fresh multichannel runtime availability concern.

## Other queued work

- Authoritative cross-component credential/game-login migration remains a later separately coordinated programme under `AUTH_GAME_LOGIN_CONTRACT.md`; Phase 3 completion does not authorize shared Canary credential writes.
- Admin/RBAC identity classification and permissions remain Phase 6. Future privileged routes must combine explicit authorization with the Phase 3 `mfa.confirmed` gate rather than an `is_admin` shortcut.
- Fresh per-channel runtime availability/count transport remains separate from the online-character identity read model.

## Recently completed

- `OTERYN-20260719-public-site-shell-and-search` — shared public Blade shell/navigation and exact-name character search merged through PR #19 as `fc50b92208de67a4630d994a8ad3923f2e1fa07e`; task record archived unchanged by exact blob identity when the current public-news task started.
- `OTERYN-20260719-online-list-read-model` — cluster-wide read-only online-character list merged through PR #18 as `c66a8c1b352c757d1beb15f1ec838eb2d3ce17d5`; task record archived unchanged by exact blob identity when the public-site task started.
- `OTERYN-20260719-phase3-identity-closure` — Phase 3 Identity foundation closure merged through PR #17 as `6aeaf961aafbfa8e991d1b11bd9f1e9fe578d5a5`; task record archived unchanged when the next real Phase 4 task started.
- `OTERYN-20260719-platform-web-mfa` — Phase 3 T3.4c complete Platform web MFA lifecycle merged through PR #16 as `b1947b2e918b689bac636942ce244492227158bb`; archived task record under `docs/agents/tasks/archive/`.
- `OTERYN-20260719-mfa-totp-provider-resolution` — Phase 3 T3.4b real Composer resolution for maintained `pragmarx/google2fa:^9.0` merged through PR #15 as `d4cc4189cbc99f47b4cec69ce198bd5ded43d719`; task record archived under `docs/agents/tasks/archive/`.
- `OTERYN-20260719-mfa-state-foundation` — Phase 3 T3.4a Platform-only encrypted MFA state and internal reset/session-revocation foundation merged through PR #14 as `6109e48d28c305f24a8db56389a433b4a4876750`; task record archived under `docs/agents/tasks/archive/`.
- `OTERYN-20260719-password-recovery-credentials` — Phase 3 T3.3 secure Platform password recovery/change merged through PR #13 as `e1ec8fddd4aedbd847558f223be35212ea11c85f`; task record archived under `docs/agents/tasks/archive/`.
- `OTERYN-20260719-web-login-sessions` — Phase 3 T3.2 secure Platform web login/logout and revocable web sessions merged through PR #12 as `74a72d4acc2f0228a147e3ce71a1542f43e97906`; archived task record under `docs/agents/tasks/archive/`.
- `OTERYN-20260719-identity-core-registration` — Phase 3 T3.1 Platform-owned Identity core and registration merged through PR #11 as `6f48cf97288963c25b0ca97563865f5b3514de3b`; archived task record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-online-status-discovery` — `players_online` rejected as a multichannel authority; sanitized `cluster_sessions` status/expiry/deletion read contract approved with explicit stale/failure semantics; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-game-read-model` — read-only level highscores, active character profiles, guild details/membership and configured channel metadata implemented with a dedicated Canary connection and query-only integration tests; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-auth-discovery` — current web/login-server/Canary credential and game-session paths mapped; shared credential migration remains blocked; target authoritative Identity contract documented; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-canary-schema-discovery` — evidence-backed Canary data contract pinned to the then-current Canary SHA; read boundaries proven, direct shared writes remain blocked, one tournament-coin schema/code conflict recorded; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-laravel-bootstrap` — Laravel 13 / PHP 8.5 application foundation, Blade, health route, lockfile, tests and CI; archive record under `docs/agents/tasks/archive/`.
- `OTERYN-20260718-platform-architecture-bootstrap` — architecture/governance baseline completed; archive record under `docs/agents/tasks/archive/`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
