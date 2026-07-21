# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260721-cms-audit-regressions`
  - branch: `task/OTERYN-20260721-cms-audit-regressions`
  - issue: #72
  - goal: add focused CMS state-transition and privileged-audit sensitive-data regressions without changing product behavior unless a real defect is proven.

- `OTERYN-20260721-functional-visual-acceptance`
  - branch: `task/OTERYN-20260721-functional-visual-acceptance`
  - draft PR: #67
  - coordination: owns the separate live acceptance/visual harness and current #68-#70 evidence path; do not duplicate its workflow/scripts ownership here.

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: COMPLETE**

## Operational release state

- **Production Readiness: STAGING_PROVEN**
- **Production Go-Live Gate: PENDING PRODUCTION VERIFICATION**
- **Production Verification: REQUIRED BEFORE GO-LIVE**

ADR 0007 separates Phase 7 engineering/hardening completion from final production go-live verification. No production-specific `UNKNOWN` is promoted by this status change.

## Completed Phase 7 repository-owned slices

- PR #48 / `676a77590e3ec93bcad0247b3065d203ac209c40` — production topology evidence baseline.
- PR #49 / `0f876d4f2209399a85cafcff1623d8e6c810b914` — provider-independent `production:verify-configuration` guardrails.
- PR #50 / `3973774727c35aea22d0a646f479a0ff079042cc` — required Composer advisory audit and bounded Dependabot updates.
- PR #54 / `eb358a245f35aea22d0a646f479a0ff079042cc` — CSP and browser security headers.
- PR #55 / `b6650966fe877a0e7872f29606b32b6394dde99f` — server-generated request correlation and bounded structured request-completion logging.
- PR #56 / `ae659089bb288dd467f5e2f163ffb7d731e35cec` — production-readiness checklist, incident/recovery runbook and Phase 7 continuation handover.
- PR #63 / `61f72ddda5c253f26c7d59aa7b6fce3506f120dc` — controlled production-like validation harness and staging evidence closure.
- PR #73 / `06d8d94aafd73de996eb4ea93705e8a45fbadafb` — controlled Platform DB outage validation; issue #71 closed with `STAGING_PROVEN` staging-only evidence.

## Production-like Phase 7 validation

Final PR #63 head evidence:

- validation SHA: `7842f78ec4ac2d07d3800ffe8bde9809b055822d`;
- rollback SHA: `b6878c4775eda542738c78ea99fd5d2e19d2b35f`;
- Phase 7 Production-Like Validation run `29779554130` / #9: PASS;
- required CI run `29779553687` / #759: PASS;
- Agent Governance run `29779554188` / #679: PASS;
- measured controlled restore: `105 ms`, `13/13` tables, `11/11` migrations, validation-SHA probe matched;
- classification: `STAGING_PROVEN` only.

Additional functional failure-path evidence:

- Platform DB outage exact-SHA workflow: PASS on PR #73;
- no false-success registration mutation with unavailable Platform DB;
- Platform state unchanged;
- bounded response/log sensitive-data checks passed;
- normal DB-backed recovery read passed;
- classification: `STAGING_PROVEN` for that controlled staging failure path only.

Staging evidence must not be promoted to proof of final production state or production RTO/RPO.

## Production Go-Live Gate

The authoritative fail-closed gate is `docs/operations/PRODUCTION_READINESS_CHECKLIST.md`.

It requires direct production evidence for:

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

These facts remain `UNKNOWN` until directly proven. The gate cannot become `PASS` from `REPO-PROVEN` or `STAGING_PROVEN` evidence alone.

## Next work

Complete `OTERYN-20260721-cms-audit-regressions` / issue #72. In parallel, PR #67 retains ownership of the separate #68-#70 live acceptance evidence path.

After both bounded paths are resolved, perform one final functional-acceptance reconciliation against `main` and update the durable matrix from direct merged evidence only.

When all staging functional-acceptance gaps are closed and actual production access plus deployment authorization are available, create a bounded production-verification task and execute the fail-closed Production Go-Live Gate against the exact deployed SHA(s).

## Repository-verifiable preflight commands

```text
php artisan production:verify-configuration
php artisan canary:verify-db-privileges
php artisan canary:verify-provisioning-db-privileges
php artisan canary:verify-character-create-db-privileges
```

These commands prove only their documented boundaries and the environment in which they are executed.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate and requires explicit authorization before external repository writes if it is part of launch scope. If required for launch, the Production Go-Live Gate remains blocked until it is resolved and proven.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
