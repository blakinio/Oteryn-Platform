# OTERYN-20260719 Public site shell and character search

## Goal

Complete the next bounded Phase 4 public-web slice by reusing the existing Blade game-data layout as the shared public shell, exposing complete navigation for implemented public read surfaces, and adding exact-name character search that routes to the existing character profile endpoint without introducing a new Canary query or any shared-data write.

## Acceptance criteria

- [x] Archive the merged `OTERYN-20260719-online-list-read-model` task record under `docs/agents/tasks/archive/` without changing its historical blob contents.
- [ ] Make the homepage use the existing shared public/game-data Blade layout instead of a standalone document.
- [ ] Expose Home, Online, Highscores and Servers navigation from the shared public layout.
- [ ] Add an exact-name character search form on the homepage and a bounded GET search route that validates input then redirects to the existing `game.characters.show` profile route.
- [ ] Do not add a second character query implementation, new Canary table access, caching, live runtime-status claims or any Canary/shared-data write.
- [ ] Add focused feature coverage for the shared homepage shell/navigation, successful character-search redirect and missing-name validation.
- [ ] Update Phase 4 roadmap/module/project-state documentation to reflect the delivered online list and this public shell/search slice without claiming CMS/news or live runtime availability are complete.
- [ ] Run the repository CI and Agent Governance checks on the exact ready head and merge only when the full merge gate is clean.

## Ownership

```yaml
owned_paths:
  - routes/web.php
  - app/Http/Controllers/PublicGameData/PublicGameDataController.php
  - resources/views/home.blade.php
  - resources/views/game/layout.blade.php
  - tests/Feature/PublicSiteShellTest.php
  - docs/architecture/ROADMAP.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-public-site-shell-and-search.md
  - docs/agents/tasks/active/OTERYN-20260719-online-list-read-model.md
  - docs/agents/tasks/archive/OTERYN-20260719-online-list-read-model.md
modules:
  - PublicGameData
  - public web
  - testing
  - agent-governance
dependencies:
  - OTERYN-20260718-game-read-model
  - OTERYN-20260719-online-list-read-model
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T17:10:00+02:00
head: 041c4dba01c33558b98f759a48d19f3cf753bc82
branch: task/OTERYN-20260719-public-site-shell-and-search
pr: none
status: investigating
context_routes:
  - agent-governance
  - web-cms
  - public-game-data
  - testing
owned_paths:
  - routes/web.php
  - app/Http/Controllers/PublicGameData/PublicGameDataController.php
  - resources/views/home.blade.php
  - resources/views/game/layout.blade.php
  - tests/Feature/PublicSiteShellTest.php
  - docs/architecture/ROADMAP.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-public-site-shell-and-search.md
  - docs/agents/tasks/active/OTERYN-20260719-online-list-read-model.md
  - docs/agents/tasks/archive/OTERYN-20260719-online-list-read-model.md
proven:
  - Main was verified at c66a8c1b352c757d1beb15f1ec838eb2d3ce17d5, the squash merge of PR #18, before starting this task.
  - Live GitHub PR search returned no open pull requests in blakinio/Oteryn-Platform before task claim.
  - PR #18 is closed and merged, and the online-list task archive now points to blob 64daae69830e60d845439eebd1291ebc106c5587, exactly matching the former active task blob.
  - Phase 4 remains in progress; highscores, character profiles, guild pages, configured server metadata and the cluster-wide online list already exist.
  - The current homepage is a standalone Blade document and the existing shared game-data layout navigation exposes only Highscores and Servers.
  - The existing character profile route is GET /characters/{name} and resolves active characters through the existing PublicGameData query boundary.
derived:
  - Exact-name search can remain a routing concern by validating the submitted name and redirecting to the existing character profile route, avoiding a duplicate read model or new Canary privilege requirement.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Add a new character-search database query: rejected because exact-name lookup already exists behind the character profile route.
  - Add live server availability to the homepage: rejected because fresh multichannel runtime availability transport remains a separate unresolved integration concern.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260719-online-list-read-model.md
  - docs/agents/tasks/active/OTERYN-20260719-online-list-read-model.md
  - docs/agents/tasks/active/OTERYN-20260719-public-site-shell-and-search.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: current main commit, merged PR #18, open PR state, governance/routing context and affected public-web source were inspected before implementation
  - command: implementation validation
    result: NOT_RUN
    evidence: implementation has not started yet
blockers:
  - none
next_action: Update ACTIVE_WORK and open the draft PR, then implement the bounded shared public shell and exact-name character-search redirect with focused tests.
```

## Notes

This task is presentation/routing only around already-approved read surfaces. It does not authorize CMS authoring, new game-data reads, runtime availability transport, caching, authentication changes, Admin/RBAC, shared Canary writes or production deployment work.
