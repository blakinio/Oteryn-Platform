# Oteryn Platform Project State

This file is the compact authoritative entry point for "where are we now?". It is not a replacement for live Git/PR/task verification.

## Last architecture-state update

2026-07-18

## Current phase

**Phase 0 — Architecture and agent bootstrap: COMPLETE**

**Phase 1 — Laravel application bootstrap: COMPLETE**

**Canary data-contract discovery: COMPLETE**

**Next work — bounded authentication/identity and web-to-game session discovery.**

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
- SQLite as the default local/test database connection;
- `GET /health` application availability route;
- baseline unit and feature tests;
- Laravel Pint formatting checks;
- GitHub Actions CI using PHP 8.5 and lockfile-backed `composer install`;
- documented local setup and validation commands;
- evidence-backed Canary data contract pinned to `blakinio/canary` commit `6df7f906ed6f8fef0aa326439a5494bd1e3d523c`;
- proven read-oriented account/player/guild/ban/session/channel schema boundaries;
- explicit zero-approved-direct-shared-write policy pending operation-level contracts;
- documented tournament-coin schema/code `CONFLICT` and remaining online/character-creation `UNKNOWN` items.

## Canary data-contract summary

`docs/contracts/CANARY_DATA_CONTRACT.md` is now partially proven and suitable as the baseline for read-only integration design.

Key proven points:

- accounts and characters are global across channels;
- `players.account_id` owns the account-to-character relationship;
- persistent channel identity is `channels.id`;
- modern protocol world-list index is transient and must not be persisted as `channels.id`;
- guild ownership/membership/rank tables and constraints are documented;
- account/IP bans, namelocks and lazy expiration behavior are documented;
- `account_sessions` and `cluster_sessions` are separate concepts;
- current per-process and multichannel status sources are documented;
- there are no approved direct Oteryn Platform writes to shared Canary data.

Known contract blockers/unknowns:

- `schema.sql` defines `accounts.tournament_coins`, while current Canary repository code expects `accounts.coins_tournament` for tournament coin access;
- actual deployed database shape for that field is not proven;
- legacy `players_online` writer lifecycle is not proven;
- cluster-wide public online-character identity source/freshness policy is not yet contracted;
- product initialization rules for Platform-driven character creation are not proven.

## What does not exist yet

Unless source is added after this state update, the following are **not implemented**:

- production website/CMS beyond the bootstrap Blade page;
- real account authentication/login;
- password/hash migration;
- MFA;
- account management;
- character management;
- highscores/guilds/character pages;
- admin/RBAC/audit UI;
- Canary integration code or shared-data write paths;
- login-server integration code;
- production Cloudflare/deployment configuration;
- payments/shop.

Agents must verify repository source before relying on this list because later tasks may supersede it.

## Next planned task

`OTERYN-20260718-auth-discovery`

Objective:

- prove which login-server/auth path is actually used;
- prove how credentials are verified and which hash formats are accepted;
- prove web/game session and token creation, TTL, replay/single-use and revocation behavior;
- prove logout, password-change/reset, ban and bypass-path interactions;
- separate current `PROVEN` behavior from recommended target behavior;
- update `docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md`;
- do not implement credential migration until a separately approved task.

## High-priority unknowns

- actual login-server component and current end-to-end authentication flow;
- password hash compatibility/migration requirements;
- game session/token creation, TTL, replay and revocation semantics;
- password reset/change behavior across web and game login;
- email verification and MFA enforcement/bypass behavior;
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
       +--> explicit Canary/login-server contracts
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