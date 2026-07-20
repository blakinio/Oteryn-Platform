# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase7-request-correlation-logging` — PR #55 — `task/OTERYN-20260720-phase7-request-correlation-logging`

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: IN PROGRESS**

## Completed Phase 7 repository-owned slices

- PR #48 / `676a77590e3ec93bcad0247b3065d203ac209c40` — production topology evidence baseline.
- PR #49 / `0f876d4f2209399a85cafcff1623d8e6c810b914` — provider-independent `production:verify-configuration` guardrails.
- PR #50 / `3973774727c35aea22d0a646f479a0ff079042cc` — required Composer advisory audit and bounded Dependabot updates.
- PR #54 / `eb358a245f35fda1865f13e329c07ef0f4850d2f` — CSP and browser security headers without unsafe inline/eval allowances.

## Current Phase 7 slice

PR #55 adds provider-neutral request correlation and structured logging primitives:

- a fresh server-generated UUID for every Laravel-handled request;
- inbound `X-Request-ID` is never trusted as the authoritative correlation identifier;
- normal responses expose the generated `X-Request-ID`;
- one `http.request.completed` log event records only request ID, HTTP method, route name, response status and bounded duration;
- request bodies, query strings, full URLs, headers and credentials are not included in that completion log context;
- optional `stderr_json` Monolog channel emits JSON to `php://stderr` without changing the universal default logging channel.

The implementation does not claim that a centralized log, metrics or alerting service is deployed.

CI #721 passed Composer audit, Pint, PHPStan and the full test suite after exact static-analysis/formatting fixes. Final synchronized exact-head validation remains required before merge.

## Phase 7 dependency status

- provider-independent runtime production-safety guardrails — **COMPLETE**;
- dependency vulnerability scanning/update automation — **COMPLETE**;
- security headers/CSP — **COMPLETE**;
- application request correlation/structured logging primitives — **IN PROGRESS**;
- edge/origin/database exposure review — **BLOCKED ON EXTERNAL DEPLOYMENT EVIDENCE**;
- backup/restore operational validation — **BLOCKED ON ACTUAL STORAGE/DB TOPOLOGY**;
- deployed centralized logging/metrics/alerting validation — **BLOCKED ON EXTERNAL DEPLOYMENT EVIDENCE**;
- queue/cache/mail deployment setup — topology/use-case dependent;
- critical deployed E2E matrix — blocked until exact deployment and authoritative game-login dependencies exist.

## Production enablement note

Repository Phase 7 progress is not proof of production deployment.

Cloudflare/WAF/Access, HSTS posture, private origin/database paths, backups, centralized monitoring and actual production endpoints remain `UNKNOWN` until external non-secret evidence proves them.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate from current Phase 7 work and requires explicit authorization before external repository writes.

## Recommended next work

Finish PR #55 with exact-head CI. If external deployment evidence is still unavailable after merge, add provider-neutral production readiness and incident/recovery runbooks that clearly separate executable repository checks from environment-evidence-required steps.

## Recently completed

- `OTERYN-20260720-phase7-security-headers-csp` — PR #54 / `eb358a245f35fda1865f13e329c07ef0f4850d2f`.
- `OTERYN-20260720-phase7-dependency-security-scanning` — PR #50 / `3973774727c35aea22d0a646f479a0ff079042cc`.
- `OTERYN-20260720-phase7-production-config-guardrails` — PR #49 / `0f876d4f2209399a85cafcff1623d8e6c810b914`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
