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

- [ ] All eight required public routes use stable typed keys and deterministic published, unpublished and missing behavior.
- [ ] Draft and future-scheduled content is never exposed publicly.
- [ ] Legal documents require and preserve version plus effective-date history.
- [ ] Administrator mutations require `auth`, `mfa.confirmed` and exact `support.content.manage` authorization.
- [ ] Privileged mutations create bounded audit records without page bodies, personal data or secrets.
- [ ] Approved Discord, contact and support links are configuration-backed and reject unsafe or unapproved external URLs.
- [ ] No stored ticket submission path, arbitrary HTML, executable upload or media upload is introduced.
- [ ] Focused tests and all required CI pass on the exact task head.

## Ownership

```yaml
owned_paths:
  - app/Cms/Editorial/**
  - app/Cms/Models/ManagedPage.php
  - app/Cms/Models/ManagedPageLegalVersion.php
  - app/Cms/Actions/SaveManagedPage.php
  - app/Http/Controllers/Support/**
  - app/Http/Controllers/Admin/AdminSupportContentController.php
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
updated_at: 2026-07-24T21:06:00Z
head: UNKNOWN
branch: feat/OTERYN-20260724-editorial-support-legal
pr: none
status: implementing
context_routes:
  - agent-governance
  - architecture
  - web-cms
  - admin-rbac
  - database
  - security
  - testing
owned_paths:
  - app/Cms/Editorial/**
  - app/Cms/Models/ManagedPage.php
  - app/Cms/Models/ManagedPageLegalVersion.php
  - app/Cms/Actions/SaveManagedPage.php
  - app/Http/Controllers/Support/**
  - app/Http/Controllers/Admin/AdminSupportContentController.php
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
  - Existing CMS managed pages provide plain-text persistence, published-only reads, confirmed-MFA administration and bounded transactional audit.
  - No open PR or main-branch active task owns CMS managed-page persistence, typed editorial routes or support-content administration.
derived:
  - Fixed typed mappings can reuse managed-page persistence without a second CMS or a generic public slug dependency.
  - Legal history requires an additive Platform-owned snapshot table because mutating only current managed-page columns would lose prior published meaning.
unknown:
  - Required CI outcome on the final task head.
conflicts: []
first_failure:
  marker: local-checkout-unavailable
  evidence: sandbox DNS could not resolve github.com; repository reads and writes continue through the GitHub connector
rejected_hypotheses:
  - A second support CMS is necessary.
  - A stored support-ticket form is required for report-a-bug guidance.
  - Existing generic /pages/{slug} is sufficient as the launch route contract.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-editorial-support-legal.md
validation:
  - command: overlap and precondition review against main and open pull requests
    result: PASS
    evidence: PR #143 and #146 merged; only unrelated draft PR #116 remains open
  - command: local checkout
    result: BLOCKED
    evidence: sandbox DNS could not resolve github.com
blockers:
  - none
next_action: Open the draft PR, then implement the typed CMS extension and focused regression tests.
```

## Notes

The public missing and unpublished states may differ in presentation but neither state may include draft title/body content. Legal snapshots are immutable per page/version; changing published legal meaning requires a new version.
