# Oteryn Platform Delivery Roadmap

## Goal

Replace MyAAC with a first-party Oteryn web/application platform without coupling the project to speculative Canary or login-server assumptions.

The roadmap is ordered by risk: establish contracts and identity correctness before adding broad mutation features or payments.

## Phase 0 — Architecture and agent bootstrap

**Status: COMPLETE**

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

**Status: COMPLETE**

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

**Status: COMPLETE FOR CURRENT IMPLEMENTATION BOUNDARIES**

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

Unresolved production/global-auth facts remain explicit blockers for later authoritative game-login migration; completing Phase 2 does not convert those unknowns into assumptions.

## Phase 3 — Identity foundation

**Status: COMPLETE**

Delivered:

- Platform-owned registration policy and Identity persistence;
- secure Platform web login/logout;
- revocable Platform web-session generation and session invalidation;
- password recovery and authenticated password change with Platform web-session revocation;
- current credential strategy: Platform Identity credentials remain Platform-owned and framework-hashed, with no shared Canary password migration/write until the authoritative-Identity rollout gates in `AUTH_GAME_LOGIN_CONTRACT.md` are satisfied;
- current email-verification policy: not required for Phase 3 product scope and therefore intentionally not enabled; any future global requirement must account for alternate Canary/login-server authentication paths;
- layered rate limiting for registration, login, recovery, password change and MFA flows;
- append-oriented account security event audit primitives for implemented Identity security events;
- complete opt-in Platform web MFA enrollment, challenge, recovery-code, replay-prevention, disable and session-revocation lifecycle using a maintained TOTP provider;
- reusable `mfa.confirmed` middleware foundation for future privileged routes;
- administrator authentication policy foundation: future privileged/Admin routes must combine `auth`, explicit Phase 6 RBAC/policy authorization and mandatory `mfa.confirmed`; the MFA gate does not classify administrators or grant authorization;
- auth, MFA, rate-limit, session and revocation regression coverage.

Exit gate:

- end-to-end Platform web auth works;
- current game-login compatibility is preserved by credential-boundary non-interference: Phase 3 does not mutate Canary reusable credentials or game sessions;
- password reset/change revokes Platform web sessions and does not claim to revoke unrelated Canary/login-server credentials;
- administrator authentication policy is defined and the confirmed-MFA gate is tested independently from the future Phase 6 role/permission model.

Phase 3 completion does **not** claim that Platform MFA, email verification, password change or password reset is globally enforced for native Canary or external login-server authentication. The cross-path authoritative Identity migration remains a separate later programme governed by `AUTH_GAME_LOGIN_CONTRACT.md`.

## Phase 4 — Public website and read-only game data

**Status: IN PROGRESS**

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

Current implementation includes a shared public Blade shell/navigation, homepage exact-name character search routing, read-only highscores, character profiles, guild pages, configured server/channel metadata, the cluster-wide online-character list, and a Platform-owned published-only public news list/detail read model. News authoring/management remains a future privileged CMS concern, live multichannel runtime availability remains a separate integration concern, and caching remains deferred until correctness/freshness policy is defined.

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
- mandatory admin MFA by combining explicit RBAC/policy authorization with the Phase 3 `mfa.confirmed` gate;
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
