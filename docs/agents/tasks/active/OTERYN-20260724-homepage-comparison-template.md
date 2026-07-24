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

- [ ] A separately addressable preview route renders the comparison template.
- [ ] The current `/` homepage remains unchanged.
- [ ] The preview reuses existing safe public routes and brand artwork and does not fabricate live game data.
- [ ] The implementation is responsive and keyboard-accessible.
- [ ] A focused feature test proves the route and current-home isolation.
- [ ] Relevant formatting, tests and asset validation pass on the final head.

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
updated_at: 2026-07-24T11:49:17Z
head: cdc1d07d7c7d4cca0f1133e2beb30890359eadd1
branch: task/OTERYN-20260724-homepage-comparison-template
pr: none
status: implementing
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
  - The current homepage is Route::view('/', 'home') and can remain isolated from a new preview route.
  - The shared public layout already provides navigation, account actions, CSP-compatible same-origin styles and existing Oteryn artwork.
  - No overlapping homepage/public-web implementation PR was found.
derived:
  - A separate Blade view plus dedicated stylesheet is the smallest reversible comparison boundary.
unknown:
  - Final visual acceptance requires a rendered browser preview after implementation.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Replacing the current homepage directly was rejected because the user requested side-by-side comparison first.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-homepage-comparison-template.md
validation:
  - command: not-run
    result: NOT_RUN
    evidence: implementation not yet complete
blockers:
  - none
next_action: Open the draft PR and implement the isolated preview route, view, stylesheet and feature test.
```

## Notes

The comparison page will avoid fake player counts, rankings, server state and news content. It will link to the existing authoritative public surfaces until a separately scoped aggregation read model is approved.
