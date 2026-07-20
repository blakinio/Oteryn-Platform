# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

None.

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: NEXT / PLANNED**

## Proven Phase 6 implementation state

PR #44 merged as `170d52393e543c8033ebd896f42fb43f3fccdf42` and established the deny-by-default RBAC foundation:

- explicit roles and permissions;
- no administrator assignment by default;
- no wildcard or implicit unrestricted-admin path;
- `auth` + `mfa.confirmed` + exact `admin.permission:*` composition;
- authorization regression coverage.

PR #45 merged as `be25d6ec3e0512bb9615329f99f16fff294d8b1d` and completed the planned privileged Phase 6 vertical slice:

- one-time MFA-confirmed first-admin bootstrap;
- audited transactional role assignment/removal with final-`platform_admin` protection;
- permission-scoped plain-text news and managed-page administration;
- published-only public managed pages with escaped rendering;
- append-oriented administrator audit events;
- bounded permission-scoped audit visibility;
- ADR 0006 RBAC/audit policy;
- optional Cloudflare Access deployment documentation.

PR #46 merged as `f25abd8799718ac99acce050ac55018d04fff2de` and closed Phase 6 after merged-main revalidation.

Final exact-head validation before merge:

- PR #44: CI #598 and Agent Governance #519 passed;
- PR #45: CI #648 and Agent Governance #569 passed;
- PR #46: CI #659 and Agent Governance #580 passed.

## Phase 6 exit gate

Satisfied:

- administrator authorization is deny by default and unknown permissions fail closed;
- every current admin web capability requires Platform authentication, confirmed MFA and an exact explicit permission;
- privileged role, CMS and audit paths have authorization/MFA regression coverage;
- delivered administrator state-changing actions append administrator audit events;
- no arbitrary code/plugin upload, rich HTML or media upload feature exists in the delivered Phase 6 surface.

Phase 6 changes are Platform-only. Canary/login-server credentials, sessions, schema and game-login behavior are unchanged.

## Production enablement note

Repository phase completion is not proof of production deployment.

To enable the first administrator in an environment after Platform migrations are applied:

1. create or use an existing Platform Identity;
2. complete and confirm Platform MFA for that Identity;
3. run the one-time `php artisan admin:bootstrap <email>` command;
4. manage later role assignments only through the permission-protected administrator role surface.

Cloudflare Access remains optional defense in depth. The repository does not claim that it is deployed or that a production administrator hostname/path has been chosen.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate from completed Phase 5 and Phase 6.

A future explicitly authorized cross-repository task must provide exact-account Platform authorization with short-lived cryptographically protected exchange material, explicit audience/expiry, replay-resistant consumption/session semantics and deterministic revocation/failure behavior.

Expected primary external scope remains `opentibiabr/login-server`; `blakinio/canary` changes require separate explicit authorization if the selected protocol needs them.

## Recommended next work

Start the smallest Phase 7 production-hardening discovery task by proving the actual deployed application/edge/origin/database/cache/queue/mail topology before making production-readiness changes or claims.

## Recently completed

- `OTERYN-20260720-phase6-closure` — PR #46 / `f25abd8799718ac99acce050ac55018d04fff2de`; task archived by post-merge housekeeping.
- `OTERYN-20260720-phase6-admin-cms-audit` — PR #45 / `be25d6ec3e0512bb9615329f99f16fff294d8b1d`.
- `OTERYN-20260720-phase6-admin-rbac-foundation` — PR #44 / `170d52393e543c8033ebd896f42fb43f3fccdf42`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
