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

`docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md` distinguishes repository-proven configuration capabilities from actual deployed production state.

The repository does **not** currently prove actual Cloudflare/DNS/WAF/Access policy, origin ingress restrictions, production database/Redis endpoints, session/cache backend, queue/worker model, mail provider, centralized monitoring, backup/restore implementation or deployment/rollback mechanism.

Local `.env.example` defaults are not production evidence.

### Provider-independent production configuration guardrails

PR #49 merged as `0f876d4f2209399a85cafcff1623d8e6c810b914`.

Command:

`php artisan production:verify-configuration`

It fails closed when repository-defined invariant production configuration is unsafe:

- environment is not `production`;
- debug is enabled;
- application encryption key is missing;
- `APP_URL` is not HTTPS or uses localhost/loopback;
- Secure or HttpOnly session cookies are disabled;
- default mail transport is non-delivery (`array`/`log`) for implemented password-recovery flows;
- sender address is invalid or uses a reserved test domain.

Violation output does not print application keys or other secret values.

The verifier intentionally does not require a specific database engine, Redis session/cache backend, asynchronous queue, logging provider or Cloudflare policy because those remain topology-dependent.

## Current Phase 7 slice — dependency security scanning

`OTERYN-20260720-phase7-dependency-security-scanning` — PR #50.

The current branch adds:

- required CI step `composer audit --no-interaction` after dependency installation from the committed lockfile;
- preserved Composer validation, Pint, PHPStan and full test gates;
- bounded weekly Dependabot updates for Composer;
- bounded weekly Dependabot updates for GitHub Actions.

CI #687 proved the current locked Composer dependency set passes the new advisory audit.

Dependabot update automation complements but does not replace the required fail-closed advisory gate.

No dependency version is changed directly by this task.

## Phase 7 blocked deployment-dependent work

The following work cannot be truthfully completed from repository evidence alone:

- Cloudflare/WAF/rate-limit production configuration validation;
- direct-origin bypass validation and ingress firewall review;
- production database and runtime Redis network-isolation validation;
- backup/restore operational validation;
- deployed centralized logging/metrics/alerting validation;
- production mail-delivery validation;
- deployment/rollback operational validation.

These require non-secret evidence from the actual deployed environment.

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

Phase 6 CMS authoring is plain text with escaped public output. Rich HTML, media uploads and arbitrary plugin/code upload are not implemented.

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

Required future properties include exact-account binding, short-lived cryptographically protected exchange material, explicit audience/expiry, replay-resistant consumption/session semantics and deterministic revocation/failure behavior.

Expected external scope remains primarily `opentibiabr/login-server`; `blakinio/canary` changes require separate explicit authorization if needed by the selected protocol.

No Canary/login-server repository was modified by Phase 7 work.

## Current active task

`OTERYN-20260720-phase7-dependency-security-scanning` — PR #50.

## Recommended next work

Finish PR #50 with exact-head validation. If actual deployment evidence remains unavailable, continue with repository-owned security headers/CSP hardening rather than inventing provider-specific infrastructure state.

## High-priority remaining unknowns

- authoritative Platform game-login assertion/session protocol and rollout;
- deployed production edge/origin/network topology;
- production runtime Redis ACL/endpoint provisioning;
- production database, mail, session/cache and queue topology;
- backup/restore/deployment/rollback mechanisms;
- centralized production logging/metrics/alerting;
- exact production Cloudflare Access/admin-hostname choice, if adopted;
- current Canary tournament-coin schema/code naming conflict.

Payments remain deferred.
