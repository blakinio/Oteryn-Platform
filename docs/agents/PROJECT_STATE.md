# Oteryn Platform Project State

This file is the compact authoritative entry point for "where are we now?". It is not a replacement for live Git/PR/task verification.

## Last architecture-state update

2026-07-18

## Current phase

**Phase 0 — Architecture and agent bootstrap: COMPLETE**

**Next phase — Phase 1: Laravel application bootstrap**

## What exists on main

- root agent governance in `AGENTS.md`;
- repository map and context routing;
- durable task/checkpoint/handoff model;
- target system architecture;
- module catalog;
- security architecture;
- data ownership rules;
- test strategy;
- delivery roadmap;
- Canary data integration discovery contract;
- web-to-game authentication discovery contract;
- ADRs for Laravel modular monolith, separate Canary repository and deferred payments;
- project changelog and durable current-state documentation.

## What does not exist yet

Unless source is added after this state update, the following are **not implemented**:

- Laravel application skeleton;
- production website;
- database migrations/models;
- authentication/login;
- MFA;
- account management;
- character management;
- highscores/guilds/character pages;
- CMS/admin UI;
- Canary integration code;
- login-server integration code;
- CI application pipeline;
- Cloudflare/deployment configuration;
- payments/shop.

Agents must verify repository source before relying on this list because later tasks may supersede it.

## Next planned task

`OTERYN-20260718-laravel-bootstrap`

Objective:

- bootstrap the maintained Laravel/PHP application stack;
- establish Blade, tests, CI and safe environment configuration;
- do not implement speculative Canary shared auth/data writes.

See `docs/agents/ACTIVE_WORK.md`, then verify the individual task record and live PR/Git state.

## Work after Laravel bootstrap

Create bounded discovery tasks for:

1. actual Oteryn Canary account/player/guild schema;
2. actual login-server component and authentication/session flow;
3. password/hash compatibility and migration constraints;
4. game session/token creation/revocation behavior;
5. single-world versus multi-world requirement.

Do not implement speculative shared credential/schema mutations before required contracts are proven.

## High-priority unknowns

- exact Oteryn Canary account/player/guild schema;
- actual login-server component and current authentication flow;
- password hash compatibility/migration requirements;
- game session/token creation and revocation semantics;
- single-world versus multi-world requirements;
- final production hosting/network topology;
- mail/cache/queue providers.

## Architecture summary

```text
Cloudflare / Edge
       |
       v
Oteryn Platform (Laravel modular monolith)
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
