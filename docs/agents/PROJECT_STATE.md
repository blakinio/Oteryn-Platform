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

The E2E coverage-hardening programme is a continuous verification track. It does not reopen a completed delivery phase.

## Operational release state

- **Production Readiness: STAGING_PROVEN**
- **Production Go-Live Gate: PENDING PRODUCTION VERIFICATION**
- **Production Verification: REQUIRED BEFORE GO-LIVE**

ADR 0007 separates Phase 7 engineering/hardening completion from final production go-live verification. ADR 0008 defines risk-based continuous E2E validation beyond the acceptance baseline. Repository/staging evidence never substitutes for direct production verification.

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

The production-like path proves, within its controlled staging boundary:

- clean deployment, migrations, controlled rollback, interrupted-release isolation and redeploy;
- provider-independent production configuration guardrails;
- effective MariaDB least-privilege principals for generic read-only, provisioning and character creation;
- prohibited cross-surface writes and privilege fail-closed behavior;
- runtime Redis ACL/key/command boundaries and missing/malformed/unavailable dependency semantics;
- SMTP delivery through a real test SMTP service and unavailable-mail behavior;
- exact-SHA critical feature/integration regressions across Identity, admin/RBAC/CMS, account/binding, character and public game-data surfaces;
- running health, CSP/security headers, Secure/HttpOnly cookies, request correlation, JSON request-completion logging and representative sensitive-error/log behavior;
- production-like MariaDB backup/clean restore/integrity/restored-environment smoke.

Detailed evidence is maintained in `docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md`. Staging recovery measurements are not production RTO/RPO claims.

## Continuous E2E coverage hardening

ADR 0008 plus `docs/testing/E2E_COVERAGE_ROADMAP.md` define the additive continuous-verification programme beyond the already `STAGING_PROVEN` functional acceptance baseline.

Merged slices:

- PR #94 / `26ff602696c597aac0833415b0a47af5d427a52d` — bounded Chromium/Firefox/WebKit portability, representative desktop/tablet/mobile journeys and browser-visible session/ownership security checks.
- PR #99 / `21d67c7e7edb533f9765ff96417f2ab2fbb1aea8` — existing-data migration, candidate smoke, old-code rollback smoke against the post-upgrade database and candidate redeploy validation.
- PR #102 / `ee235cbbdd379a5047fede98ff79a0e35e22ce76` — exact response `X-Request-ID` to structured request-completion log correlation.
- PR #106 / `8030f98d7280c16705f34f2d29c8ebd7fc85f285` — zero-retry Chromium public dependency recovery for Canary read grants and Redis `HMGET` ACL restoration.
- PR #111 / `740d9879b341d98e4cf0ef0e7f076b43cd86cdaf` — required bounded keyboard/focus accessibility plus reusable acceptance execution, scheduled/manual three-iteration stability measurement and bounded read-only public soak calibration.

PR #111 final head `66a1acb2fd508210c3bbd941ac1036a73af9be32`, synchronized with the then-current `main`, passed:

- CI run `29855146602`;
- Agent Governance run `29855146606`;
- Platform DB Outage Validation run `29855146617`;
- Phase 7 Production-Like Validation run `29855146614`;
- Acceptance E2E and Visual UX run `29855146601`.

The required pull-request `critical` profile now composes:

- primary Chromium smoke;
- bounded Chromium/Firefox/WebKit portability;
- bounded desktop/tablet/mobile responsive validation;
- bounded Chromium public dependency resilience;
- bounded Chromium keyboard/focus accessibility interaction.

The `full` profile requires the full primary Chromium baseline plus resilience and accessibility before it can claim `FUNCTIONAL_ACCEPTANCE_STAGING_PROVEN` or execute the visual/accessibility collector.

The merged stability workflow runs three fresh isolated zero-retry `critical` jobs on its scheduled/manual cadence. The merged soak workflow runs a bounded read-only Chromium public-surface soak and records navigation-time, Laravel process-tree RSS and Redis key-count calibration metrics without arbitrary performance thresholds.

Their first scheduled/manual runtime measurements remain pending. This is intentional: they are non-blocking evidence profiles until measured variance justifies stronger gates.

Concurrency, locking, uniqueness, ambiguous commits and core data-integrity invariants remain primarily real-database integration concerns; browser E2E is added only for unique composed user-visible outcomes.

All continuous-hardening evidence remains staging/repository evidence. It does not change the Production Go-Live Gate.

## Production Go-Live Gate

The authoritative fail-closed gate is `docs/operations/PRODUCTION_READINESS_CHECKLIST.md`.

PR #92 / `c18432df6b387932aa04e1eb269677c9078d9063` prepared `docs/operations/PRODUCTION_VERIFICATION_EVIDENCE.md` as the non-secret execution record for issue #91. The record does not itself prove production; every production-specific fact starts as `UNKNOWN` until directly verified against the exact deployed release.

Controlled staging cannot prove the final production:

- exact deployed Oteryn Platform SHA and relevant Canary/login-server versions;
- DNS/edge/TLS/WAF/origin exposure and ingress controls;
- production Platform DB topology/isolation/credentials/backup/restore;
- production Canary SQL effective grants;
- production runtime Redis ACL/network/TLS/freshness monitoring;
- effective session/cache/queue topology;
- production mail delivery/monitoring;
- centralized logs/metrics/alerts/on-call;
- actual provider deployment/migration/rollback mechanism;
- final critical production smoke/E2E against the exact deployed SHA.

These facts remain `UNKNOWN` until directly proven in the final production environment. `STAGING_PROVEN` or repository evidence does not promote them to `PRODUCTION_PROVEN`.

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

No Canary/login-server repository was modified by Phase 7 work, production-verification preparation or the E2E continuous-hardening tasks.

## Current active task

None.

Repository/staging E2E implementation hardening is closed through PR #111. Scheduled stability/soak workflows now accumulate further non-blocking evidence over time.

## Recommended next work

Do not add more repository/staging E2E solely to increase test count. Review scheduled repeat/soak evidence when it becomes available and promote thresholds only after measured variance justifies them.

Independently, resume issue #91 only when the exact final deployed production SHA, explicit production deployment/verification authorization and access to collect sanitized production evidence are available.

## High-priority remaining unknowns

- authoritative Platform game-login assertion/session protocol and rollout if required for launch scope;
- first three-iteration zero-retry critical stability result after PR #111;
- first bounded public soak latency/RSS/Redis-key baseline after PR #111;
- long-term Firefox/WebKit, resilience and accessibility flakiness beyond current exact-SHA evidence;
- deployed production edge/origin/network/TLS topology;
- production runtime Redis ACL/endpoint provisioning;
- production database, mail, session/cache and queue topology;
- production backup/restore/deployment/rollback mechanisms;
- centralized production logging/metrics/alerting;
- exact production Cloudflare Access/admin-hostname choice, if adopted;
- current Canary tournament-coin schema/code naming conflict.

Payments remain deferred.
