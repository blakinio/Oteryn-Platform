# Oteryn Platform Project State

This file is the compact authoritative entry point for "where are we now?". It is not a replacement for live Git/PR/task verification.

## Last architecture-state update

2026-07-18

## Current phase

**Phase 0 — Architecture and agent bootstrap**

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
- initial Canary data contract placeholder;
- initial web-to-game authentication contract placeholder;
- ADRs for Laravel modular monolith, separate Canary repository and deferred payments.

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

## Current active task

- `OTERYN-20260718-platform-architecture-bootstrap`
  - record: `docs/agents/tasks/active/OTERYN-20260718-platform-architecture-bootstrap.md`
  - objective: finish durable architecture/governance bootstrap.

See `docs/agents/ACTIVE_WORK.md` as a convenience index, then verify the individual task record/live PR.

## Next recommended work

After architecture bootstrap is complete:

1. create a dedicated **Phase 1 Laravel bootstrap** task;
2. select the currently maintained Laravel/PHP version based on official support at implementation time;
3. create the application skeleton, test baseline and CI;
4. in parallel or immediately after, create bounded Canary/login-server discovery tasks;
5. do not implement speculative shared credential/schema mutations before contracts are proven.

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
