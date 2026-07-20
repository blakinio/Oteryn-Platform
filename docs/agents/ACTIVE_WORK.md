# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase7-production-evidence-collection` — IN PROGRESS — PR #63 — `task/OTERYN-20260720-phase7-production-evidence-collection`

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: IN PROGRESS / FINAL PRODUCTION EVIDENCE REQUIRED FOR COMPLETION**

## Completed Phase 7 repository-owned slices

- PR #48 / `676a77590e3ec93bcad0247b3065d203ac209c40` — production topology evidence baseline.
- PR #49 / `0f876d4f2209399a85cafcff1623d8e6c810b914` — provider-independent `production:verify-configuration` guardrails.
- PR #50 / `3973774727c35aea22d0a646f479a0ff079042cc` — required Composer advisory audit and bounded Dependabot updates.
- PR #54 / `eb358a245f35fda1865f13e329c07ef0f4850d2f` — CSP and browser security headers.
- PR #55 / `b6650966fe877a0e7872f29606b32b6394dde99f` — server-generated request correlation and bounded structured request-completion logging.
- PR #56 / `ae659089bb288dd467f5e2f163ffb7d731e35cec` — production-readiness checklist, incident/recovery runbook and Phase 7 continuation handover.

## Current Phase 7 validation slice

PR #63 adds a controlled production-like validation workflow intended to generate repeatable non-secret `STAGING_PROVEN` evidence for the staging-verifiable gates that do not require final production access.

The workflow is scoped to exact-SHA validation of:

1. clean deployment, migrations, rollback and redeploy in the controlled release model;
2. production configuration guardrails;
3. effective MariaDB least-privilege principals and fail-closed privilege drift;
4. runtime Redis ACL/read boundary and failure semantics;
5. SMTP delivery through a real test SMTP service and mail-unavailable behavior;
6. full regression coverage and running security/header/correlation checks;
7. measured backup/restore with integrity and restored-environment smoke validation.

Staging evidence must not be promoted to proof of final production state.

## Final production-only completion evidence

Phase 7 still requires direct evidence for facts that the controlled environment cannot prove, including final production DNS/edge/Cloudflare/TLS/origin/firewall state, actual production DB/Redis effective grants and network restrictions, production backup schedule and restore, production logging/monitoring sink, production mail provider/delivery monitoring, exact deployed production SHA(s) and final production smoke/E2E checks.

Until those facts are proven or eligible risks are explicitly owner-accepted, do not mark Phase 7 COMPLETE.

## Repository-verifiable preflight commands

```text
php artisan production:verify-configuration
php artisan canary:verify-db-privileges
php artisan canary:verify-provisioning-db-privileges
php artisan canary:verify-character-create-db-privileges
```

These commands prove only their documented boundaries and the environment in which they are executed.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate and requires explicit authorization before external repository writes if it is part of launch scope.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
