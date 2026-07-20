# OTERYN-20260720-phase6-admin-cms-audit

## Goal

Complete the remaining Phase 6 privileged vertical slice on top of merged PR #44: safe first-admin bootstrap, audited role assignment, privileged plain-text news/page management, append-oriented administrator audit visibility, and Cloudflare Access deployment guidance.

## Acceptance criteria

- [x] A one-time console bootstrap can assign the first `platform_admin` only when no administrator role assignment exists and the target Identity has confirmed MFA.
- [x] Role assignment/removal is permission-protected, audited, transactional, and cannot remove the final `platform_admin` assignment.
- [x] News management supports create/update publication state behind `cms.news.manage`, confirmed MFA and auth; public published-only behavior remains intact.
- [x] Managed pages have Platform-owned persistence, published-only public reads, privileged create/update management behind `cms.pages.manage`, and escaped plain-text rendering.
- [x] Administrator privileged mutations append audit events without secrets; `audit.view` exposes a bounded audit query surface.
- [x] Privileged actions are covered by authorization/audit regression tests and all current admin routes remain deny-by-default.
- [x] Cloudflare Access is documented as an optional production outer gate that never replaces application auth/MFA/RBAC.
- [x] No arbitrary code/plugin upload, rich HTML, media upload, Canary mutation, or cross-repository change is introduced.

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
  - docs/architecture/adr/0006-admin-rbac-and-audit-policy.md
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
updated_at: 2026-07-20T09:56:00Z
head: 640aa6cea8f6c798118a63186e396cc3fc7e56e2
branch: task/OTERYN-20260720-phase6-admin-cms-audit
pr: 45
status: validating
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
  - docs/architecture/adr/0006-admin-rbac-and-audit-policy.md
proven:
  - PR #44 merged the durable explicit RBAC schema, permission registry, fail-closed admin.permission middleware and protected /admin route as 170d52393e543c8033ebd896f42fb43f3fccdf42.
  - Administrator authorization is independent from and composed with existing auth and mfa.confirmed gates.
  - First-admin bootstrap is console-only, requires an existing MFA-confirmed Identity, serializes through the role boundary and closes after the first administrator assignment exists.
  - Role assignment/removal requires admin.roles.manage at the web boundary, is transactional and audited, and supported removal refuses to delete the final platform_admin assignment.
  - News management requires cms.news.manage; managed-page management requires cms.pages.manage; both also require auth and confirmed MFA.
  - Managed-page public reads expose only published pages and Blade output remains escaped plain text.
  - Bootstrap, role and CMS state-changing operations append minimal administrator audit events without credentials.
  - Administrator audit visibility requires audit.view plus auth and confirmed MFA and paginates at 50 records per page.
  - ADR 0006 records the durable explicit-permission RBAC and audit policy with no wildcard administrator authorization.
  - Cloudflare Access is documented only as an optional outer gate and never as an application authorization substitute.
  - Full CI run 639 passed Composer install, Pint, PHPStan and the complete test suite on implementation head 5688edccefe90a4eb62334369155aa263f0c797c.
  - Exact-head CI run 647 and Agent Governance run 568 passed on synchronized head 640aa6cea8f6c798118a63186e396cc3fc7e56e2.
  - PR #45 has no comments and no review threads; its changed-file list is restricted to the Phase 6 task scope.
derived:
  - PR #45 is merge-ready after the final docs-only head receives required checks; after merge, a separate closure revalidation can evaluate the Phase 6 exit gate on live main.
unknown: []
conflicts: []
first_failure:
  marker: Initial PR #45 CI failed during Composer package discovery because global exception use statements in routes/console.php were promoted to ErrorException; a later run then exposed three Pint-only style mismatches.
  evidence: dedicated Phase 6 diagnostic workflow artifacts identified the package-discovery warning exactly and supplied Pint-formatted versions of the three reported files; after both fixes CI run 639 passed bootstrap, formatting, static analysis and tests.
rejected_hypotheses:
  - A permanent unrestricted console role-grant command would bypass RBAC and is not acceptable; bootstrap closes after the first assignment exists.
  - Rich HTML/media upload is not required to satisfy Phase 6 and would introduce a separate sanitizer/upload threat surface.
  - Cloudflare Access is not administrator authorization and cannot replace auth, confirmed MFA or RBAC.
  - platform_admin is not a wildcard; future permissions require explicit role-permission decisions.
changed_paths:
  - app/Admin/**
  - app/Audit/AdminAuditRecorder.php
  - app/Cms/**
  - app/Http/Controllers/Admin/**
  - app/Http/Controllers/Cms/PublicPageController.php
  - app/Http/Requests/Admin/**
  - database/migrations/2026_07_20_093300_create_admin_audit_events_table.php
  - database/migrations/2026_07_20_093400_create_managed_pages_table.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase6-admin-cms-audit.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase6-admin-rbac-foundation.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/adr/0006-admin-rbac-and-audit-policy.md
  - docs/operations/CLOUDFLARE_ACCESS_ADMIN.md
  - resources/views/admin/**
  - resources/views/pages/show.blade.php
  - routes/console.php
  - routes/web.php
  - tests/Feature/Admin/**
  - tests/Feature/Cms/AdminCmsManagementTest.php
validation:
  - command: dedicated Phase 6 bootstrap diagnostic workflow
    result: PASS
    evidence: package discovery passed after removing ineffective global exception imports; temporary diagnostic workflow was later removed.
  - command: dedicated Phase 6 Pint diagnostic workflow
    result: PASS
    evidence: exact Pint-formatted versions of the three reported files were applied; temporary diagnostic workflow was later removed.
  - command: CI run 639 on 5688edccefe90a4eb62334369155aa263f0c797c
    result: PASS
    evidence: Composer validation/install, Pint, PHPStan and the complete test suite succeeded.
  - command: CI run 647 and Agent Governance run 568 on 640aa6cea8f6c798118a63186e396cc3fc7e56e2
    result: PASS
    evidence: exact synchronized-head required checks passed before the final task-record evidence update.
  - command: final exact-head CI and Agent Governance after this task-record-only update
    result: NOT_RUN
    evidence: required before readiness and merge.
blockers:
  - none
next_action: Verify required checks on the final docs-only head, mark PR #45 ready, and squash-merge if the merge gate remains satisfied.
```

## Notes

All CMS content in this task remains plain text and escaped on output. The authoritative game-login bridge remains out of scope.
