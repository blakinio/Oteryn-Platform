# Oteryn Platform Project State

This file is the compact authoritative entry point for "where are we now?". It is not a replacement for live Git/PR/task verification.

## Last architecture-state update

2026-07-20

## Current phase

- **Phase 0 — Architecture and agent bootstrap: COMPLETE**
- **Phase 1 — Laravel application bootstrap: COMPLETE**
- **Phase 2 — Canary/login authentication discovery for current implementation boundaries: COMPLETE**
- **Phase 3 — Identity foundation: COMPLETE**
- **Phase 4 — Public website and read-only game data: COMPLETE**
- **Phase 5 — Account and character management: COMPLETE**
- **Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**
- **Phase 7 — Production hardening and operations: IN PROGRESS**

## Current architecture state

Oteryn Platform is a Laravel 13 / PHP 8.5 modular monolith with Platform-owned Identity and application persistence.

Supported game accounts are greenfield only:

`1 Platform Identity <-> 1 Canary accounts.id`

Existing Canary accounts are not imported or claimed.

Platform web authentication remains separate from the still-unimplemented authoritative game-login bridge.

## Phase 7 completed repository-owned slices

### Production topology evidence baseline

PR #48 merged as `676a77590e3ec93bcad0247b3065d203ac209c40`.

`docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md` distinguishes repository-proven capabilities from actual deployed production state. Local `.env.example` defaults are not production evidence.

### Provider-independent production configuration guardrails

PR #49 merged as `0f876d4f2209399a85cafcff1623d8e6c810b914`.

`php artisan production:verify-configuration` fails closed for unsafe provider-independent settings: non-production environment, debug enabled, missing APP_KEY, non-HTTPS/local APP_URL, insecure session cookie settings and non-delivery/test mail configuration.

### Dependency security scanning

PR #50 merged as `3973774727c35aea22d0a646f479a0ff079042cc`.

Required CI runs `composer audit --no-interaction` in addition to Composer validation/install, Pint, PHPStan and tests. Dependabot provides bounded weekly Composer and GitHub Actions update PRs.

### Browser security headers and CSP

PR #54 merged as `eb358a245f35fda1865f13e329c07ef0f4850d2f`.

- first-party CSS is same-origin and no longer embedded inline in the public layout;
- CSP restricts default/script/style/connect/font to same-origin, permits self/data images, limits forms to self and denies objects/framing/base-uri changes;
- no `unsafe-inline` or `unsafe-eval` allowance;
- `nosniff`, frame denial, strict referrer policy and restrictive permissions policy are applied to web responses;
- HSTS remains unclaimed until actual TLS/proxy/hostname topology is proven.

## Current Phase 7 slice — request correlation and logging

`OTERYN-20260720-phase7-request-correlation-logging` — PR #55.

Current branch implementation:

- generates a fresh server-owned UUID for every Laravel-handled request;
- ignores inbound `X-Request-ID` as authoritative correlation input;
- returns the server-generated identifier through `X-Request-ID` on normal responses;
- records one bounded `http.request.completed` event with request ID, HTTP method, route name, response status and duration only;
- excludes query strings, request bodies, full URLs, headers and credentials from that completion context;
- adds an optional JSON-to-stderr logging channel while leaving the default logging choice unchanged;
- correlates `/health` as well as normal application routes.

CI #721 passed Composer advisory audit, Pint, PHPStan and full tests on the cleaned implementation head after exact diagnostic fixes. Final synchronized exact-head validation remains required before merge.

This proves only application-side correlation and log shape. A deployed centralized log/metrics/alerting sink remains `UNKNOWN`.

## Phase 7 deployed-state unknowns

The repository does not currently prove:

- actual Cloudflare/DNS/WAF/Access configuration;
- actual TLS termination and safe HSTS policy;
- actual origin provider, reverse proxy or ingress firewall restrictions;
- actual Platform production database engine/endpoint/network isolation;
- actual production session/cache backend;
- actual queue/worker model;
- actual mail provider/delivery status;
- actual centralized log/metrics/alerting sink;
- actual Canary SQL production network paths/credential provisioning status;
- actual runtime Redis endpoint/ACL provisioning status;
- actual backup/restore, deployment or rollback mechanism.

## Implemented Identity and privileged application boundary

- secure Platform registration/login/logout;
- revocable Platform web sessions;
- password recovery/change with session revocation;
- TOTP MFA and single-use recovery codes;
- security-event recording;
- explicit administrator RBAC separate from authentication and MFA;
- privileged routes require `auth` + `mfa.confirmed` + exact permission;
- privileged CMS/role mutations are audited;
- no wildcard administrator authorization path exists.

## Implemented public/read-only and shared-write boundary

- public news, managed pages and bounded public game-data reads;
- database-enforced generic Canary SELECT-only boundary;
- separate read-only `canary_runtime` Redis boundary;
- exactly two approved Oteryn Platform -> Canary mutation surfaces:
  - `canary_provisioning` for greenfield account provisioning/recovery;
  - `canary_character_create` for greenfield character creation.

Deferred and not authorized:

- existing-account claim/import;
- character deletion/soft deletion;
- character rename;
- irreversible Canary account deletion;
- exceptional unlink/rebind/transfer.

## Game-login boundary

Platform-originated users still require a separately authorized authoritative game-login bridge before game login can use Platform credential authority.

Expected external scope remains primarily `opentibiabr/login-server`; `blakinio/canary` changes require separate explicit authorization if needed by the selected protocol.

No Canary/login-server repository was modified by Phase 7 work.

## Current active task

`OTERYN-20260720-phase7-request-correlation-logging` — PR #55.

## Recommended next work

Finish PR #55 with exact-head validation. If deployment evidence remains unavailable, continue with provider-neutral production-readiness and incident/recovery runbooks that clearly distinguish executable repository checks from environment-evidence-required operations.

## High-priority remaining unknowns

- authoritative Platform game-login assertion/session protocol and rollout;
- deployed production edge/origin/network/TLS topology;
- production runtime Redis ACL/endpoint provisioning;
- production database, mail, session/cache and queue topology;
- backup/restore/deployment/rollback mechanisms;
- centralized production logging/metrics/alerting;
- exact production Cloudflare Access/admin-hostname choice, if adopted;
- current Canary tournament-coin schema/code naming conflict.

Payments remain deferred.
