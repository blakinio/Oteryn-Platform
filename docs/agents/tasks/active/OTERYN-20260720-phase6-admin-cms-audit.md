# OTERYN-20260720-phase6-admin-cms-audit

## Goal

Complete the remaining Phase 6 privileged vertical slice on top of merged PR #44: safe first-admin bootstrap, audited role assignment, privileged plain-text news/page management, append-oriented administrator audit visibility, and Cloudflare Access deployment guidance.

## Acceptance criteria

- [ ] A one-time console bootstrap can assign the first `platform_admin` only when no administrator role assignment exists and the target Identity has confirmed MFA.
- [ ] Role assignment/removal is permission-protected, audited, transactional, and cannot remove the final `platform_admin` assignment.
- [ ] News management supports create/update publication state behind `cms.news.manage`, confirmed MFA and auth; public published-only behavior remains intact.
- [ ] Managed pages have Platform-owned persistence, published-only public reads, privileged create/update management behind `cms.pages.manage`, and escaped plain-text rendering.
- [ ] Administrator privileged mutations append audit events without secrets; `audit.view` exposes a bounded audit query surface.
- [ ] Privileged actions are covered by authorization/audit regression tests and all current admin routes remain deny-by-default.
- [ ] Cloudflare Access is documented as an optional production outer gate that never replaces application auth/MFA/RBAC.
- [ ] No arbitrary code/plugin upload, rich HTML, media upload, Canary mutation, or cross-repository change is introduced.

## Ownership

```yaml
owned_paths:
  - app/Admin/**
  - app/Audit/**
  - app/Cms/**
  - app/Http/Controllers/Admin/**
  - app/Http/Controllers/Cms/**
  - app/Http/Requests/Admin/**
  - database/migrations/*admin_audit*.php
  - database/migrations/*managed_pages*.php
  - docs/operations/**
  - resources/views/admin/**
  - resources/views/pages/**
  - routes/console.php
  - routes/web.php
  - tests/Feature/Admin/**
  - tests/Feature/Cms/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase6-admin-cms-audit.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase6-admin-rbac-foundation.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/ROADMAP.md
modules:
  - Admin
  - Audit
  - CMS
dependencies:
  - PR #44 / 170d52393e543c8033ebd896f42fb43f3fccdf42
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T09:31:29Z
head: 170d52393e543c8033ebd896f42fb43f3fccdf42
branch: task/OTERYN-20260720-phase6-admin-cms-audit
pr: none
status: implementing
context_routes:
  - admin-rbac
  - web-cms
  - security
  - database
  - architecture
owned_paths:
  - app/Admin/**
  - app/Audit/**
  - app/Cms/**
  - app/Http/Controllers/Admin/**
  - app/Http/Controllers/Cms/**
  - app/Http/Requests/Admin/**
  - database/migrations/*admin_audit*.php
  - database/migrations/*managed_pages*.php
  - docs/operations/**
  - resources/views/admin/**
  - resources/views/pages/**
  - routes/console.php
  - routes/web.php
  - tests/Feature/Admin/**
  - tests/Feature/Cms/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase6-admin-cms-audit.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase6-admin-rbac-foundation.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/ROADMAP.md
proven:
  - PR #44 merged the durable explicit RBAC schema, permission registry, fail-closed admin.permission middleware and protected /admin route.
  - Administrator authorization is independent from and composed with existing auth and mfa.confirmed gates.
  - Existing CMS news persistence and published-only public read boundary already exist on main.
  - No administrator is assigned by default.
derived:
  - Remaining Phase 6 privileged mutations must record audit events inside the same transaction as their Platform-owned state mutation where practical.
  - First-admin bootstrap must be a narrowly constrained console-only operation because no web-authorized administrator exists before the first assignment.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - A permanent unrestricted console role-grant command would bypass RBAC and is not acceptable; bootstrap must close after the first assignment exists.
  - Rich HTML/media upload is not required to satisfy Phase 6 and would introduce a separate sanitizer/upload threat surface.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260720-phase6-admin-cms-audit.md
validation:
  - command: GitHub Actions on draft PR head
    result: NOT_RUN
    evidence: implementation not yet pushed
blockers:
  - none
next_action: Open a draft PR and implement the audited privileged Phase 6 vertical slice.
```

## Notes

All CMS content in this task remains plain text and escaped on output. The authoritative game-login bridge remains out of scope.
