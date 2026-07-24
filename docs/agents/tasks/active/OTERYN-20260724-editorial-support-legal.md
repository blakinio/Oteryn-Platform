---
task_id: OTERYN-20260724-editorial-support-legal
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/PUBLIC_WEBSITE_EXPANSION_PLAN.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/contracts/PUBLIC_PORTAL_EXTENSION_CONTRACT.md
search_first:
  - docs/agents/tasks/active/**
  - open pull requests for CMS managed-page, editorial-route or support-content ownership
  - existing managed-page models, services, controllers, requests, views and tests
optional_reads: []
---

# OTERYN-20260724-editorial-support-legal

## Goal

Extend the existing CMS with typed editorial mappings, published-only public routes and a confirmed-MFA administrator workflow for launch Server Information, Beginner's Guide, support guidance, rules and required legal documents without introducing a second CMS, stored support tickets, arbitrary HTML or media uploads.

## Acceptance criteria

- [x] All eight required public routes use stable typed keys and deterministic published, unpublished and missing behavior.
- [x] Draft and future-scheduled content is never exposed publicly.
- [x] Legal documents require and preserve version plus effective-date history.
- [x] Administrator mutations require `auth`, `mfa.confirmed` and exact `support.content.manage` authorization.
- [x] Privileged mutations create bounded audit records without page bodies, personal data or secrets.
- [x] Approved Discord, contact and support links are configuration-backed and reject unsafe or unapproved external URLs.
- [x] No stored ticket submission path, arbitrary HTML, executable upload or media upload is introduced.
- [ ] Focused tests and all required CI pass on the exact task head.

## Ownership

```yaml
owned_paths:
  - .env.example
  - app/Cms/Editorial/**
  - app/Cms/Models/ManagedPage.php
  - app/Cms/Models/ManagedPageLegalVersion.php
  - app/Cms/Actions/SaveManagedPage.php
  - app/Http/Controllers/Support/**
  - app/Http/Controllers/Admin/AdminManagedPageController.php
  - app/Http/Controllers/Admin/AdminSupportContentController.php
  - app/Http/Controllers/Cms/PublicPageController.php
  - app/Http/Requests/Admin/AdminManagedPageRequest.php
  - app/Http/Requests/Admin/AdminSupportContentRequest.php
  - app/Support/**
  - config/support.php
  - database/migrations/*editorial_support_legal*
  - resources/navigation/public/support.php
  - resources/views/support/**
  - resources/views/admin/support-content/**
  - routes/modules/support.php
  - tests/Feature/Support/**
  - docs/agents/tasks/active/OTERYN-20260724-editorial-support-legal.md
modules:
  - CMS
  - Support
  - Admin
  - Audit
dependencies:
  - PR #143 merged
  - PR #146 merged
  - docs/contracts/PUBLIC_PORTAL_EXTENSION_CONTRACT.md
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T21:26:00Z
head: 196b4f4d56969f6f741bc876ee3a43383b8c8233
branch: feat/OTERYN-20260724-editorial-support-legal
pr: 159
status: validating
context_routes:
  - agent-governance
  - architecture
  - web-cms
  - admin-rbac
  - database
  - security
  - testing
owned_paths:
  - .env.example
  - app/Cms/Editorial/**
  - app/Cms/Models/ManagedPage.php
  - app/Cms/Models/ManagedPageLegalVersion.php
  - app/Cms/Actions/SaveManagedPage.php
  - app/Http/Controllers/Support/**
  - app/Http/Controllers/Admin/AdminManagedPageController.php
  - app/Http/Controllers/Admin/AdminSupportContentController.php
  - app/Http/Controllers/Cms/PublicPageController.php
  - app/Http/Requests/Admin/AdminManagedPageRequest.php
  - app/Http/Requests/Admin/AdminSupportContentRequest.php
  - app/Support/**
  - config/support.php
  - database/migrations/*editorial_support_legal*
  - resources/navigation/public/support.php
  - resources/views/support/**
  - resources/views/admin/support-content/**
  - routes/modules/support.php
  - tests/Feature/Support/**
  - docs/agents/tasks/active/OTERYN-20260724-editorial-support-legal.md
proven:
  - PR #143 is merged and provides the public website expansion plan.
  - PR #146 is merged and provides deterministic module-local route and navigation loading plus the reserved support.content.manage permission.
  - Existing CMS managed pages remain the sole editorial persistence boundary; no second CMS was introduced.
  - No open PR or main-branch active task owns CMS managed-page persistence, typed editorial routes or support-content administration.
  - Eight typed public routes resolve fixed managed-page keys and return distinct 404 missing or unpublished states without exposing draft content.
  - Generic managed-page administration and /pages/{slug} reject all reserved editorial slugs.
  - Published legal versions are preserved in additive immutable snapshots keyed by managed page and version.
  - Support administration composes auth, confirmed MFA and exact support.content.manage middleware and writes bounded audit metadata.
  - Support links are emitted only from validated configuration-backed email or HTTPS allowlisted hosts.
  - No support POST route, ticket model, arbitrary HTML field, executable upload or media upload exists in this change.
derived:
  - Fixed typed mappings reuse managed-page persistence while providing stable primary route contracts.
  - Immutable legal snapshots preserve historical meaning without requiring a parallel legal CMS.
unknown:
  - Required CI outcome on the complete pull-request head.
conflicts: []
first_failure:
  marker: local-checkout-unavailable
  evidence: sandbox DNS could not resolve github.com; repository reads and writes continued through the GitHub connector
rejected_hypotheses:
  - A second support CMS is necessary.
  - A stored support-ticket form is required for report-a-bug guidance.
  - Existing generic /pages/{slug} is sufficient as the launch route contract.
changed_paths:
  - .env.example
  - app/Cms/Actions/SaveManagedPage.php
  - app/Cms/Editorial/EditorialPageKey.php
  - app/Cms/Editorial/EditorialPageQuery.php
  - app/Cms/Editorial/EditorialPageResult.php
  - app/Cms/Editorial/EditorialPageState.php
  - app/Cms/Models/ManagedPage.php
  - app/Cms/Models/ManagedPageLegalVersion.php
  - app/Http/Controllers/Admin/AdminManagedPageController.php
  - app/Http/Controllers/Admin/AdminSupportContentController.php
  - app/Http/Controllers/Cms/PublicPageController.php
  - app/Http/Controllers/Support/EditorialPageController.php
  - app/Http/Controllers/Support/SupportPageController.php
  - app/Http/Requests/Admin/AdminManagedPageRequest.php
  - app/Http/Requests/Admin/AdminSupportContentRequest.php
  - app/Support/ApprovedSupportLinks.php
  - app/Support/PublicEditorialPage.php
  - config/support.php
  - database/migrations/2026_07_24_230000_add_editorial_support_legal_to_managed_pages.php
  - docs/agents/tasks/active/OTERYN-20260724-editorial-support-legal.md
  - resources/navigation/public/support.php
  - resources/views/admin/support-content/form.blade.php
  - resources/views/admin/support-content/index.blade.php
  - resources/views/support/editorial/show.blade.php
  - routes/modules/support.php
  - tests/Feature/Support/EditorialSupportLegalTest.php
validation:
  - command: overlap and precondition review against main and open pull requests
    result: PASS
    evidence: PR #143 and #146 merged; only unrelated draft PR #116 was open before task creation
  - command: find /tmp/oteryn_impl -type f -name '*.php' -print0 | xargs -0 -n1 php -l
    result: PASS
    evidence: all 24 staged implementation, route, config, view and test PHP files reported no syntax errors
  - command: local checkout and focused PHPUnit execution
    result: BLOCKED
    evidence: sandbox DNS could not resolve github.com; required repository tests are delegated to exact-head GitHub CI
blockers:
  - none
next_action: Reopen PR #159 and inspect required checks on the complete implementation head.
```

## Notes

The public missing and unpublished states intentionally return HTTP 404 while rendering distinct safe guidance; neither state receives draft title or body data. Legal snapshots are immutable per page/version, so changing published legal meaning requires a new version. Authoritative rates, rules and legal wording are not fabricated or seeded; the administrator workflow identifies the required launch topics and publishes only reviewed content.
