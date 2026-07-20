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
- **Phase 7 — Production hardening and operations: IN PROGRESS / EXTERNAL-EVIDENCE BLOCKED FOR COMPLETION**

## Current architecture state

Oteryn Platform is a Laravel 13 / PHP 8.5 modular monolith with Platform-owned Identity and application persistence.

Supported game accounts are greenfield only:

`1 Platform Identity <-> 1 Canary accounts.id`

Existing Canary accounts are not imported or claimed.

Platform web authentication remains separate from the still-unimplemented authoritative game-login bridge.

## Phase 7 repository-owned hardening completed on main

### Production topology evidence baseline — PR #48

Merged as `676a77590e3ec93bcad0247b3065d203ac209c40`.

`docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md` separates repository-proven capabilities from actual deployed production facts. Local `.env.example` defaults are explicitly not production evidence.

### Production configuration guardrails — PR #49

Merged as `0f876d4f2209399a85cafcff1623d8e6c810b914`.

`php artisan production:verify-configuration` fails closed for unsafe provider-independent settings without exposing secret values.

### Dependency security scanning — PR #50

Merged as `3973774727c35aea22d0a646f479a0ff079042cc`.

Required CI includes Composer advisory scanning; Dependabot provides bounded Composer/GitHub Actions update PRs.

### Browser security headers and CSP — PR #54

Merged as `eb358a245f35fda1865f13e329c07ef0f4850d2f`.

The Platform enforces same-origin CSP/browser hardening without `unsafe-inline`/`unsafe-eval`. HSTS remains deployment-evidence dependent.

### Request correlation and bounded structured logging — PR #55

Merged as `b6650966fe877a0e7872f29606b32b6394dde99f`.

Every Laravel-handled request receives a fresh server-generated UUID, normal responses expose `X-Request-ID`, and request-completion logging is bounded to request ID/method/route/status/duration. An optional JSON-to-stderr channel exists without claiming a deployed centralized sink.

Final exact-head validation for PR #55 passed as CI #727 / Agent Governance #647.

## Current Phase 7 slice — readiness and recovery runbooks

`OTERYN-20260720-phase7-production-readiness-runbooks` — PR #56.

Current branch adds:

- `docs/operations/PRODUCTION_READINESS_CHECKLIST.md` — evidence-gated release/readiness checklist with `REPO-PROVEN`, `ENV-EVIDENCE-REQUIRED` and `CROSS-REPO-BLOCKED` states;
- `docs/operations/INCIDENT_RECOVERY_RUNBOOK.md` — provider-neutral response/recovery decision order for configuration, identity/admin, Canary credential/privilege, runtime Redis, mail, logging, deployment, database restore and partial-write incidents;
- `docs/agents/handovers/OTERYN-20260720-phase7-handover.md` — continuation state, merged hardening SHAs and explicit external blockers.

This task closes the currently available repository-only Phase 7 documentation work. It does **not** close Phase 7 itself.

## Phase 7 completion blockers

Actual environment evidence is still required for:

- production DNS/Cloudflare/WAF/Access/TLS/HSTS posture;
- direct-origin exposure and ingress firewall rules;
- Platform DB engine/endpoint/network isolation/HA;
- effective Canary SQL production endpoints/network paths and credential provisioning;
- runtime Redis endpoint/ACL/network/TLS state;
- effective session/cache scaling model;
- queue/worker model;
- mail provider/domain/delivery monitoring;
- centralized logs/metrics/alerts/retention/on-call routing;
- deployment/migration/rollback mechanism;
- backup technology/policy and a dated successful operational restore test.

The authoritative Platform game-login bridge remains unresolved if Platform-originated game login is part of launch scope.

Therefore Phase 7 must remain incomplete until these evidence gates are satisfied or explicitly risk-accepted where policy permits.

## Repository-verifiable operations gates

Available commands:

```text
php artisan production:verify-configuration
php artisan canary:verify-db-privileges
php artisan canary:verify-provisioning-db-privileges
php artisan canary:verify-character-create-db-privileges
```

Required CI also includes strict Composer validation/install, Composer advisory audit, Pint, PHPStan and full tests.

Passing these proves only their documented repository/application boundaries.

## Implemented Identity/admin/shared-write boundary

- secure Platform registration/login/logout;
- revocable Platform web sessions;
- password recovery/change with session revocation;
- TOTP MFA and recovery codes;
- explicit deny-by-default administrator RBAC;
- privileged routes require `auth` + `mfa.confirmed` + exact permission;
- privileged CMS/role mutations are audited;
- bounded public game-data reads;
- generic Canary SQL remains read-only;
- exactly two approved shared-write credentials: `canary_provisioning` and `canary_character_create`.

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

`OTERYN-20260720-phase7-production-readiness-runbooks` — PR #56.

## Recommended next work

Finish PR #56 with exact-head validation and post-merge housekeeping. Then obtain sanitized evidence for the actual production topology and run the edge/origin/database exposure review plus a dated backup-restore operational test.

Until that evidence is available, do not mark Phase 7 COMPLETE or invent provider-specific deployment claims.

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
