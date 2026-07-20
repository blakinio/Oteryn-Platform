# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase7-security-headers-csp` — PR #54 — `task/OTERYN-20260720-phase7-security-headers-csp`

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: IN PROGRESS**

## Completed Phase 7 repository-owned slices

- PR #48 / `676a77590e3ec93bcad0247b3065d203ac209c40` — production topology evidence baseline.
- PR #49 / `0f876d4f2209399a85cafcff1623d8e6c810b914` — provider-independent `production:verify-configuration` guardrails.
- PR #50 / `3973774727c35aea22d0a646f479a0ff079042cc` — required Composer advisory audit and bounded Dependabot updates for Composer/GitHub Actions.

## Current Phase 7 slice

PR #54 adds browser security hardening:

- first-party inline public CSS moved to `public/css/app.css`;
- same-origin stylesheet used by public/game and administrator layouts;
- global Laravel `web` middleware adds an enforceable CSP;
- CSP permits same-origin scripts/styles/connect/fonts, self/data images and self form actions while denying objects, framing and base URI changes;
- no `unsafe-eval` or inline-script allowance;
- `X-Content-Type-Options: nosniff`;
- `X-Frame-Options: DENY`;
- `Referrer-Policy: strict-origin-when-cross-origin`;
- restrictive `Permissions-Policy` for camera/geolocation/microphone/payment/USB.

HSTS is intentionally not hard-coded because actual TLS termination, proxy and hostname/subdomain deployment topology remains unproven.

Feature tests cover the header boundary on public and authentication pages and verify that the CSP contains neither `unsafe-inline` nor `unsafe-eval`.

## Phase 7 dependency status

- provider-independent runtime production-safety guardrails — **COMPLETE**;
- dependency vulnerability scanning/update automation — **COMPLETE**;
- security headers/CSP — **IN PROGRESS**;
- edge/origin/database exposure review — **BLOCKED ON EXTERNAL DEPLOYMENT EVIDENCE**;
- backup/restore operational validation — **BLOCKED ON ACTUAL STORAGE/DB TOPOLOGY**;
- logging/monitoring/correlation — repository-owned application portion still available;
- queue/cache/mail deployment setup — topology/use-case dependent;
- critical deployed E2E matrix — blocked until exact deployment and authoritative game-login dependencies exist.

## Production enablement note

Repository Phase 7 progress is not proof of production deployment.

Cloudflare/WAF/Access, HSTS posture, private origin/database paths, backups, centralized monitoring and actual production endpoints remain `UNKNOWN` until external non-secret evidence proves them.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate from current Phase 7 work and requires explicit authorization before external repository writes.

## Recommended next work

Finish PR #54 with exact-head CI. If deployment evidence remains unavailable after merge, continue with provider-neutral request correlation/structured logging primitives without claiming a deployed monitoring sink.

## Recently completed

- `OTERYN-20260720-phase7-dependency-security-scanning` — PR #50 / `3973774727c35aea22d0a646f479a0ff079042cc`.
- `OTERYN-20260720-phase7-production-config-guardrails` — PR #49 / `0f876d4f2209399a85cafcff1623d8e6c810b914`.
- `OTERYN-20260720-phase7-production-topology-discovery` — PR #48 / `676a77590e3ec93bcad0247b3065d203ac209c40`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
