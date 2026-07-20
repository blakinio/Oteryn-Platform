# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase7-production-evidence-collection` — BLOCKED ON FINAL PRODUCTION-ONLY EVIDENCE — PR #63 — `task/OTERYN-20260720-phase7-production-evidence-collection`

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

## Production-like Phase 7 validation

PR #63 implemented and successfully executed the controlled production-like validation workflow.

Evidence snapshot:

- validation SHA: `b6dcd6ed95c55f400206864ffd6ff799e65aa2b3`;
- rollback SHA: `b6878c4775eda542738c78ea99fd5d2e19d2b35f`;
- Phase 7 Production-Like Validation run `29779031870` / #5: PASS;
- required CI run `29779031976` / #755: PASS;
- Agent Governance run `29779031673` / #675: PASS;
- measured controlled restore: `102 ms`, `13/13` tables, `11/11` migrations, validation-SHA probe matched;
- classification: `STAGING_PROVEN` only.

The controlled validation closes the currently staging-verifiable deployment/rollback, configuration, DB privilege, Redis ACL, SMTP, critical-flow regression, security-smoke and backup/restore work. Detailed evidence and limitations are recorded in `docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md`.

Staging evidence must not be promoted to proof of final production state or production RTO/RPO.

## Final production-only completion evidence

Phase 7 now waits only for facts requiring the actual final production environment or separately authorized scope. The minimal pass is maintained in `docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md` and includes:

1. exact deployed production SHA(s) and relevant Canary/login-server versions;
2. production DNS/edge/Cloudflare/TLS/origin/firewall state;
3. actual production Platform/Canary DB topology, network isolation and effective grants;
4. actual production runtime Redis endpoint/ACL/network/TLS state;
5. effective production session/cache/queue topology;
6. production mail provider/domain/delivery monitoring;
7. centralized production logs/metrics/alerts/retention/on-call routing;
8. actual provider deployment/migration/rollback mechanism and operator authorization;
9. production backup schedule/policy and dated production restore result;
10. final production critical smoke/E2E checks against the exact deployed SHA.

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
