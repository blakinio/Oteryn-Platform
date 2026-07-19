# OTERYN-20260719 Public site shell and character search

## Goal

Complete the next bounded Phase 4 public-web slice by reusing the existing Blade game-data layout as the shared public shell, exposing complete navigation for implemented public read surfaces, and adding exact-name character search that routes to the existing character profile endpoint without introducing a new Canary query or any shared-data write.

## Acceptance criteria

- [x] Archive the merged `OTERYN-20260719-online-list-read-model` task record under `docs/agents/tasks/archive/` without changing its historical blob contents.
- [x] Make the homepage use the existing shared public/game-data Blade layout instead of a standalone document.
- [x] Expose Home, Online, Highscores and Servers navigation from the shared public layout.
- [x] Add an exact-name character search form on the homepage and a bounded GET search route that validates input then redirects to the existing `game.characters.show` profile route.
- [x] Do not add a second character query implementation, new Canary table access, caching, live runtime-status claims or any Canary/shared-data write.
- [x] Add focused feature coverage for the shared homepage shell/navigation, successful character-search redirect and missing-name validation.
- [x] Update Phase 4 roadmap/module/project-state documentation to reflect the delivered online list and this public shell/search slice without claiming CMS/news or live runtime availability are complete.
- [x] Run repository CI and Agent Governance on the delivery-validation head; require a fresh exact-head pass after this ready checkpoint before merge.

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
updated_at: 2026-07-19T17:50:00+02:00
head: e936dc94e176f23525d799df23d6714bca042d3a
branch: task/OTERYN-20260719-public-site-shell-and-search
pr: 19
status: ready
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
  - PR #18 is closed and merged, and the online-list task archive points to blob 64daae69830e60d845439eebd1291ebc106c5587, exactly matching the former active task blob.
  - Draft PR #19 targets main from the dedicated task/OTERYN-20260719-public-site-shell-and-search branch.
  - The homepage extends game.layout, so the homepage and existing public game-data views share the same Blade shell while preserving the existing homepage foundation text contract.
  - The shared layout exposes Home, Online, Highscores and Servers navigation.
  - GET /characters validates a required string name with maximum length 255, obtains the validated input without relying on an unproven array offset, narrows it to string, and redirects to the existing game.characters.show route; it performs no new Canary query itself.
  - Focused feature tests cover homepage shell/navigation/search presence, exact-name redirect and missing-name validation.
  - ROADMAP, MODULE_CATALOG and PROJECT_STATE describe the delivered online list and public shell/search slice while leaving managed news and live multichannel runtime availability incomplete.
  - PR #19 contains only 11 task-owned paths, and the archived online-list task is represented as an exact-content rename with zero additions and zero deletions.
  - Delivery-validation head e936dc94e176f23525d799df23d6714bca042d3a passed CI run 29693605094 (#300), including Composer validation/install, Pint format check, PHPStan/Larastan level 10 and the full test suite.
  - Delivery-validation head e936dc94e176f23525d799df23d6714bca042d3a passed Agent Governance run 29693605093 (#221).
derived:
  - Exact-name search remains a routing concern around the existing character profile read model, so the Canary privilege allowlist and data contract do not need expansion for this task.
unknown: []
conflicts: []
first_failure:
  marker: CI_STATIC_ANALYSIS_VALIDATED_INPUT_TYPING
  evidence: CI runs #296, #297 and #298 failed at the static-analysis step while the new search action relied on the generic Request validation result shape; after replacing the array-offset access with validated Request input plus an explicit string guard, CI #299 passed static analysis. CI #299 then exposed the existing HomeTest homepage text contract, which was preserved in the shared-shell homepage; CI #300 passed all steps.
rejected_hypotheses:
  - Add a new character-search database query: rejected because exact-name lookup already exists behind the character profile route.
  - Add live server availability to the homepage: rejected because fresh multichannel runtime availability transport remains a separate unresolved integration concern.
  - Weaken PHPStan or suppress the new typing failure: rejected; the search action was changed to a level-10-compatible explicit input narrowing pattern instead.
  - Modify the existing HomeTest solely for the new copy: rejected; the established homepage foundation text contract remains valid and was preserved in the new shared-shell view.
changed_paths:
  - app/Http/Controllers/PublicGameData/PublicGameDataController.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260719-public-site-shell-and-search.md
  - docs/agents/tasks/archive/OTERYN-20260719-online-list-read-model.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/ROADMAP.md
  - resources/views/game/layout.blade.php
  - resources/views/home.blade.php
  - routes/web.php
  - tests/Feature/PublicSiteShellTest.php
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: current main commit, merged PR #18, open PR state, governance/routing context and affected public-web source were inspected before implementation
  - command: local Composer/Pint/PHPStan/tests
    result: NOT_RUN
    evidence: current execution environment cannot resolve github.com and has no usable local Oteryn-Platform checkout; exact-head GitHub Actions is the executable validation source
  - command: GitHub Actions CI run 29693339730 (#296)
    result: FAIL
    evidence: static-analysis step failed on the initial search-action implementation; full tests were skipped
  - command: GitHub Actions CI run 29693413678 (#297)
    result: FAIL
    evidence: static-analysis step still failed before validated-input offset handling was removed
  - command: GitHub Actions CI run 29693493218 (#298)
    result: FAIL
    evidence: static-analysis step still failed after the first type guard because the generic validated array offset remained
  - command: GitHub Actions CI run 29693549396 (#299)
    result: FAIL
    evidence: Composer validation/install, formatting and static analysis passed; full tests exposed the pre-existing HomeTest foundation-text expectation
  - command: GitHub Actions CI run 29693605094 (#300)
    result: PASS
    evidence: exact delivery-validation head e936dc94e176f23525d799df23d6714bca042d3a passed Composer validation/install, Pint, PHPStan/Larastan level 10 and the full test suite
  - command: Agent Governance run 29693605093 (#221)
    result: PASS
    evidence: exact delivery-validation head e936dc94e176f23525d799df23d6714bca042d3a passed active checkpoint validation
blockers:
  - none
next_action: Revalidate CI and Agent Governance on the final ready-checkpoint head, then perform the full PR #19 merge gate and squash-merge only if main divergence, final diff and review state remain clean.
```

## Notes

This task is presentation/routing only around already-approved read surfaces. It does not authorize CMS authoring, new game-data reads, runtime availability transport, caching, authentication changes, Admin/RBAC, shared Canary writes or production deployment work.
