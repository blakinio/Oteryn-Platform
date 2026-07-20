# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase7-production-readiness-runbooks` — PR #56 — `task/OTERYN-20260720-phase7-production-readiness-runbooks`

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: IN PROGRESS / EXTERNAL-EVIDENCE BLOCKED FOR COMPLETION**

## Completed Phase 7 repository-owned slices

- PR #48 / `676a77590e3ec93bcad0247b3065d203ac209c40` — production topology evidence baseline.
- PR #49 / `0f876d4f2209399a85cafcff1623d8e6c810b914` — provider-independent `production:verify-configuration` guardrails.
- PR #50 / `3973774727c35aea22d0a646f479a0ff079042cc` — required Composer advisory audit and bounded Dependabot updates.
- PR #54 / `eb358a245f35fda1865f13e329c07ef0f4850d2f` — CSP and browser security headers.
- PR #55 / `b6650966fe877a0e7872f29606b32b6394dde99f` — server-generated request correlation and bounded structured request-completion logging.

## Current Phase 7 slice

PR #56 adds provider-neutral production operations documentation:

- `docs/operations/PRODUCTION_READINESS_CHECKLIST.md` maps repository-verifiable controls to concrete commands and separates them from `ENV-EVIDENCE-REQUIRED`/`CROSS-REPO-BLOCKED` gates;
- `docs/operations/INCIDENT_RECOVERY_RUNBOOK.md` provides safe decision order for configuration, identity/admin, Canary SQL privilege, runtime Redis, mail, logging, deployment, database restore and shared-write incidents without inventing provider commands;
- `docs/agents/handovers/OTERYN-20260720-phase7-handover.md` records merged Phase 7 work, unresolved environment evidence and one next action.

This task intentionally does **not** mark Phase 7 complete.

## Phase 7 completion blockers

The repository cannot prove these without sanitized evidence from the actual environment:

- production Cloudflare/DNS/WAF/Access/TLS/HSTS posture;
- direct-origin exposure and ingress firewall rules;
- production Platform DB topology/network isolation;
- effective Canary SQL production endpoints/credential provisioning;
- runtime Redis endpoint/ACL/network/TLS state;
- session/cache scaling model;
- queue/worker model;
- production mail provider/domain/delivery monitoring;
- centralized logs/metrics/alerts and retention/on-call routing;
- deployment/migration/rollback mechanism;
- backup policy and a dated successful operational restore test.

The authoritative Platform game-login bridge is also unresolved if Platform-originated game login is part of launch scope.

## Repository-verifiable preflight commands

```text
php artisan production:verify-configuration
php artisan canary:verify-db-privileges
php artisan canary:verify-provisioning-db-privileges
php artisan canary:verify-character-create-db-privileges
```

These commands prove only their documented boundaries; they do not prove the rest of the production topology.

## Recommended next work

After PR #56 merges and housekeeping archives the task, obtain sanitized evidence for the actual production topology, then perform the edge/origin/database exposure review and dated backup-restore operational test required by the Phase 7 exit gate.

Until that evidence exists, do not create provider-specific production claims or mark Phase 7 COMPLETE.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate and requires explicit authorization before external repository writes.

## Recently completed

- `OTERYN-20260720-phase7-request-correlation-logging` — PR #55 / `b6650966fe877a0e7872f29606b32b6394dde99f`.
- `OTERYN-20260720-phase7-security-headers-csp` — PR #54 / `eb358a245f35fda1865f13e329c07ef0f4850d2f`.
- `OTERYN-20260720-phase7-dependency-security-scanning` — PR #50 / `3973774727c35aea22d0a646f479a0ff079042cc`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
