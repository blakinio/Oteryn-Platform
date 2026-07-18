# Oteryn Platform Project State

This file is the compact authoritative entry point for "where are we now?". It is not a replacement for live Git/PR/task verification.

## Last architecture-state update

2026-07-19

## Current phase

**Phase 0 — Architecture and agent bootstrap: COMPLETE**

**Phase 1 — Laravel application bootstrap: COMPLETE**

**Canary data-contract discovery: COMPLETE**

**Authentication / web-to-game session discovery: COMPLETE**

**Initial read-only PublicGameData implementation: COMPLETE**

**Next work — bounded cluster-wide online-status discovery.**

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
- Laravel Pint formatting checks and GitHub Actions CI;
- evidence-backed Canary data contract with approved read boundaries and zero approved direct shared-data writes;
- evidence-backed current web/login-server/Canary authentication contract;
- documented target direction for one authoritative Identity policy and short-lived atomic game-login authorization;
- dedicated Canary read connection configured independently from the Platform-owned database;
- read-only public level highscores at `GET /highscores`;
- read-only active character profiles at `GET /characters/{name}`;
- read-only public guild details and membership at `GET /guilds/{name}`;
- read-only configured channel metadata at `GET /servers`;
- integration tests that exercise PublicGameData routes after placing an isolated Canary SQLite connection in `query_only` mode.

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
- no `players_online`, `cluster_sessions` or cluster-wide online-character list is exposed by PublicGameData;
- no application caching is used yet because accepted staleness semantics are not defined.

Known PublicGameData unknowns:

- authoritative cluster-wide online-character identity source;
- freshness and failure semantics for online identity/availability;
- transport from Canary multichannel runtime state to Oteryn Platform;
- privileged/group-hidden character filtering policy for public rankings;
- production cache/staleness expectations.

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
- there are no approved direct Oteryn Platform writes to shared Canary data.

Known data-contract blockers/unknowns:

- `schema.sql` defines `accounts.tournament_coins`, while Canary repository code expects `accounts.coins_tournament` for tournament coin access;
- actual deployed database shape for that field is not proven;
- cluster-wide public online-character identity source/freshness policy is not yet contracted;
- product initialization rules for Platform-driven character creation are not proven.

## Authentication contract summary

`docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md` maps current behavior and separately documents a recommended target contract.

Credential migration remains blocked because:

- native Canary and external login-server authentication paths can coexist;
- current native Canary verifies custom Argon2 then SHA-1 fallback;
- current upstream external login-server verifies SHA-1 only;
- standard Laravel Argon2id stored-string compatibility with Canary is not proven;
- password/reset revocation across all game-login credential classes is not proven;
- no inspected current game-login path globally enforces MFA or email verification;
- failed native Canary password authentication logs the stored credential hash value and requires a separate Canary security fix.

## What does not exist yet

Unless source is added after this state update, the following are **not implemented**:

- full production public website/CMS;
- real Oteryn Platform account authentication/login;
- password/hash migration;
- MFA;
- account management;
- character creation/delete/rename;
- cluster-wide online character list;
- live multichannel availability integration;
- admin/RBAC/audit UI;
- Canary/shared-data write paths;
- login-server integration code owned by Oteryn Platform;
- production Cloudflare/deployment configuration;
- payments/shop.

Agents must verify repository source before relying on this list because later tasks may supersede it.

## Next planned task

`OTERYN-20260718-online-status-discovery`

Objective:

- inspect current Canary source read-only;
- prove the lifecycle and semantics of `players_online`;
- prove whether and how `cluster_sessions` can represent online character identity without exposing stale/unsafe runtime state;
- inspect `channel_runtime_status`, Redis `ChannelRuntimeRegistry` and process-local `ProtocolStatus` behavior;
- define freshness, stale-data and dependency-failure semantics;
- select one evidence-backed cluster-wide online-character source/aggregation contract, or retain the feature as `UNKNOWN` if no safe source exists;
- update `docs/contracts/CANARY_DATA_CONTRACT.md` when new durable facts are proven;
- do not implement an online-list route until the contract is proven.

## High-priority unknowns and blockers

- cluster-wide online-character identity source/freshness/failure policy;
- exact deployed production authentication topology and login-server image digest;
- password hash compatibility/migration rollout;
- password reset/change and global revocation behavior;
- MFA/email-verification enforcement across every login path;
- tournament-coin schema/code conflict;
- final production hosting/network topology;
- production mail/cache/queue providers.

## Architecture summary

```text
Cloudflare / Edge
       |
       v
Oteryn Platform (Laravel 13 / PHP 8.5 modular monolith)
       |
       +--> platform-owned DB data
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