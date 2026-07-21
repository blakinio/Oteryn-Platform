# Oteryn Platform Project State

This file is the compact authoritative entry point for "where are we now?". It is not a replacement for live Git/PR/task verification.

## Last architecture-state update

2026-07-21

## Current phase

- **Phase 0 — Architecture and agent bootstrap: COMPLETE**
- **Phase 1 — Laravel application bootstrap: COMPLETE**
- **Phase 2 — Canary/login authentication discovery for current implementation boundaries: COMPLETE**
- **Phase 3 — Identity foundation: COMPLETE**
- **Phase 4 — Public website and read-only game data: COMPLETE**
- **Phase 5 — Account and character management: COMPLETE**
- **Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**
- **Phase 7 — Production hardening and operations: COMPLETE**

## Operational release state

- **Production Readiness: STAGING_PROVEN**
- **Production Go-Live Gate: PENDING PRODUCTION VERIFICATION**
- **Production Verification: REQUIRED BEFORE GO-LIVE**

ADR 0007 separates Phase 7 engineering/hardening completion from final production go-live verification. Phase 7 completion does not claim that the final production environment is production-ready or `PRODUCTION_PROVEN`.

## Current architecture state

Oteryn Platform is a Laravel 13 / PHP 8.5 modular monolith with Platform-owned Identity and application persistence.

Supported game accounts are greenfield only:

`1 Platform Identity <-> 1 Canary accounts.id`

Existing Canary accounts are not imported or claimed.

Platform web authentication remains separate from the still-unimplemented authoritative game-login bridge.

## Phase 7 engineering/hardening completed

- PR #48 / `676a77590e3ec93bcad0247b3065d203ac209c40` — production topology evidence baseline.
- PR #49 / `0f876d4f2209399a85cafcff1623d8e6c810b914` — fail-closed provider-independent production configuration verifier.
- PR #50 / `3973774727c35aea22d0a646f479a0ff079042cc` — required Composer advisory scanning and bounded Dependabot updates.
- PR #54 / `eb358a245f35fda1865f13e329c07ef0f4850d2f` — CSP and browser security headers without unsafe inline/eval allowances.
- PR #55 / `b6650966fe877a0e7872f29606b32b6394dde99f` — server-generated request correlation and bounded request-completion logging.
- PR #56 / `ae659089bb288dd467f5e2f163ffb7d731e35cec` — production-readiness checklist, incident/recovery runbook and Phase 7 handover.
- PR #63 / `61f72ddda5c253f26c7d59aa7b6fce3506f120dc` — controlled production-like validation harness and staging evidence closure.
- ADR 0007 — durable separation of Phase 7 engineering completion from the Production Go-Live Gate.

## Phase 7 controlled production-like validation

PR #63 is merged on `main`. Its final PR head `7842f78ec4ac2d07d3800ffe8bde9809b055822d` passed:

- Phase 7 Production-Like Validation run `29779554130` / #9;
- required CI run `29779553687` / #759;
- Agent Governance run `29779554188` / #679.

Final PR-head staging artifact evidence:

- classification `STAGING_PROVEN`;
- rollback target `b6878c4775eda542738c78ea99fd5d2e19d2b35f`;
- measured controlled restore `105 ms`;
- source/restored tables `13/13`;
- source/restored migrations `11/11`;
- validation-SHA probe matched.

The controlled evidence closes the staging-verifiable engineering/hardening work for:

- clean deployment, migrations, controlled rollback, interrupted-release isolation and redeploy;
- provider-independent production configuration guardrails and invalid-config fail-closed behavior;
- effective MariaDB least-privilege principals for generic read-only, provisioning and character creation;
- prohibited cross-surface writes and excessive/insufficient database privilege fail-closed behavior;
- runtime Redis ACL/key/command boundary plus missing/malformed/unavailable dependency semantics;
- SMTP delivery through a real test SMTP service and unavailable-mail behavior;
- exact-SHA critical feature/integration regression coverage across Identity, admin/RBAC/CMS, account/binding, character and public game-data surfaces;
- running health, CSP/security headers, Secure/HttpOnly cookies, request correlation, JSON request-completion logging and representative sensitive-error/log behavior;
- real production-like MariaDB backup/clean restore/integrity/restored-environment smoke with measured staging recovery time.

The `105 ms` final-head recovery result and the earlier `102 ms` staging snapshot are staging measurements only. Neither is a production RTO or RPO.

Detailed evidence is maintained in `docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md`.

## Production Go-Live Gate

The authoritative fail-closed gate is `docs/operations/PRODUCTION_READINESS_CHECKLIST.md`.

PR #92 prepares `docs/operations/PRODUCTION_VERIFICATION_EVIDENCE.md` as the non-secret execution record for issue #91. The evidence record does not itself prove production; every production-specific fact starts as `UNKNOWN` until directly verified against the exact deployed release.

Controlled staging cannot prove the final production:

- exact deployed Oteryn Platform SHA and relevant Canary/login-server versions;
- DNS/Cloudflare/WAF/Access/TLS/HSTS posture;
- direct-origin exposure and ingress firewall/reverse-proxy rules;
- Platform DB engine/endpoint/network isolation/HA and production credential ownership/rotation;
- Canary SQL production endpoints/network paths and actual effective grants for each enabled dedicated principal;
- runtime Redis endpoint/ACL/network/TLS state and dependency/freshness monitoring;
- effective session/cache scaling model and queue/worker topology;
- mail provider/domain/delivery/bounce monitoring;
- centralized logs/metrics/alerts/retention/access/on-call routing;
- actual provider deployment/migration/rollback mechanism and emergency operator authorization;
- production backup scope/schedule/retention/encryption/access policy and a dated production restore result;
- final critical production smoke/E2E checks against the exact deployed SHA.

These facts remain `UNKNOWN` until directly proven in the final production environment. `STAGING_PROVEN` or `REPO-PROVEN` evidence does not promote them to `PRODUCTION_PROVEN`.

An eligible owner risk decision, where policy permits, does not fabricate production evidence and cannot be used to claim that an unverified production fact was verified.

The Production Go-Live Gate must remain pending until its mandatory production verification is complete.

The authoritative Platform game-login bridge remains a separately authorized cross-repository requirement. If Platform-originated game login is part of launch scope, the go-live gate remains blocked until that requirement is resolved and proven end to end.

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

No Canary/login-server repository was modified by Phase 7 work or the production-verification preparation task.

## Current active task

`OTERYN-20260721-production-go-live-verification-prep` / PR #92.

Repository-only scope: prepare a fail-closed non-secret production verification evidence packet for issue #91. It does not perform production deployment, production mutation smoke or cross-repository writes.

## Recommended next work

Complete and merge PR #92, then leave issue #91 blocked until the exact final deployed production SHA, explicit production deployment/verification authorization and production evidence access are available. At that point execute only the authoritative Production Go-Live Gate and final production smoke against the exact deployed release.

Do not repeat the closed staging validation unless the production candidate code or relevant contracts change materially, and do not claim production readiness from staging evidence alone.

## High-priority remaining unknowns

- authoritative Platform game-login assertion/session protocol and rollout if required for launch scope;
- deployed production edge/origin/network/TLS topology;
- production runtime Redis ACL/endpoint provisioning;
- production database, mail, session/cache and queue topology;
- production backup/restore/deployment/rollback mechanisms;
- centralized production logging/metrics/alerting;
- exact production Cloudflare Access/admin-hostname choice, if adopted;
- current Canary tournament-coin schema/code naming conflict.

Payments remain deferred.
