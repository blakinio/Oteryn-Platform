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

**Cluster-wide online-character read model: COMPLETE**

**Public site shell and exact-name character search: COMPLETE**

**Public news read model: COMPLETE**

**Channel runtime availability transport discovery: CONTRACT APPROVED IN CURRENT DELIVERY / VALIDATING**

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
- database-enforced least-privilege Canary credential verification with direct table-level `SELECT` allowlisting for the implemented read surface, including `cluster_sessions` for the online-character read model;
- shared Blade public shell used by the homepage, news and implemented public game-data surfaces, with navigation for Home, News, Online, Highscores and Servers;
- homepage exact-name character search at `GET /characters?name=...`, validating the submitted name and redirecting to the existing character profile route rather than introducing a second query path;
- Platform-owned `news_posts` persistence with unique slug, title, plain-text body and nullable publication timestamp;
- published-only public news list at `GET /news` and detail at `GET /news/{slug}`, excluding drafts and future-scheduled posts;
- deterministic public news ordering by `published_at DESC, id DESC` with bounded pagination of 10 posts;
- escaped plain-text public news rendering with no rich-HTML or media-upload boundary introduced;
- read-only public level highscores at `GET /highscores`;
- read-only active character profiles at `GET /characters/{name}`;
- read-only public guild details and membership at `GET /guilds/{name}`;
- read-only configured channel metadata at `GET /servers`;
- cluster-wide read-only online-character list at `GET /online` using fresh `cluster_sessions` identity joined to public player and approved channel fields;
- integration tests that exercise PublicGameData routes after placing an isolated Canary SQLite connection in `query_only` mode;
- online-list integration coverage for fresh `ONLINE`, expired, non-`ONLINE`, deleted-player, dependency-failure and public-field allowlist cases;
- mandatory online filters `cluster_sessions.status = 'ONLINE'`, `cluster_sessions.expires_at > read_time_epoch_ms` and `players.deletion = 0`;
- explicit online-read stale/failure semantics: expired lease rows are excluded, Canary DB failure is returned as dependency unavailable rather than an empty list, and raw session/security fields remain non-public;
- proven rejection of shared `players_online` as a multichannel authority because every process periodically rewrites/prunes it from only its local player set;
- an evidence-backed approved next integration contract for per-channel runtime availability/count using dedicated read-only Redis access to deterministic `cluster:channel:{id}:runtime` keys, with Redis-TTL freshness, complete-snapshot failure semantics and no SQL/`ProtocolStatus` fallback; runtime integration code is not yet implemented.

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

The current PublicGameData implementation is intentionally narrow and read-only.

Proven implementation properties:

- shared Canary tables are accessed through a dedicated query service using Laravel query builder rather than mutation-capable shared Eloquent models;
- deployment documentation requires a separate least-privilege SELECT-only Canary database credential, and the verifier/provisioning allowlist includes `cluster_sessions` because the online-list adapter reads it;
- the shared Blade public shell exposes Home, News, Online, Highscores and Servers navigation and the homepage uses that same layout rather than a separate document;
- exact-name homepage character search validates the `name` query value and redirects to the existing `game.characters.show` route, so no duplicate character query implementation or extra Canary privilege is introduced;
- character/highscore/guild member reads filter `players.deletion = 0`;
- highscores select only public fields, use deterministic `level DESC, name ASC` ordering and paginate 50 rows;
- public character profiles expose only `id`, `name`, `level` and `vocation` to the query layer, while the view renders only name/level/vocation;
- guild details exclude `guilds.balance` and membership data is joined in one paginated read path without per-member N+1 queries;
- Blade output escapes guild content by default and XSS regression coverage exists for MOTD rendering;
- server/channel page currently exposes configured enabled channel metadata and maintenance state only;
- `GET /online` reads `cluster_sessions` joined to `players` and `channels`, selects only public player fields plus durable `channel_id` and channel name, and applies mandatory online/expiry/deletion filters;
- Canary DB query failure for `/online` becomes an explicit HTTP 503 dependency-unavailable result rather than a synthetic empty list;
- no application caching is used yet.

The implemented PublicGameData online-list contract is:

- backend identity source: `cluster_sessions` joined to `players`;
- mandatory positive filters: `cluster_sessions.status = 'ONLINE'`, `cluster_sessions.expires_at > read_time_epoch_ms`, `players.deletion = 0`;
- output: explicit public player allowlist plus durable `channels.id` and approved channel name, never raw account/session/lease identifiers;
- dependency failure: Canary DB read failure remains an explicit unavailable/error result, never a synthetic empty list;
- `players_online`, process-local `ProtocolStatus`, and unbounded stale cache are forbidden fallbacks;
- runtime availability remains independent from online-character identity.

The approved, not-yet-implemented per-channel runtime-availability contract is:

- expected channel IDs come from the existing enabled `channels` database read;
- runtime source is the deterministic Redis hash `cluster:channel:{channels.id}:runtime` through a dedicated read-only Platform connection/credential;
- Redis key existence plus positive TTL and valid runtime fields/state define external freshness; missing/expired keys while Redis is healthy mean runtime availability is unknown/unavailable, not synthetic `OFFLINE` or zero players;
- any Redis transport/protocol failure while reading one logical configured-channel snapshot invalidates all runtime fields for that snapshot; static configured metadata may still render independently;
- public runtime output is limited to durable `channel_id`, explicit runtime `status` and non-negative `players_online`; operational instance/node/build/map/data metadata remains private;
- SQL `channel_runtime_status` is a best-effort diagnostic mirror and is forbidden as an authoritative fallback because Canary queues its write even after Redis runtime publish/refresh failure;
- current process-local `ProtocolStatus` is also forbidden as a cluster runtime fallback;
- no application cache is approved initially;
- the current Platform codebase still lacks the dedicated Canary-runtime Redis dependency/config boundary required to implement this contract.

Known PublicGameData unknowns:

- privileged/group-hidden character filtering policy for public rankings;
- production cache/staleness expectations outside the bounded online-lease and approved Redis-runtime freshness contracts;
- maximum production wall-clock skew relevant to the exact `cluster_sessions` online-character freshness SLA;
- production provisioning details for the dedicated read-only Canary runtime Redis credential/endpoint, to be supplied outside Git while implementing the approved adapter.

## CMS implementation summary

The current CMS implementation is intentionally public-read-only and Platform-owned.

Implemented properties:

- `news_posts` belongs to the Platform database and is managed by an authoritative Laravel migration; it does not introduce a Canary/shared-data contract;
- public list/detail queries use the dedicated `PublicNewsQuery` boundary and expose only rows where `published_at` is non-null and not later than the read time;
- drafts and future-scheduled posts remain non-public, including direct detail lookup by slug;
- the list orders by `published_at DESC` and then `id DESC`, and paginates 10 posts per page;
- slugs are unique at the database level;
- title and body are rendered through escaped Blade output, with body treated as plain text and whitespace preserved;
- focused tests cover required schema, unique slug, publication-state visibility, deterministic ordering/pagination, detail 404 behavior and XSS escaping.

Not included in the current CMS boundary:

- authoring or editing routes/UI;
- page management;
- Admin/RBAC or privileged CMS permissions;
- privileged CMS audit actions;
- arbitrary rich HTML or a sanitizer integration;
- media/file uploads.

Those mutation and privileged-management capabilities remain Phase 6 work and must not be inferred from the public read model.

## Canary data-contract summary

`docs/contracts/CANARY_DATA_CONTRACT.md` is partially proven and is the baseline for read-only integration design.

Key proven points:

- the contract is revalidated against Canary `main` at `d4f8bb3aa3a6ca31b54f324797078360da28f8f8`;
- accounts and characters are global across channels;
- `players.account_id` owns the account-to-character relationship;
- persistent channel identity is `channels.id`;
- modern protocol world-list index is transient and must not be persisted as `channels.id`;
- guild ownership/membership/rank tables and constraints are documented;
- account/IP bans, namelocks and session structures are documented;
- `account_sessions` and `cluster_sessions` are separate concepts;
- current `players_online` lifecycle is incompatible with cluster-wide completeness and is rejected as a multichannel authority;
- `cluster_sessions` acquire/heartbeat/expiry behavior supports the implemented bounded sanitized online-character read model when status and expiry are both filtered;
- Redis `ChannelRuntimeRegistry` is the fail-closed per-channel liveness fast path and the dedicated direct read-only Redis runtime-key boundary is approved for the next Platform adapter;
- SQL `channel_runtime_status` is a best-effort asynchronous diagnostic mirror and is not approved as an authoritative public runtime fallback;
- process-local `ProtocolStatus` is not a cluster-wide runtime or character-identity source;
- there are no approved direct Oteryn Platform writes to shared Canary data.

Known data-contract blockers/unknowns:

- `schema.sql` defines `accounts.tournament_coins`, while Canary repository code expects `accounts.coins_tournament` for tournament coin access;
- actual deployed database shape for that field is not proven;
- whether another cleanup path eventually physically deletes every expired orphaned `cluster_sessions` row is not proven, but online-read correctness no longer depends on physical deletion because expiry filtering is mandatory;
- maximum production wall-clock skew relevant to the `cluster_sessions` lease-expiry SLA is not proven;
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

- CMS news/page authoring and management, privileged CMS administration, rich-HTML publishing or media uploads;
- shared password/hash migration to an authoritative cross-component credential model;
- global game-login enforcement of Platform MFA/email verification;
- account management beyond Identity-owned credential/security operations;
- character creation/delete/rename;
- live multichannel availability integration through the now-approved dedicated Redis runtime adapter;
- Admin/RBAC/audit UI and administrator identity classification;
- Canary/shared-data write paths;
- login-server integration code owned by Oteryn Platform;
- production Cloudflare/deployment configuration;
- payments/shop.

Agents must verify repository source before relying on this list because later tasks may supersede it.

## Current active task

`OTERYN-20260719-channel-runtime-availability-discovery`

Objective:

- verify the current Canary runtime availability source/transport against live `main`;
- define explicit freshness, least-privilege and dependency-failure semantics for Platform consumption;
- reject SQL `channel_runtime_status` and process-local `ProtocolStatus` as authoritative fallbacks;
- approve the smallest safe next Platform integration boundary without adding runtime integration code in this discovery task.

Approved successor bounded task after this discovery is merged:

`OTERYN-20260719-channel-runtime-availability-read-model`

That task must implement the dedicated read-only Redis runtime adapter and server/channel public projection under the approved contract, including a dedicated dependency/config boundary and fail-closed tests. It must not broaden the Canary database table allowlist to `channel_runtime_status`.

## High-priority unknowns and blockers

- exact deployed production authentication topology and login-server image digest;
- shared password hash compatibility/migration rollout;
- password reset/change and global game-credential revocation behavior;
- MFA/email-verification enforcement across every game-login path;
- tournament-coin schema/code conflict;
- maximum production wall-clock skew for exact `cluster_sessions` lease freshness SLA;
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
