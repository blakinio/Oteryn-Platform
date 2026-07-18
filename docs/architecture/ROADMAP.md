# Oteryn Platform Delivery Roadmap

## Goal

Replace MyAAC with a first-party Oteryn web/application platform without coupling the project to speculative Canary or login-server assumptions.

The roadmap is ordered by risk: establish contracts and identity correctness before adding broad mutation features or payments.

## Phase 0 — Architecture and agent bootstrap

**Status: IN PROGRESS**

Deliverables:

- agent governance and durable task/checkpoint workflow;
- product/system architecture;
- module catalog;
- security architecture;
- data ownership policy;
- cross-repository contract placeholders;
- ADRs for initial direction;
- project state and roadmap.

Exit gate:

- a fresh agent can continue without chat history;
- unknown integration facts are explicitly listed rather than assumed.

## Phase 1 — Laravel application bootstrap

**Status: PLANNED**

Deliverables:

- supported PHP/Laravel version selected from current maintained releases at implementation time;
- clean Laravel application skeleton;
- Blade-based initial frontend;
- environment template with no secrets;
- test framework and baseline test;
- formatter/linter/static-analysis decision;
- CI for install + tests + lint/static checks;
- local development setup documentation;
- basic health endpoint.

Exit gate:

- reproducible local install;
- clean CI on main;
- no production credentials in repository.

## Phase 2 — Canary and login authentication discovery

**Status: PLANNED / REQUIRED BEFORE SHARED AUTH MUTATIONS**

Deliverables:

- verified account/player/guild schema references from actual Oteryn Canary;
- verified login-server repository/component and interface;
- password/hash compatibility evidence;
- session/token flow evidence;
- account ban/status semantics;
- game-login revocation behavior;
- world model: single-world or multi-world;
- completed Canary/auth contracts in `docs/contracts/**`.

Exit gate:

- no critical auth/data integration question remains `UNKNOWN` for the next implementation scope.

## Phase 3 — Identity foundation

**Status: PLANNED**

Deliverables:

- registration policy;
- login/logout;
- secure password storage/migration strategy proven compatible with game login;
- session management;
- password reset;
- email verification if required;
- rate limiting;
- account security event audit;
- administrator MFA foundation;
- tests for auth, authorization and revocation invariants.

Exit gate:

- end-to-end web auth works;
- game-login compatibility is tested where relevant;
- password reset/change behavior cannot leave unintended bypass sessions;
- administrator authentication policy is defined and tested.

## Phase 4 — Public website and read-only game data

**Status: PLANNED**

Deliverables:

- public layout/navigation;
- homepage/server status;
- news display;
- character search/profile;
- highscores;
- guild pages;
- online list where supported;
- efficient read/query services;
- caching only after correctness/freshness policy is defined.

Exit gate:

- no write access required for public game-data features;
- query performance avoids obvious N+1/mass-query patterns;
- public output is escaped/sanitized correctly.

## Phase 5 — Account and character management

**Status: PLANNED**

Deliverables depend on verified Canary contract and may include:

- account profile/settings;
- character creation;
- character deletion/soft deletion;
- character rename or other lifecycle operations if product requires them;
- ownership and online-state checks;
- transactional integration tests.

Exit gate:

- every shared write is documented in contract;
- authorization and concurrency invariants tested;
- no undocumented raw writes to Canary-owned data.

## Phase 6 — CMS, Admin, RBAC and Audit

**Status: PLANNED**

Deliverables:

- news/page management;
- role/permission model;
- dedicated privileged actions;
- mandatory admin MFA;
- admin audit trail;
- Cloudflare Access deployment option/documentation;
- no arbitrary code/plugin upload feature.

Exit gate:

- deny-by-default policies;
- privileged operations covered by authorization tests;
- admin actions auditable.

## Phase 7 — Production hardening and operations

**Status: PLANNED**

Deliverables:

- production deployment architecture;
- Cloudflare/WAF/rate-limit configuration plan;
- origin/database network restriction;
- backups and tested restore procedure;
- structured logging and monitoring;
- dependency/security scanning;
- security headers/CSP review;
- queue/cache/mail production setup where used;
- runbooks for incident/recovery;
- end-to-end regression suite for critical account/game-login flows.

Exit gate:

- production readiness checklist complete;
- known critical/high findings resolved or explicitly risk-accepted by owner.

## Phase 8 — Payments, coins and shop

**Status: DEFERRED**

Start only after core platform and identity are stable.

Deliverables:

- dedicated payment ADR/threat model;
- provider integration;
- signed webhook verification;
- idempotency/replay controls;
- immutable transaction ledger;
- reconciliation;
- refunds/chargebacks;
- shop fulfillment contract with Canary;
- admin and fraud controls.

Exit gate:

- financial consistency tested under retries/concurrency;
- payment/provider security reviewed separately.

## Cross-cutting rule

A phase may be split into small tasks and PRs. Agents should not implement an entire phase as one large change.

Before each task:

1. create an active task record;
2. claim owned paths;
3. load routed context;
4. prove required external facts;
5. implement the smallest complete vertical slice;
6. test and update documentation/contracts.
