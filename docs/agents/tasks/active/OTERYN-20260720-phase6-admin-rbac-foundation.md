# OTERYN-20260720-phase6-admin-rbac-foundation

## Goal

Establish the smallest complete Phase 6 administrator/RBAC foundation: durable explicit roles and permissions, deny-by-default server-side authorization, and mandatory composition with existing Platform authentication and confirmed MFA before privileged routes.

## Acceptance criteria

- [ ] Durable role, permission, role-permission, and identity-role assignment persistence exists with no administrator assigned by default.
- [ ] Current privileged capabilities are represented by explicit permission keys; no wildcard or implicit unrestricted-admin bypass exists.
- [ ] A reusable server-side permission middleware fails closed for missing identity, role, permission, or malformed state.
- [ ] The first `/admin` route requires `auth`, `mfa.confirmed`, and explicit `admin.access` permission.
- [ ] Feature tests prove unauthenticated, missing-MFA, missing-role, and missing-permission denial plus authorized access.
- [ ] Admin module state and Phase 6 project state are synchronized without claiming later CMS/audit work complete.

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
updated_at: 2026-07-20T09:12:09Z
head: a379a51aaa42a9956ef3661fe2769b77d36ceb5b
branch: task/OTERYN-20260720-phase6-admin-rbac-foundation
pr: none
status: implementing
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
  - Phase 5 is complete and Phase 6 is NEXT on main.
  - No active task or open pull request overlaps Phase 6 work.
  - Existing privileged route composition requires auth plus explicit Phase 6 authorization plus mfa.confirmed.
  - Existing mfa.confirmed middleware fails closed when the authenticated Identity has no confirmed MFA.
derived:
  - The first Phase 6 slice must establish authorization before any privileged CMS mutation endpoint is introduced.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - A single boolean administrator flag is insufficient because SECURITY_ARCHITECTURE requires separation of content and account/security administration.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260720-phase6-admin-rbac-foundation.md
validation:
  - command: GitHub Actions on draft PR head
    result: NOT_RUN
    evidence: implementation not yet pushed
blockers:
  - none
next_action: Open the draft PR, then implement and validate the deny-by-default RBAC foundation.
```

## Notes

This task intentionally creates no CMS authoring, account-security mutation, role-management UI, audit query UI, upload surface, or cross-repository behavior.
