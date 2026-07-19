# Oteryn Platform Project State

This file is the compact authoritative entry point for "where are we now?". It is not a replacement for live Git/PR/task verification.

## Last architecture-state update

2026-07-19

## Current phase

**Phase 0 — Architecture and agent bootstrap: COMPLETE**

**Phase 1 — Laravel application bootstrap: COMPLETE**

**Phase 2 — Canary and login authentication discovery for current implementation boundaries: COMPLETE**

**Phase 3 — Identity foundation: COMPLETE**

**Phase 4 — Public website and read-only game data: IN PROGRESS**

**Initial read-only PublicGameData implementation: COMPLETE**

**Cluster-wide online-status discovery: COMPLETE**

**Next work — bounded PublicGameData cluster-wide online-list implementation.**

## What exists on main after the current delivery PR is merged

- root agent governance in `AGENTS.md`;
- repository map and context routing;
- durable task/checkpoint/handoff model;
- target system architecture and module boundaries;
- security architecture and data ownership rules;
- test strategy and delivery roadmap;
- ADRs for Laravel modular monolith, separate Canary repository and deferred payments;
- Laravel 13 application foundation targeting PHP 8.5;
- Blade as the initial server-rendered UI layer;
- safe `.env.example` local defaults/placeholders with no committed application secret;
- committed Composer lockfile;
- SQLite as the default Platform local/test database connection;
- `GET /health` application availability route;
- Laravel Pint formatting checks, PHPStan/Larastan and GitHub Actions CI;
- Platform-owned Identity registration using framework password hashing;
- secure Platform web login/logout with revocable `web_session_generation` state and fail-closed current-session middleware;
- Platform password recovery and authenticated password change with web-session revocation and security audit events;
- complete opt-in Platform web MFA using maintained `pragmarx/google2fa`, including secure enrollment confirmation, pending-login second-factor challenge, replay-resistant persisted TOTP timestep, hash-only single-use recovery codes inside encrypted state, layered rate limits, audit, disable and session revocation;
- reusable `mfa.confirmed` middleware for future privileged routes; this gate requires confirmed MFA but does not classify administrators or grant authorization;
- Phase 3 administrator-authentication composition defined as `auth` + explicit Phase 6 RBAC/policy authorization + mandatory `mfa.confirmed`;
- current email-verification policy explicitly not required for Phase 3 and therefore not enabled;
- Phase 3 credential compatibility strategy explicitly preserves game-login behavior by keeping Platform Identity credentials separate from Canary reusable credentials and performing no shared password migration/write;
- evidence-backed Canary data contract with approved read boundaries and zero approved direct shared-data writes;
- evidence-backed current web/login-server/Canary authentication contract and staged target direction for one authoritative Identity policy plus short-lived atomic game-login authorization;
- dedicated Canary read connection configured independently from the Platform-owned database;
- read-only public level highscores at `GET /highscores`;
- read-only active character profiles at `GET /characters/{name}`;
- read-only public guild details and membership at `GET /guilds/{name}`;
- read-only configured channel metadata at `GET /servers`;
- integration tests that exercise PublicGameData routes after placing an isolated Canary SQLite connection in `query_only` mode;
- an approved cluster-wide online-character read contract using sanitized `cluster_sessions` identity joined to public player fields, with mandatory `ONLINE` status, unexpired lease and active-character filters;
- explicit online-read stale/failure semantics: expired lease rows are excluded, Canary DB failure is not converted to an empty list, and raw session/security fields remain non-public;
- proven rejection of shared `players_online` as a multichannel authority because every process periodically rewrites/prunes it from only its local player set.

## Phase 3 Identity summary

Phase 3 is complete for the Platform-owned web Identity boundary.

Implemented properties:

- registration, login/logout, password recovery/change, revocable sessions, layered rate limiting and account security event recording are available on main;
- MFA enrollment does not become confirmed until a maintained-provider TOTP is verified;
- confirmed-MFA identities remain unauthenticated after password verification until a valid TOTP or recovery code completes the pending login challenge;
- TOTP replay prevention persists the last accepted timestep and verifies/updates under database row locking;
- recovery codes are returned in plaintext only at creation, framework-hashed before encrypted persistence and consumed atomically once;
- MFA reset/disable and credential changes revoke Platform web sessions according to explicit policy;
- future privileged routes can require the tested `mfa.confirmed` middleware in addition to authentication and separate authorization;
- no `is_admin` or equivalent authorization shortcut was introduced.

Phase 3 credential strategy:

- Platform Identity passwords are Platform-owned and framework-hashed;
- Phase 3 does not read or write the shared Canary `accounts.password` field and therefore preserves current game-login compatibility by non-interference;
- shared credential migration remains blocked until every supported login entry point is integrated with the authoritative Identity contract, direct/fallback reusable-password paths are fenced, revocation is implemented across game credentials and exact deployed versions are proven;
- Platform password reset/change and MFA currently govern Platform web authentication only and must not be described as globally revoking or gating native Canary/external login-server authentication.

Email verification policy:

- current Phase 3 product policy does not require email verification, so no verification gate is enabled;
- a future Platform-web requirement may be added as a dedicated policy task;
- a future requirement intended to gate game login cannot be claimed globally until every supported Canary/login-server path consults the same authoritative policy.

Administrator authentication policy:

- administrator identity classification and permissions belong to Phase 6 Admin/RBAC;
- every future privileged/Admin route must require normal authentication, explicit deny-by-default RBAC/policy authorization and confirmed MFA through `mfa.confirmed`;
- the MFA middleware is deliberately not an authorization mechanism and does not determine administrator status.

## PublicGameData implementation summary

The initial PublicGameData implementation is intentionally narrow and read-only.

Proven implementation properties:

- shared Canary tables are accessed through a dedicated query service using Laravel query builder rather than mutation-capable shared Eloquent models;
- deployment documentation requires a separate least-privilege SELECT-only Canary database credential;
- character/highscore/guild member reads filter `players.deletion = 0`;
- highscores select only public fields, use deterministic `level DESC, name ASC` ordering and paginate 50 rows;
- public character profiles expose only `id`, `name`, `level` and `vocation` to the query layer, while the view renders only name/level/vocation;
- guild details exclude `guilds.balance` and membership data is joined in one paginated read path without per-member N+1 queries;
- Blade output escapes guild content by default and XSS regression coverage exists for MOTD rendering;
- server/channel page exposes configured enabled channel metadata and maintenance state only;
- no cluster-wide online-character route is implemented yet;
- no application caching is used yet.

The approved next PublicGameData online-list implementation contract is:

- backend identity source: `cluster_sessions` joined to `players`;
- mandatory positive filters: `cluster_sessions.status = 'ONLINE'`, `cluster_sessions.expires_at > read_time_epoch_ms`, `players.deletion = 0`;
- output: explicit public player allowlist plus durable `channels.id`, never raw account/session/lease identifiers;
- dependency failure: Canary DB read failure must remain an explicit unavailable/error result, never a synthetic empty list;
- `players_online`, process-local `ProtocolStatus`, and unbounded stale cache are forbidden fallbacks;
- SQL `channel_runtime_status` is not a required hard identity gate because it is a best-effort diagnostic mirror; fresh channel availability remains a separate integration concern.

Known PublicGameData unknowns:

- transport from Canary multichannel runtime state to Oteryn Platform for independent fresh per-channel availability/count;
- privileged/group-hidden character filtering policy for public rankings;
- production cache/staleness expectations outside the newly bounded online-lease freshness contract;
- maximum production wall-clock skew relevant to the exact online freshness SLA.

## Canary data-contract summary

`docs/contracts/CANARY_DATA_CONTRACT.md` is partially proven and is the baseline for read-only integration design.

Key proven points:

- accounts and characters are global across channels;
- `players.account_id` owns the account-to-character relationship;
- persistent channel identity is `channels.id`;
- modern protocol world-list index is transient and must not be persisted as `channels.id`;
- guild ownership/membership/rank tables and constraints are documented;
- account/IP bans, namelocks and session structures are documented;
- `account_sessions` and `cluster_sessions` are separate concepts;
- current `players_online` lifecycle is incompatible with cluster-wide completeness and is rejected as a multichannel authority;
- `cluster_sessions` acquire/heartbeat/expiry behavior supports a bounded sanitized online-character read contract when status and expiry are both filtered;
- Redis `ChannelRuntimeRegistry` is the fail-closed per-channel liveness fast path, while SQL `channel_runtime_status` is a best-effort diagnostic mirror;
- process-local `ProtocolStatus` is not a cluster-wide character-identity source;
- there are no approved direct Oteryn Platform writes to shared Canary data.

Known data-contract blockers/unknowns:

- `schema.sql` defines `accounts.tournament_coins`, while Canary repository code expects `accounts.coins_tournament` for tournament coin access;
- actual deployed database shape for that field is not proven;
- whether another cleanup path eventually physically deletes every expired orphaned `cluster_sessions` row is not proven, but online-read correctness no longer depends on physical deletion because expiry filtering is mandatory;
- maximum production wall-clock skew relevant to the lease-expiry SLA is not proven;
- product initialization rules for Platform-driven character creation are not proven.

## Authentication contract summary

`docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md` maps current behavior and separately documents a recommended target contract.

Global credential migration remains blocked because:

- native Canary and external login-server authentication paths can coexist;
- current native Canary verifies custom Argon2 then SHA-1 fallback;
- current upstream external login-server verifies SHA-1 only;
- standard Laravel Argon2id stored-string compatibility with Canary is not proven;
- password/reset revocation across all game-login credential classes is not proven;
- current game-login paths do not globally enforce Platform MFA or email verification;
- failed native Canary password authentication logs the stored credential hash value and requires a separate Canary security fix.

These blockers do not block the completed Platform-owned Phase 3 web Identity boundary because that implementation does not mutate shared Canary credentials or claim game-login enforcement.

## What does not exist yet

Unless source is added after this state update, the following are **not implemented**:

- full production public website/CMS;
- shared password/hash migration to an authoritative cross-component credential model;
- global game-login enforcement of Platform MFA/email verification;
- account management beyond Identity-owned credential/security operations;
- character creation/delete/rename;
- cluster-wide online character list route/UI despite its read contract now being approved;
- live multichannel availability integration;
- Admin/RBAC/audit UI and administrator identity classification;
- Canary/shared-data write paths;
- login-server integration code owned by Oteryn Platform;
- production Cloudflare/deployment configuration;
- payments/shop.

Agents must verify repository source before relying on this list because later tasks may supersede it.

## Next planned task

`OTERYN-20260719-online-list-read-model`

Objective:

- implement the cluster-wide online-character read model through the existing dedicated Canary query boundary;
- select only the approved public player fields plus durable `channel_id`/approved channel metadata;
- enforce `cluster_sessions.status = 'ONLINE'`, `cluster_sessions.expires_at > read_time_epoch_ms` and `players.deletion = 0`;
- preserve Canary DB dependency failure explicitly rather than converting it to an empty list;
- add integration tests for fresh, expired, deleted-character and dependency-failure cases under the read-only/query-only Canary test boundary;
- do not use `players_online`, process-local `ProtocolStatus` or SQL `channel_runtime_status` as replacement identity authorities;
- do not add shared Canary writes.

## High-priority unknowns and blockers

- exact deployed production authentication topology and login-server image digest;
- shared password hash compatibility/migration rollout;
- password reset/change and global game-credential revocation behavior;
- MFA/email-verification enforcement across every game-login path;
- tournament-coin schema/code conflict;
- maximum production wall-clock skew for exact lease freshness SLA;
- final production hosting/network topology;
- production mail/cache/queue providers.

## Architecture summary

```text
Cloudflare / Edge
       |
       v
Oteryn Platform (Laravel 13 / PHP 8.5 modular monolith)
       |
       +--> Platform-owned Identity + application data
       |
       +--> explicit read/auth contracts
                    |
                    v
                  Canary
```

Initial UI direction: Laravel Blade.

Payments: deferred.

MyAAC: not a target dependency for the long-term platform; it may remain only as an external reference during migration/discovery, not as the architectural foundation.

## How to update this file

Update only when the project-level phase, implemented capabilities, major unknowns or next recommended work materially changes.

Do not use this file as a per-PR scratchpad. Detailed progress belongs in active task records and live PRs.
