---
task_id: OTERYN-20260724-announcements-events
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/PUBLIC_WEBSITE_EXPANSION_PLAN.md
  - docs/contracts/PUBLIC_PORTAL_EXTENSION_CONTRACT.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/architecture/TEST_STRATEGY.md
search_first:
  - docs/agents/tasks/active/**
  - open pull requests affecting announcements, events, CMS, RBAC, audit, module routes or localization
  - existing CMS/Admin/RBAC/Audit services, requests, controllers and tests
  - existing date/time and localization conventions
optional_reads: []
---

# OTERYN-20260724-announcements-events

## Goal

Deliver isolated, audited Announcements and Events modules with deterministic UTC scheduling, exact authorization, safe localized public content, module-local routes and reusable homepage integration providers/components without modifying homepage or shared navigation/footer files.

## Acceptance criteria

- [ ] Announcements support title, body, severity, publication state, start/end boundaries and validated internal or approved external action links.
- [ ] Public announcement queries expose only active approved records and ticker boundary behavior is deterministic and tested.
- [ ] Announcement administration requires `auth`, confirmed MFA and `portal.announcements.manage`; mutations are audited with bounded metadata and stale edits fail explicitly.
- [ ] Events support localized title, slug, summary and safe body, UTC start/end, featured flag, optional news relation and draft/scheduled/active/completed/cancelled states.
- [ ] Public `/events` and `/events/{slug}` expose only approved public records, including upcoming, archived, cancelled and empty states as specified.
- [ ] Event administration requires `auth`, confirmed MFA and exact `events.manage` / `events.publish` permissions; mutations are audited with bounded metadata and stale edits fail explicitly.
- [ ] Localized slug uniqueness and deterministic timezone behavior are enforced and tested.
- [ ] Reusable ticker and upcoming-event providers/components exist for later homepage integration without modifying homepage files.
- [ ] No raw HTML or image upload is introduced.
- [ ] Focused and full required CI pass on the exact final head.

## Ownership

```yaml
owned_paths:
  - app/Announcements/**
  - app/Events/**
  - app/Http/Controllers/Announcements/**
  - app/Http/Controllers/Events/**
  - app/Http/Requests/Announcements/**
  - app/Http/Requests/Events/**
  - database/migrations/*site_announcements*.php
  - database/migrations/*events*.php
  - database/factories/SiteAnnouncementFactory.php
  - database/factories/EventFactory.php
  - database/factories/EventTranslationFactory.php
  - resources/views/announcements/**
  - resources/views/events/**
  - resources/views/admin/announcements/**
  - resources/views/admin/events/**
  - resources/navigation/public/events.php
  - routes/modules/announcements.php
  - routes/modules/events.php
  - tests/Feature/Announcements/**
  - tests/Feature/Events/**
  - tests/Unit/Announcements/**
  - tests/Unit/Events/**
  - docs/agents/tasks/active/OTERYN-20260724-announcements-events.md
modules:
  - Announcements
  - Events
  - AdminRBAC
  - Audit
  - PublicPortal
  - CMS
dependencies:
  - PR #146 public-web foundation merged
  - exact permissions reserved centrally
  - module-local route loading available
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T21:03:20Z
head: 9245fca6762918f7d2a230b8a7dfe231bd4b8131
branch: feat/OTERYN-20260724-announcements-events
pr: none
status: investigating
context_routes:
  - agent-governance
  - architecture
  - web-cms
  - admin-rbac
  - database
  - security
  - testing
owned_paths:
  - app/Announcements/**
  - app/Events/**
  - app/Http/Controllers/Announcements/**
  - app/Http/Controllers/Events/**
  - app/Http/Requests/Announcements/**
  - app/Http/Requests/Events/**
  - database/migrations/*site_announcements*.php
  - database/migrations/*events*.php
  - database/factories/SiteAnnouncementFactory.php
  - database/factories/EventFactory.php
  - database/factories/EventTranslationFactory.php
  - resources/views/announcements/**
  - resources/views/events/**
  - resources/views/admin/announcements/**
  - resources/views/admin/events/**
  - resources/navigation/public/events.php
  - routes/modules/announcements.php
  - routes/modules/events.php
  - tests/Feature/Announcements/**
  - tests/Feature/Events/**
  - tests/Unit/Announcements/**
  - tests/Unit/Events/**
  - docs/agents/tasks/active/OTERYN-20260724-announcements-events.md
proven:
  - Current main head 9245fca6762918f7d2a230b8a7dfe231bd4b8131 contains the merged public-web foundation and archived foundation task.
  - The only open pull request is unrelated scheduled E2E evidence work and does not claim announcement or event paths.
  - Exact permission keys `portal.announcements.manage`, `events.manage` and `events.publish` were reserved by the public-web foundation without automatic role grants.
  - Writes are authorized only in blakinio/Oteryn-Platform.
derived:
  - The task can proceed on isolated module paths without editing homepage, shared navigation/footer or the central permission registry.
unknown:
  - Exact existing CMS, audit, concurrency, localization and date/time implementation patterns still require source inspection.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses: []
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-announcements-events.md
validation:
  - command: repository and overlap preflight
    result: PASS
    evidence: current main, merged PR #146 and open PR inventory inspected
blockers:
  - none
next_action: Inspect the public extension contract and existing CMS, audit, authorization, concurrency, localization and time-handling implementations before designing the modules.
```

## Notes

Trust boundaries affected: public publication filtering, privileged CMS-like mutations, exact permission authorization, MFA enforcement and bounded audit logging. Platform-owned schema only; no Canary/login-server compatibility change, secrets or production-only configuration is involved. Migrations must remain reversible and stale-write behavior must be deterministic.
