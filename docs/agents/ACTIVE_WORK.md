# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260721-phase7-go-live-gate-separation` — IN PROGRESS — `task/OTERYN-20260721-phase7-go-live-gate-separation`
- `OTERYN-20260720-phase7-production-evidence-collection` — BLOCKED ON FINAL PRODUCTION-ONLY EVIDENCE — staging-validation PR #63 MERGED as `61f72ddda5c253f26c7d59aa7b6fce3506f120dc`

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: IN PROGRESS / COMPLETION SEMANTICS UNDER BOUNDED ARCHITECTURE REVIEW**

## Completed Phase 7 repository-owned slices

- PR #48 / `676a77590e3ec93bcad0247b3065d203ac209c40` — production topology evidence baseline.
- PR #49 / `0f876d4f2209399a85cafcff1623d8e6c810b914` — provider-independent `production:verify-configuration` guardrails.
- PR #50 / `3973774727c35aea22d0a646f479a0ff079042cc` — required Composer advisory audit and bounded Dependabot updates.
- PR #54 / `eb358a245f35fda1865f13e329c07ef0f4850d2f` — CSP and browser security headers.
- PR #55 / `b6650966fe877a0e7872f29606b32b6394dde99f` — server-generated request correlation and bounded structured request-completion logging.
- PR #56 / `ae659089bb288dd467f5e2f163ffb7d731e35cec` — production-readiness checklist, incident/recovery runbook and Phase 7 continuation handover.
- PR #63 / `61f72ddda5c253f26c7d59aa7b6fce3506f120dc` — controlled production-like validation harness and staging evidence closure.

## Production-like Phase 7 validation

PR #63 implemented and successfully executed the controlled production-like validation workflow before squash merge.

Final PR-head evidence snapshot:

- validation SHA: `7842f78ec4ac2d07d3800ffe8bde9809b055822d`;
- rollback SHA: `b6878c4775eda542738c78ea99fd5d2e19d2b35f`;
- Phase 7 Production-Like Validation run `29779554130` / #9: PASS;
- required CI run `29779553687` / #759: PASS;
- Agent Governance run `29779554188` / #679: PASS;
- measured controlled restore: `105 ms`, `13/13` tables, `11/11` migrations, validation-SHA probe matched;
- classification: `STAGING_PROVEN` only.

The controlled validation closes the currently staging-verifiable deployment/rollback, configuration, DB privilege, Redis ACL, SMTP, critical-flow regression, security-smoke and backup/restore work. Detailed durable evidence and limitations are recorded in `docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md`; the final PR body records the exact-head run #9 artifact and digest.

Staging evidence must not be promoted to proof of final production state or production RTO/RPO.

## Current architecture review

The current ROADMAP makes `production readiness checklist complete` a Phase 7 exit gate, while the checklist itself requires actual deployed-production evidence. The bounded task `OTERYN-20260721-phase7-go-live-gate-separation` is deciding whether Phase 7 engineering completion should be separated durably from a fail-closed Production Go-Live Gate. Until that decision is merged, do not change Phase 7 completion status or weaken the production checklist.

## Final production-only completion evidence

The current production-only pass is maintained in `docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md` and includes:

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

None of those items becomes `PRODUCTION_PROVEN` from staging evidence.

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
