# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase7-production-topology-discovery` — PR #48 — `task/OTERYN-20260720-phase7-production-topology-discovery`

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: IN PROGRESS**

## Current Phase 7 slice

PR #48 establishes the production-topology evidence baseline before any provider-specific hardening or production-readiness claim.

Repository-proven facts include:

- provider-neutral logical target architecture with Cloudflare/edge, origin/reverse proxy and Laravel web tier;
- Laravel `/health` route;
- CI-only GitHub Actions workflow with no deployment step;
- Platform SQLite/MySQL configuration support;
- separate Canary read-only/provisioning/character-create SQL configuration surfaces;
- dedicated Canary runtime Redis configuration surface;
- environment-driven sessions with local file default;
- Platform cache stores currently limited to array/file/null;
- queue currently limited to synchronous execution;
- SMTP/log/array mail transports;
- single-file/stderr logging options.

The repository does **not** prove the actual production provider, Cloudflare policy, origin ingress restrictions, database/Redis endpoints, session/cache backend, queue/worker model, mail provider, monitoring sink, backup/restore implementation or deployment/rollback mechanism.

The evidence baseline is documented in `docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md` and explicitly forbids treating local `.env.example` defaults as production evidence.

## Phase 7 dependency order

1. provider-independent runtime production-safety guardrails;
2. actual edge/origin/database exposure review when deployment evidence exists;
3. backup/restore contract and operational test;
4. logging/monitoring/correlation;
5. dependency/security scanning and security headers/CSP;
6. queue/cache/mail setup only where deployment/use-case evidence proves need;
7. critical E2E matrix against exact deployed versions.

## Production enablement note

Repository phase progress is not proof of production deployment.

Cloudflare/WAF/Access, private origin/database paths, backups, centralized monitoring and real production mail remain `UNKNOWN` until non-secret deployment evidence proves them.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate from Phase 7 production topology discovery.

A future explicitly authorized cross-repository task must provide exact-account Platform authorization with short-lived cryptographically protected exchange material, explicit audience/expiry, replay-resistant consumption/session semantics and deterministic revocation/failure behavior.

## Recommended next work

After PR #48 merges, implement the smallest provider-independent production runtime verifier that fails closed on unsafe invariant configuration without assuming a hosting provider.

## Recently completed

- `OTERYN-20260720-phase6-closure` — PR #46 / `f25abd8799718ac99acce050ac55018d04fff2de`.
- `OTERYN-20260720-phase6-admin-cms-audit` — PR #45 / `be25d6ec3e0512bb9615329f99f16fff294d8b1d`.
- `OTERYN-20260720-phase6-admin-rbac-foundation` — PR #44 / `170d52393e543c8033ebd896f42fb43f3fccdf42`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
