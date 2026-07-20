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

## Phase 7 completed slices

### Production topology evidence baseline

PR #48 merged as `676a77590e3ec93bcad0247b3065d203ac209c40`.

`docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md` distinguishes repository-proven configuration capabilities from actual deployed production state. Local `.env.example` defaults are not production evidence.

### Provider-independent production configuration guardrails

PR #49 merged as `0f876d4f2209399a85cafcff1623d8e6c810b914`.

`php artisan production:verify-configuration` fails closed for unsafe provider-independent production settings: non-production environment, debug enabled, missing APP_KEY, non-HTTPS/local APP_URL, insecure session cookie settings and non-delivery/test mail configuration.

The verifier does not expose secret values and intentionally does not require a specific database/cache/queue/logging/Cloudflare provider.

### Dependency security scanning

PR #50 merged as `3973774727c35aea22d0a646f479a0ff079042cc`.

Required CI now runs:

`composer audit --no-interaction`

The existing Composer validation/install, Pint, PHPStan and test gates remain required. Dependabot is configured for bounded weekly Composer and GitHub Actions update PRs.

The validated lockfile passed the advisory scan at merge time.

## Current Phase 7 slice — browser security headers and CSP

`OTERYN-20260720-phase7-security-headers-csp` — PR #54.

Current branch implementation:

- moves first-party inline public CSS to same-origin `public/css/app.css`;
- loads that stylesheet from public/game and administrator layouts;
- applies browser security headers to Laravel `web` responses;
- enforces CSP with same-origin default/script/style/connect/font sources, self/data image sources, `form-action 'self'`, `base-uri 'none'`, `frame-ancestors 'none'` and `object-src 'none'`;
- does not grant `unsafe-eval` or inline-script execution;
- adds `X-Content-Type-Options: nosniff`;
- adds `X-Frame-Options: DENY`;
- adds `Referrer-Policy: strict-origin-when-cross-origin`;
- adds restrictive camera/geolocation/microphone/payment/USB `Permissions-Policy`;
- covers public and authentication responses with regression tests.

HSTS is intentionally not hard-coded because deployed TLS termination, proxy and hostname/subdomain policy remain unproven.

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

## Implemented Identity boundary

- secure Platform registration/login/logout;
- revocable Platform web sessions;
- password recovery/change with session revocation;
- TOTP MFA and single-use recovery codes;
- security-event recording;
- reusable `mfa.confirmed` gate;
- explicit administrator RBAC separate from authentication and MFA.

## Implemented Admin/CMS/Audit boundary

Phase 6 merged through:

- PR #44 / `170d52393e543c8033ebd896f42fb43f3fccdf42` — explicit deny-by-default RBAC;
- PR #45 / `be25d6ec3e0512bb9615329f99f16fff294d8b1d` — first-admin bootstrap, audited role lifecycle, privileged news/pages and administrator audit;
- PR #46 / `f25abd8799718ac99acce050ac55018d04fff2de` — Phase 6 closure.

Every current administrator web capability requires:

`auth` + `mfa.confirmed` + `admin.permission:<exact-permission>`

No wildcard administrator authorization path exists.

## Implemented public/read-only boundary

- public news and managed pages;
- character search/profile and level highscores;
- guild detail/membership;
- cluster-wide online-character list;
- configured channels with fresh runtime availability projection;
- database-enforced `canary` / `oteryn_readonly` SELECT-only SQL boundary;
- separate read-only `canary_runtime` Redis boundary.

## Implemented Phase 5 shared-write boundary

Exactly two Oteryn Platform -> Canary mutation surfaces are approved:

- `canary_provisioning` — greenfield account provisioning/recovery under `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`;
- `canary_character_create` — greenfield character creation under `CHARACTER_CREATION_CONTRACT.md` and ADR 0005.

The generic `canary` connection remains database-enforced read-only.

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

`OTERYN-20260720-phase7-security-headers-csp` — PR #54.

## Recommended next work

Finish PR #54 with exact-head validation. If actual deployment evidence remains unavailable, continue with provider-neutral request correlation and structured logging primitives while explicitly leaving the deployed logging/metrics/alerting sink `UNKNOWN`.

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
