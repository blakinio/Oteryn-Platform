---
task_id: OTERYN-20260724-wiki-foundation
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/WIKI_IMPLEMENTATION_PLAN.md
  - docs/architecture/PUBLIC_WEBSITE_EXPANSION_PLAN.md
  - docs/architecture/SYSTEM_ARCHITECTURE.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/architecture/TEST_STRATEGY.md
search_first:
  - active Wiki or CMS tasks and open pull requests
  - existing CMS models, services, factories and migrations
  - existing exact-permission, audit and optimistic-locking conventions
  - relevant CMS, RBAC, audit and production-migration ADRs
optional_reads: []
---

# OTERYN-20260724-wiki-foundation

## Goal

Implement only the Oteryn Wiki architecture and persistence foundation: Wiki-owned schema, models, lifecycle and revision invariants, exact authorization boundaries, bounded audit definitions, focused tests and a durable ADR. Public activation remains out of scope.

## Acceptance criteria

- [ ] Reversible migrations create `wiki_articles`, `wiki_article_translations`, `wiki_categories`, `wiki_category_translations`, `wiki_article_category` and `wiki_revisions` without assuming an empty database.
- [ ] Supported locale, localized slug uniqueness, lifecycle, publication-content, append-only revision, restore-as-new-revision and stale-edit invariants are enforced and tested.
- [ ] Exact reserved permissions are used deny-by-default; no wildcard or implicit role grant is introduced.
- [ ] Wiki audit metadata is bounded and excludes complete article bodies.
- [ ] No public Wiki route, navigation link, media upload, arbitrary HTML, search service, comments or player editing is activated.
- [ ] Wiki ADR and module implementation status are current.
- [ ] Formatting, PHPStan, focused/full tests and required CI pass on the exact PR head, or an external blocker is recorded precisely.

## Ownership

```yaml
owned_paths:
  - app/Wiki/**
  - app/Providers/WikiServiceProvider.php
  - routes/wiki.php
  - database/migrations/*_create_wiki_*.php
  - database/factories/Wiki/**
  - tests/Unit/Wiki/**
  - tests/Feature/Wiki/**
  - docs/architecture/adr/*wiki*.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/agents/tasks/active/OTERYN-20260724-wiki-foundation.md
modules:
  - Wiki
  - Admin/RBAC
  - Audit
  - Database
dependencies:
  - PR #142 Wiki architecture plan
  - PR #146 public-web parallel foundation and reserved Wiki permission keys
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T23:04:00+02:00
head: 9245fca6762918f7d2a230b8a7dfe231bd4b8131
branch: feat/OTERYN-20260724-wiki-foundation
pr: none
status: implementing
context_routes:
  - agent-governance
  - architecture
  - web-cms
  - database
  - admin-rbac
  - security
  - testing
owned_paths:
  - app/Wiki/**
  - app/Providers/WikiServiceProvider.php
  - routes/wiki.php
  - database/migrations/*_create_wiki_*.php
  - database/factories/Wiki/**
  - tests/Unit/Wiki/**
  - tests/Feature/Wiki/**
  - docs/architecture/adr/*wiki*.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/agents/tasks/active/OTERYN-20260724-wiki-foundation.md
proven:
  - PR 142 is merged and defines a dedicated Wiki module with Slice 0 and Slice 1 boundaries.
  - PR 146 is merged and reserves wiki.access, wiki.articles.manage, wiki.categories.manage and wiki.publish without wildcard or automatic role grants.
  - no open Wiki or CMS pull request and no active Wiki task record was found before branch creation.
  - only blakinio/Oteryn-Platform is authorized for writes.
derived:
  - the first implementation PR can consume the reserved exact permissions without editing the shared permission registry.
unknown:
  - exact reusable CMS, audit, lifecycle, migration and testing patterns still to be confirmed from current main.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Wiki permission keys require a shared-registry edit: PR 146 already reserved all four first-slice keys.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-wiki-foundation.md
validation:
  - command: preflight overlap and precondition verification
    result: PASS
    evidence: PR 142 and PR 146 are merged; open PR search found only unrelated E2E evidence PR 116.
  - command: local repository validation
    result: BLOCKED
    evidence: sandbox cannot resolve github.com, so repository checkout and local commands are unavailable; exact-head CI will be used.
blockers:
  - none
next_action: Open the draft PR, then inspect current CMS, audit, migration, authorization and test patterns before implementing Wiki persistence.
```

## Notes

The first slice deliberately excludes public Wiki routes, rendering, media, search, navigation and editor UI. `routes/wiki.php` may exist only as an unactivated module-local placeholder if repository conventions require it.
