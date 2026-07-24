---
task_id: OTERYN-20260724-download-center
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/architecture/PUBLIC_WEBSITE_EXPANSION_PLAN.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/contracts/PUBLIC_PORTAL_EXTENSION_CONTRACT.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
search_first:
  - current CMS/Admin/RBAC/Audit conventions
  - current release and deployment artifact documentation
  - active tasks and open PRs for Downloads path overlap
optional_reads: []
---

# OTERYN-20260724-download-center

## Goal

Implement an isolated production-capable Download Center that publishes approved immutable client artifact references without exposing executable uploads or arbitrary URL proxying.

## Acceptance criteria

- [ ] Platform-owned `client_releases` and `client_release_artifacts` persistence supports stable/beta channels, OS/architecture variants, publication and per-channel current state.
- [ ] Public `/download` identifies current approved builds and shows version, platform, filename, size and SHA-256.
- [ ] Optional platform filtering, empty state and dependency-unavailable state are explicit.
- [ ] Draft and non-current unpublished releases are not public.
- [ ] Administrator list/create/update/publish workflow requires `auth`, `mfa.confirmed` and exact `downloads.manage`.
- [ ] No executable upload input or Platform URL proxy exists.
- [ ] Artifact references reject unapproved schemes/hosts including `javascript:` and `data:`.
- [ ] Administrator mutations are CSRF-protected, validated and recorded with bounded non-secret audit metadata.
- [ ] No shared route, layout, homepage, central permission registry, Events, Wiki, Support or PublicGameData paths are modified.
- [ ] Formatting, PHPStan, focused tests, full tests and required CI pass on the exact head.

## Ownership

```yaml
owned_paths:
  - app/Downloads/**
  - app/Http/Controllers/Downloads/**
  - app/Http/Requests/Downloads/**
  - config/downloads.php
  - database/migrations/*client_release*
  - database/factories/Downloads/**
  - resources/navigation/public/downloads.php
  - resources/views/downloads/**
  - resources/views/admin/downloads/**
  - routes/modules/downloads.php
  - tests/Feature/Downloads/**
  - tests/Unit/Downloads/**
  - docs/agents/tasks/active/OTERYN-20260724-download-center.md
modules:
  - Downloads
dependencies:
  - merged public-web parallel foundation / PR #146
  - reserved exact permission downloads.manage
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T21:07:22Z
head: 9245fca6762918f7d2a230b8a7dfe231bd4b8131
branch: feat/OTERYN-20260724-download-center
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
  - app/Downloads/**
  - app/Http/Controllers/Downloads/**
  - app/Http/Requests/Downloads/**
  - config/downloads.php
  - database/migrations/*client_release*
  - database/factories/Downloads/**
  - resources/navigation/public/downloads.php
  - resources/views/downloads/**
  - resources/views/admin/downloads/**
  - routes/modules/downloads.php
  - tests/Feature/Downloads/**
  - tests/Unit/Downloads/**
  - docs/agents/tasks/active/OTERYN-20260724-download-center.md
proven:
  - PR #146 is merged on main and provides module-local routes, navigation contributions and the reserved downloads.manage permission.
  - routes/web.php loads routes/modules/*.php without requiring a shared route edit.
  - the public navigation registry loads resources/navigation/public/*.php without requiring shared layout edits.
  - the reserved downloads.manage permission has no automatic role grant.
  - the only open PR path overlap is docs-only scheduled E2E evidence and does not overlap Downloads paths.
derived:
  - Downloads persistence is Platform-owned and requires no Canary or login-server contract.
  - direct approved HTTPS links avoid executable upload and arbitrary proxy surfaces.
unknown:
  - exact final validation and CI result for the implementation head.
conflicts: []
first_failure:
  marker: local-checkout-unavailable
  evidence: sandbox DNS could not resolve github.com, so repository inspection and writes use the GitHub connector and local validation is not yet available.
rejected_hypotheses:
  - editing routes/web.php or the shared public/admin layouts is unnecessary and prohibited.
  - granting downloads.manage to an existing shared role bundle is outside this isolated task.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-download-center.md
validation:
  - command: not-run
    result: NOT_RUN
    evidence: implementation not yet complete; local checkout unavailable because sandbox DNS cannot resolve github.com
blockers:
  - none
next_action: Implement the Downloads persistence, artifact URL policy, public/admin workflows, module routes, views and focused tests on the task branch.
```

## Notes

The module will store operator-supplied artifact metadata and approved immutable HTTPS references only. It will not fetch artifacts, proxy URLs, upload executables or claim checksum verification.