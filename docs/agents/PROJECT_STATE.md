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
- **Phase 7 — Production hardening and operations: IN PROGRESS / FINAL PRODUCTION EVIDENCE REQUIRED FOR COMPLETION**

## Current architecture state

Oteryn Platform is a Laravel 13 / PHP 8.5 modular monolith with Platform-owned Identity and application persistence.

Supported game accounts are greenfield only:

`1 Platform Identity <-> 1 Canary accounts.id`

Existing Canary accounts are not imported or claimed.

Platform web authentication remains separate from the still-unimplemented authoritative game-login bridge.

## Phase 7 repository-owned hardening completed on main

- PR #48 / `676a77590e3ec93bcad0247b3065d203ac209c40` — production topology evidence baseline.
- PR #49 / `0f876d4f2209399a85cafcff1623d8e6c810b914` — fail-closed provider-independent production configuration verifier.
- PR #50 / `3973774727c35aea22d0a646f479a0ff079042cc` — required Composer advisory scanning and bounded Dependabot updates.
- PR #54 / `eb358a245f35fda1865f13e329c07ef0f4850d2f` — CSP and browser security headers without unsafe inline/eval allowances.
- PR #55 / `b6650966fe877a0e7872f29606b32b6394dde99f` — server-generated request correlation and bounded request-completion logging.
- PR #56 / `ae659089bb288dd467f5e2f163ffb7d731e35cec` — production-readiness checklist, incident/recovery runbook and Phase 7 handover.

PR #56 passed CI #735 and Agent Governance #655 on exact PR head `b03a2de6836ff7769b0911f847fb5b8dc0fe8572` before squash merge.

## Phase 7 controlled production-like validation in progress

PR #63 / `task/OTERYN-20260720-phase7-production-evidence-collection` is implementing a repeatable exact-SHA production-like validation workflow.

Its evidence model is explicit:

- `STAGING_PROVEN` — directly proven by the controlled production-like workflow;
- `PRODUCTION_PROVEN` — reserved for direct evidence from the final production environment;
- `UNKNOWN` — not yet established.

The controlled workflow is intended to validate, without claiming final production state:

- clean deployment, migrations, rollback and redeploy;
- production configuration guardrails;
- effective MariaDB least-privilege principals for generic read-only, provisioning and character creation;
- fail-closed excessive/insufficient database privileges;
- runtime Redis ACL/key/command boundary and failure semantics;
- SMTP delivery through a real test SMTP service and mail failure handling;
- full critical regression suite on the exact validation SHA;
- running health/security-header/cookie/request-correlation/sensitive-error checks;
- measured database backup/restore with integrity and restored-environment smoke validation.

## Phase 7 final production-only completion evidence

Controlled staging cannot prove the final production:

- DNS/Cloudflare/WAF/Access/TLS/HSTS posture;
- direct-origin exposure and ingress firewall rules;
- Platform DB engine/endpoint/network isolation/HA and actual effective grants;
- Canary SQL production endpoints/network paths and actual credential provisioning/effective grants;
- runtime Redis endpoint/ACL/network/TLS state;
- effective session/cache scaling model;
- queue/worker model;
- mail provider/domain/delivery monitoring;
- centralized logs/metrics/alerts/retention/on-call routing;
- real deployment/migration/rollback mechanism;
- production backup schedule/technology and a dated production restore result;
- exact deployed production SHA(s) and final critical production smoke/E2E checks.

The authoritative Platform game-login bridge remains unresolved if Platform-originated game login is part of launch scope.

Therefore Phase 7 must remain incomplete until the remaining final-production evidence gates are satisfied or explicitly risk-accepted where policy permits.

## Repository-verifiable operations gates

Available commands:

```text
php artisan production:verify-configuration
php artisan canary:verify-db-privileges
php artisan canary:verify-provisioning-db-privileges
php artisan canary:verify-character-create-db-privileges
```

Required CI includes strict Composer validation/install, Composer advisory audit, Pint, PHPStan and full tests.

Passing these proves only their documented boundaries and the environment in which they are executed.

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

`OTERYN-20260720-phase7-production-evidence-collection` — IN PROGRESS — draft PR #63.

## Recommended next work

Finish exact-head validation of PR #63, record only non-secret `STAGING_PROVEN` results from the controlled workflow, close any workflow defects, and reduce the remaining Phase 7 checklist to the final production-only verification pass.

Do not mark Phase 7 COMPLETE from staging evidence alone.

## High-priority remaining unknowns

- authoritative Platform game-login assertion/session protocol and rollout;
- deployed production edge/origin/network/TLS topology;
- production runtime Redis ACL/endpoint provisioning;
- production database, mail, session/cache and queue topology;
- production backup/restore/deployment/rollback mechanisms;
- centralized production logging/metrics/alerting;
- exact production Cloudflare Access/admin-hostname choice, if adopted;
- current Canary tournament-coin schema/code naming conflict.

Payments remain deferred.
