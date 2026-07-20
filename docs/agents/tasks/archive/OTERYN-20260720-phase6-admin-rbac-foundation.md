# OTERYN-20260720-phase6-admin-rbac-foundation

## Goal

Establish the smallest complete Phase 6 administrator/RBAC foundation: durable explicit roles and permissions, deny-by-default server-side authorization, and mandatory composition with existing Platform authentication and confirmed MFA before privileged routes.

## Acceptance criteria

- [x] Durable role, permission, role-permission, and identity-role assignment persistence exists with no administrator assigned by default.
- [x] Current privileged capabilities are represented by explicit permission keys; no wildcard or implicit unrestricted-admin bypass exists.
- [x] A reusable server-side permission middleware fails closed for missing identity, role, permission, or malformed state.
- [x] The first `/admin` route requires `auth`, `mfa.confirmed`, and explicit `admin.access` permission.
- [x] Feature tests prove unauthenticated, missing-MFA, missing-role, missing-permission, unknown-permission denial plus authorized access.
- [x] Admin module state and Phase 6 project state are synchronized without claiming later CMS/audit work complete.

## Ownership

```yaml
owned_paths:
  - app/Admin/**
  - app/Http/Middleware/RequireAdminPermission.php
  - bootstrap/app.php
  - database/migrations/*admin*rbac*.php
  - resources/views/admin/**
  - routes/web.php
  - tests/Feature/Admin/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase6-admin-rbac-foundation.md
  - docs/architecture/MODULE_CATALOG.md
modules:
  - Admin
  - Identity
dependencies:
  - Phase 3 Identity authentication and mfa.confirmed middleware
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T09:31:12Z
head: 170d52393e543c8033ebd896f42fb43f3fccdf42
branch: task/OTERYN-20260720-phase6-admin-rbac-foundation
pr: 44
status: completed
context_routes:
  - admin-rbac
  - security
  - architecture
owned_paths:
  - app/Admin/**
  - app/Http/Middleware/RequireAdminPermission.php
  - bootstrap/app.php
  - database/migrations/*admin*rbac*.php
  - resources/views/admin/**
  - routes/web.php
  - tests/Feature/Admin/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase6-admin-rbac-foundation.md
  - docs/architecture/MODULE_CATALOG.md
proven:
  - Phase 5 is complete and Phase 6 started with PR #44 as the first bounded slice.
  - No administrator role is assigned by the RBAC migration.
  - Current roles map only to explicit enumerated permissions; unknown permission keys fail closed.
  - Privileged routes compose auth, mfa.confirmed and admin.permission middleware independently.
  - Focused AdminAuthorizationTest passed after using the persisted web_session_generation in request fixtures.
  - Full CI run 593 passed formatting, PHPStan and the complete test suite on implementation head 09739f45ab05918c1ed0a2fcdafd60988d943396.
  - Exact-head CI run 598 and Agent Governance run 519 passed on 5729be1c443cfcac9a0cf16abef65475464847fc.
  - PR #44 was squash-merged to main as 170d52393e543c8033ebd896f42fb43f3fccdf42.
derived:
  - Privileged CMS mutation and role-management endpoints can now be added only behind explicit permission keys and confirmed MFA.
unknown: []
conflicts: []
first_failure:
  marker: AdminAuthorizationTest authorized route initially failed because the request fixture used an unrefreshed Identity model's database-default session generation.
  evidence: focused method isolation proved the authorization service passed while the authorized route failed; reloading persisted Identity session generation made the focused suite and full CI pass.
rejected_hypotheses:
  - A single boolean administrator flag is insufficient because SECURITY_ARCHITECTURE requires separation of content and account/security administration.
  - RBAC role-permission persistence was not the failure source; direct AdminAuthorization coverage passed.
changed_paths:
  - app/Admin/AdminAuthorization.php
  - app/Admin/AdminPermission.php
  - app/Http/Middleware/RequireAdminPermission.php
  - bootstrap/app.php
  - database/migrations/2026_07_20_091300_create_admin_rbac_tables.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase6-admin-rbac-foundation.md
  - docs/architecture/MODULE_CATALOG.md
  - resources/views/admin/dashboard.blade.php
  - routes/web.php
  - tests/Feature/Admin/AdminAuthorizationTest.php
validation:
  - command: focused RBAC Debug workflow run 8
    result: PASS
    evidence: AdminAuthorizationTest completed successfully; temporary diagnostic workflow was removed before final readiness.
  - command: CI run 593 on 09739f45ab05918c1ed0a2fcdafd60988d943396
    result: PASS
    evidence: formatting, static analysis and full tests succeeded.
  - command: exact-head CI run 598 and Agent Governance run 519 on 5729be1c443cfcac9a0cf16abef65475464847fc
    result: PASS
    evidence: required checks passed before squash merge.
blockers:
  - none
next_action: Continue Phase 6 through OTERYN-20260720-phase6-admin-cms-audit.
```

## Notes

This task intentionally created no CMS authoring, account-security mutation, role-management UI, audit query UI, upload surface, or cross-repository behavior.
