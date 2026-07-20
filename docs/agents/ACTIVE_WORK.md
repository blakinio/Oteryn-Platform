# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase7-dependency-security-scanning` — PR #50 — `task/OTERYN-20260720-phase7-dependency-security-scanning`

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: IN PROGRESS**

## Completed Phase 7 slices

PR #48 merged as `676a77590e3ec93bcad0247b3065d203ac209c40` and established `docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md`.

PR #49 merged as `0f876d4f2209399a85cafcff1623d8e6c810b914` and added provider-independent production configuration guardrails exposed through:

`php artisan production:verify-configuration`

The verifier checks production mode, debug state, APP_KEY presence, HTTPS/non-loopback APP_URL, Secure/HttpOnly session cookies and delivery-capable mail configuration without printing secret values.

## Current Phase 7 slice

PR #50 adds repository-owned dependency security controls:

- required CI step `composer audit --no-interaction` after lockfile installation;
- existing Composer validation, Pint, PHPStan and test gates remain unchanged;
- bounded weekly Dependabot update PRs for Composer;
- bounded weekly Dependabot update PRs for GitHub Actions.

Dependabot update automation complements but does not replace the fail-closed Composer advisory gate.

This task does not upgrade dependencies directly.

## Phase 7 dependency order

1. provider-independent runtime production-safety guardrails — **COMPLETE**;
2. actual edge/origin/database exposure review — **BLOCKED ON EXTERNAL DEPLOYMENT EVIDENCE**;
3. backup/restore contract and operational test — **BLOCKED ON ACTUAL STORAGE/DB TOPOLOGY**;
4. logging/monitoring/correlation;
5. dependency/security scanning — **IN PROGRESS**;
6. security headers/CSP review;
7. queue/cache/mail setup only where deployment/use-case evidence proves need;
8. critical E2E matrix against exact deployed versions.

## Production enablement note

Repository Phase 7 progress is not proof of production deployment.

Cloudflare/WAF/Access, private origin/database paths, backups, centralized monitoring and actual production endpoints remain `UNKNOWN` until external non-secret evidence proves them.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate from current Phase 7 work and requires explicit authorization before external repository writes.

## Recommended next work

Finish PR #50 with exact-head CI. If deployment evidence remains unavailable after merge, continue with the repository-owned security headers/CSP slice.

## Recently completed

- `OTERYN-20260720-phase7-production-config-guardrails` — PR #49 / `0f876d4f2209399a85cafcff1623d8e6c810b914`.
- `OTERYN-20260720-phase7-production-topology-discovery` — PR #48 / `676a77590e3ec93bcad0247b3065d203ac209c40`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
