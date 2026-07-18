# Oteryn Platform Project State

This file is the compact authoritative entry point for "where are we now?". It is not a replacement for live Git/PR/task verification.

## Last architecture-state update

2026-07-18

## Current phase

**Phase 0 — Architecture and agent bootstrap: COMPLETE**

**Phase 1 — Laravel application bootstrap: COMPLETE**

**Canary data-contract discovery: COMPLETE**

**Authentication / web-to-game session discovery: COMPLETE**

**Next work — evidence-backed read-only PublicGameData implementation.**

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
- evidence-backed Canary data contract with approved read boundaries and zero approved direct shared-data writes;
- evidence-backed current web/login-server/Canary authentication contract;
- documented target direction for one authoritative Identity policy and short-lived atomic game-login authorization;
- documented production-auth blockers including alternate login paths, SHA-1 compatibility, credential hash logging and incomplete revocation semantics.

## Canary data-contract summary

`docs/contracts/CANARY_DATA_CONTRACT.md` is partially proven and suitable as the baseline for read-only integration design.

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

Known data-contract blockers/unknowns:

- `schema.sql` defines `accounts.tournament_coins`, while current Canary repository code expects `accounts.coins_tournament` for tournament coin access;
- actual deployed database shape for that field is not proven;
- cluster-wide public online-character identity source/freshness policy is not yet contracted;
- product initialization rules for Platform-driven character creation are not proven.

## Authentication contract summary

`docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md` now maps current behavior and separately documents a recommended target contract.

Key proven current-state points:

- native Canary password login and external `opentibiabr/login-server` login can coexist in repository-supported topology;
- current native Canary verifies custom Argon2 then SHA-1 fallback;
- current upstream external login-server verifies SHA-1 only;
- standard Laravel Argon2id stored-string compatibility with Canary is not proven;
- Canary can issue 60-second process-local single-use login tokens in session mode;
- current external login-server issues replayable DB-backed `account_sessions` with 24-hour expiry;
- failed one-time token redemption can fall back to DB-backed session authentication;
- old protocols use direct password authentication;
- character ownership/deletion and account-ban gates remain enforced by Canary at world entry;
- password change/reset revocation across all game-login credential classes is not proven;
- no inspected current game-login path globally enforces MFA or email verification;
- failed native Canary password authentication logs the stored credential hash value and requires a separate Canary security fix.

Credential migration remains blocked until the authoritative Identity topology, legacy compatibility and revocation behavior are implemented and tested.

## What does not exist yet

Unless source is added after this state update, the following are **not implemented**:

- production website/CMS beyond the bootstrap Blade page;
- real Oteryn Platform account authentication/login;
- password/hash migration;
- MFA;
- account management;
- character management;
- highscores/guilds/character pages;
- admin/RBAC/audit UI;
- Canary integration code or shared-data write paths;
- login-server integration code owned by Oteryn Platform;
- production Cloudflare/deployment configuration;
- payments/shop.

Agents must verify repository source before relying on this list because later tasks may supersede it.

## Next planned task

`OTERYN-20260718-game-read-model`

Objective:

- implement read-only highscores;
- implement public character profiles from explicit approved fields;
- implement public guild read models;
- implement channel/server-status reads only where the data contract proves source semantics;
- use pagination and avoid N+1 queries;
- add caching only where staleness semantics are explicit;
- do not expose private/account/security fields;
- do not implement cluster-wide online character identity until its source/freshness contract is proven;
- perform no Canary/shared-data mutations.

## High-priority unknowns and blockers

- exact deployed production authentication topology and login-server image digest;
- password hash compatibility/migration rollout;
- password reset/change and global revocation behavior;
- MFA/email-verification enforcement across every login path;
- tournament-coin schema/code conflict;
- cluster-wide online-character identity source/freshness policy;
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