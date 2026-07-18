# Oteryn Platform

Oteryn Platform is the planned web and application platform for the Oteryn Open Tibia Server ecosystem. It is intended to replace MyAAC as the long-term first-party web platform while keeping Canary as a separate game-server project.

## Current status

**Architecture bootstrap. No Laravel application code has been committed yet.**

The repository currently defines agent governance, architecture boundaries, security requirements, cross-repository contracts and the phased implementation roadmap. Agents must not infer that a feature exists until source code and tests prove it.

See `docs/agents/PROJECT_STATE.md` for the current authoritative state.

## Target direction

Initial implementation is planned as a **Laravel modular monolith** with server-rendered Blade views unless a later ADR changes that decision.

Primary responsibilities:

- public website and CMS/news;
- player accounts and identity;
- authentication, sessions, recovery, verification and MFA;
- character/account management allowed by the Canary contract;
- highscores, character profiles, guilds and server status;
- administration and RBAC;
- first-party API boundaries;
- audit/security logging;
- future payments/shop as an isolated later module.

Canary remains responsible for game runtime and gameplay behavior.

## Logical architecture

```text
Internet
   |
   v
Cloudflare / edge protection
   |
   v
Oteryn Platform (Laravel)
   |-- Public Web / CMS
   |-- Identity & Accounts
   |-- Characters / Guilds / Highscores
   |-- Admin / RBAC / Audit
   |-- Internal application services
   |-- Future Payments module
   |
   +---- explicit contracts ----> login-server / game authentication path
   |
   +---- explicit DB/read contracts ----> Canary-owned game data

Canary (separate repository)
   |-- game runtime
   |-- world state
   |-- game protocol
   `-- gameplay systems
```

## Core principles

1. **Repository state beats chat history.** Agents use task records, architecture docs, contracts, Git and tests as source of truth.
2. **Security is application-owned.** Cloudflare/WAF is defense in depth, never a substitute for correct auth, authorization, validation and transactions.
3. **One source of truth per responsibility.** Authentication, authorization and shared data ownership must not be duplicated implicitly across Oteryn Platform, login-server and Canary.
4. **Modular monolith first.** Do not introduce microservices without a demonstrated boundary, scaling need or independent lifecycle requirement.
5. **Canary integration is a contract.** Never silently assume table layouts, password formats, session semantics or write permissions.
6. **Payments later.** Core identity/account architecture must not depend on a payment provider.
7. **Secure defaults.** Deny ambiguous authorization, use framework security primitives, transactions for sensitive mutations and regression tests for security fixes.

## Authoritative documentation

- `AGENTS.md` — mandatory operating rules for agents.
- `docs/agents/PROJECT_STATE.md` — what exists now and what happens next.
- `docs/architecture/SYSTEM_ARCHITECTURE.md` — system structure and boundaries.
- `docs/architecture/MODULE_CATALOG.md` — planned modules and ownership.
- `docs/architecture/SECURITY_ARCHITECTURE.md` — security model and invariants.
- `docs/architecture/DATA_OWNERSHIP.md` — database/data ownership rules.
- `docs/architecture/ROADMAP.md` — phased delivery plan.
- `docs/contracts/` — Canary/login-server integration contracts.
- `docs/architecture/adr/` — durable architectural decisions.
- `docs/agents/tasks/active/` — active implementation work.

## For a new agent

1. Read `AGENTS.md`.
2. Read `docs/agents/REPOSITORY_MAP.md`.
3. Read `docs/agents/PROJECT_STATE.md`.
4. Find the relevant active task and its `## Context checkpoint`.
5. Use `docs/agents/CONTEXT_ROUTING.md` to load only task-relevant architecture/contracts.
6. Verify live Git/PR/test state before changing code.

Do not reconstruct project state from old conversations.
