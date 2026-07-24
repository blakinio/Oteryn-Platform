---
task_id: OTERYN-20260724-homepage-comparison-template
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/architecture/MODULE_CATALOG.md
search_first:
  - active homepage/public-web tasks and pull requests
  - existing home Blade, public layout, portal CSS and feature tests
optional_reads: []
---

# OTERYN-20260724-homepage-comparison-template

## Goal

Add an isolated, responsive homepage comparison template based on the supplied dark fantasy portal mock-up, without replacing or changing the current homepage behavior.

## Acceptance criteria

- [x] A separately addressable preview route renders the comparison template.
- [x] The current `/` homepage remains unchanged.
- [x] The preview reuses existing safe public routes and brand artwork and does not fabricate live game data.
- [x] The implementation uses responsive layouts, visible focus handling inherited from the public shell and semantic keyboard-accessible controls.
- [x] A focused feature test proves the route and current-home isolation.
- [x] Formatting, static analysis, tests, browser acceptance, production-like validation and staging image validation passed on the implementation head.

## Ownership

```yaml
owned_paths:
  - routes/web.php
  - resources/views/game/layout.blade.php
  - resources/views/home-preview.blade.php
  - public/css/home-preview.css
  - tests/Feature/HomePreviewTest.php
  - docs/agents/tasks/active/OTERYN-20260724-homepage-comparison-template.md
modules:
  - PublicGameData
  - CMS
dependencies:
  - existing public routes and brand assets
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T12:02:00Z
head: 291d23915c9f156e433192a2b0cb799853a5d1f2
branch: task/OTERYN-20260724-homepage-comparison-template
pr: 139
status: validating-final-head
context_routes:
  - web-cms
  - testing
  - agent-governance
owned_paths:
  - routes/web.php
  - resources/views/game/layout.blade.php
  - resources/views/home-preview.blade.php
  - public/css/home-preview.css
  - tests/Feature/HomePreviewTest.php
  - docs/agents/tasks/active/OTERYN-20260724-homepage-comparison-template.md
proven:
  - The current homepage remains Route::view('/', 'home').
  - The isolated comparison route is /design/home-v2 and renders home-preview.blade.php.
  - The preview uses a dedicated same-origin stylesheet, noindex metadata, existing brand assets and existing authoritative public routes.
  - The preview contains no fabricated player counts, ranking entries, server state, news titles or publication dates.
  - Focused HomePreviewTest coverage proves preview rendering and root-home isolation.
  - Implementation head 291d23915c9f156e433192a2b0cb799853a5d1f2 passed CI run 30091169979, Agent Governance 30091169945, Acceptance E2E and Visual UX 30091170000, Platform DB Outage Validation 30091170055, Phase 7 Production-Like Validation 30091169954, Game Auth Ticket Concurrency 30091169963 and Build Synology Staging Images 30091169953.
derived:
  - Replacing Route::view('/', 'home') with Route::view('/', 'home-preview') is the smallest later swap after visual approval.
unknown:
  - Final subjective visual approval against the supplied mock-up remains a user decision after the preview is opened.
conflicts: []
first_failure:
  marker: none
  evidence: no implementation or CI failure observed
rejected_hypotheses:
  - Replacing the current homepage directly was rejected because the user requested side-by-side comparison first.
  - Fabricating dashboard statistics for visual fidelity was rejected because public game-data claims must remain authoritative.
changed_paths:
  - routes/web.php
  - resources/views/game/layout.blade.php
  - resources/views/home-preview.blade.php
  - public/css/home-preview.css
  - tests/Feature/HomePreviewTest.php
  - docs/agents/tasks/active/OTERYN-20260724-homepage-comparison-template.md
validation:
  - command: GitHub Actions CI run 30091169979
    result: PASS
    evidence: formatting, PHPStan and full tests succeeded on implementation head
  - command: GitHub Actions Acceptance E2E and Visual UX run 30091170000
    result: PASS
    evidence: browser acceptance and visual/accessibility workflow succeeded on implementation head
  - command: GitHub Actions Agent Governance run 30091169945
    result: PASS
    evidence: task ownership and governance validation succeeded
  - command: GitHub Actions supporting required workflows
    result: PASS
    evidence: runs 30091170055, 30091169954, 30091169963 and 30091169953 succeeded
  - command: local checkout validation
    result: UNAVAILABLE
    evidence: sandbox could not resolve github.com, so repository checkout was unavailable; exact-head GitHub CI is authoritative
blockers:
  - none
next_action: Verify the checkpoint-only final head checks, update the PR summary and merge if the merge gate remains satisfied.
```

## Notes

The comparison page intentionally links to existing authoritative public surfaces instead of aggregating or inventing live dashboard data. The current homepage remains unchanged until a separate explicit replacement decision.
