# Oteryn Platform Project State

This file is the compact authoritative entry point for "where are we now?". It is not a replacement for live Git/PR/task verification.

## Last architecture-state update

2026-07-18

## Current phase

**Phase 0 — Architecture and agent bootstrap: COMPLETE**

**Phase 1 — Laravel application bootstrap: COMPLETE**

**Next work — bounded Canary data-contract discovery, followed by authentication/session discovery.**

## What exists on main

After completion of the Laravel bootstrap delivery PR, the repository contains:

- root agent governance in `AGENTS.md`;
- repository map and context routing;
- durable task/checkpoint/handoff model;
- target system architecture and module boundaries;
- security architecture and data ownership rules;
- test strategy and delivery roadmap;
- Canary data and web-to-game authentication discovery contracts;
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
- documented local setup and validation commands.

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

`OTERYN-20260718-canary-schema-discovery`

Objective:

- inspect the actual current `blakinio/canary` schema/source as read-only evidence;
- prove account, player, guild, world/server, online, ban and session-related structures;
- classify every contract statement as `PROVEN`, `DERIVED`, `UNKNOWN` or `CONFLICT`;
- update `docs/contracts/CANARY_DATA_CONTRACT.md`;
- do not implement shared-data write paths until the contract is proven.

After that, create the separate authentication/identity discovery task to prove the actual login-server, credential verification and game-session flow.

## High-priority unknowns

- exact Oteryn Canary account/player/guild schema;
- actual login-server component and current authentication flow;
- password hash compatibility/migration requirements;
- game session/token creation, TTL, replay and revocation semantics;
- single-world versus multi-world requirements;
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
