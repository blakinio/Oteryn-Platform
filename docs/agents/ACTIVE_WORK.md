# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase7-production-evidence-collection` — BLOCKED — `task/OTERYN-20260720-phase7-production-evidence-collection`

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: IN PROGRESS / EXTERNAL-EVIDENCE BLOCKED FOR COMPLETION**

## Completed Phase 7 repository-owned slices

- PR #48 / `676a77590e3ec93bcad0247b3065d203ac209c40` — production topology evidence baseline.
- PR #49 / `0f876d4f2209399a85cafcff1623d8e6c810b914` — provider-independent `production:verify-configuration` guardrails.
- PR #50 / `3973774727c35aea22d0a646f479a0ff079042cc` — required Composer advisory audit and bounded Dependabot updates.
- PR #54 / `eb358a245f35fda1865f13e329c07ef0f4850d2f` — CSP and browser security headers.
- PR #55 / `b6650966fe877a0e7872f29606b32b6394dde99f` — server-generated request correlation and bounded structured request-completion logging.
- PR #56 / `ae659089bb288dd467f5e2f163ffb7d731e35cec` — production-readiness checklist, incident/recovery runbook and Phase 7 continuation handover.

## Current blocker

The repository cannot prove the actual production environment. The active task therefore waits for sanitized, non-secret evidence covering the deployed application/edge/origin/database/Redis/mail/logging/backup/deployment topology.

The required next environment-dependent work is:

1. edge/TLS/origin/database exposure review against real deployment evidence;
2. effective Canary SQL and runtime Redis boundary verification where enabled;
3. deployment/rollback validation;
4. dated operational backup-restore test with measured recovery result;
5. remaining critical production E2E gates against exact deployed SHAs.

Until that evidence exists, do not mark Phase 7 COMPLETE or invent provider-specific deployment claims.

## Repository-verifiable preflight commands

```text
php artisan production:verify-configuration
php artisan canary:verify-db-privileges
php artisan canary:verify-provisioning-db-privileges
php artisan canary:verify-character-create-db-privileges
```

These commands prove only their documented boundaries.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate and requires explicit authorization before external repository writes if it is part of launch scope.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
