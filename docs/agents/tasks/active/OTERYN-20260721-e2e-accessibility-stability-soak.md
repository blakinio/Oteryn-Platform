---
task_id: OTERYN-20260721-e2e-accessibility-stability-soak
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/adr/0008-risk-based-continuous-e2e-validation.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/testing/E2E_ACCESSIBILITY_STABILITY_SOAK_EVIDENCE.md
  - scripts/acceptance/playwright.config.mjs
  - scripts/acceptance/tests/helpers.mjs
  - .github/workflows/acceptance-validation.yml
search_first:
  - open PRs and active tasks overlapping scripts/acceptance or acceptance workflow ownership
  - existing accessibility smoke and visual collector before adding interaction assertions
  - existing acceptance profiles before creating repeat or soak orchestration
optional_reads:
  - docs/testing/VISUAL_UX_ACCEPTANCE_MATRIX.md
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
---

# OTERYN-20260721-e2e-accessibility-stability-soak

## Goal

Close issue #110 with bounded P1 accessibility interaction coverage plus non-blocking P2 repeated-run and soak profiles, preserving the existing exact-SHA acceptance architecture and production-verification boundary.

## Acceptance criteria

- [x] Add a zero-retry Chromium accessibility interaction profile proving representative keyboard/focus journeys for login/recovery/MFA/account/character/admin surfaces.
- [x] Keep secret-bearing raw traces/screenshots/video disabled and use sanitized diagnostics only.
- [x] Include accessibility interaction in required PR `critical` validation and full acceptance without multiplying it across all browsers/viewports.
- [x] Make the acceptance workflow reusable without changing direct PR/push/manual semantics.
- [x] Add a scheduled/manual repeated-run workflow executing the bounded `critical` profile in multiple fresh isolated jobs with iteration-labelled exact-SHA evidence.
- [x] Do not use Playwright retries as a substitute for repeat health evidence.
- [x] Add a scheduled/manual read-only Chromium soak profile over representative public surfaces.
- [x] Record soak response-time distribution plus bounded process/resource signals; do not introduce arbitrary blocking performance thresholds.
- [x] Update durable test strategy/roadmap/evidence and project state.
- [x] Keep issue #91 independent; perform no production action and no Canary/login-server repository writes.

## Ownership

```yaml
owned_paths:
  - .github/workflows/acceptance-validation.yml
  - .github/workflows/acceptance-stability.yml
  - .github/workflows/acceptance-soak.yml
  - scripts/acceptance/**
  - docs/testing/E2E_ACCESSIBILITY_STABILITY_SOAK_EVIDENCE.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-accessibility-stability-soak.md
modules:
  - testing
  - acceptance-e2e
  - accessibility
  - ci
  - observability
  - agent-governance
dependencies:
  - issue #110
  - ADR 0008
  - existing exact-SHA acceptance harness
blockers:
  - none
cross_repository_tasks:
  - none; Canary/login-server repositories remain read-only
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T17:55:00Z
head: 41dc8d8db379c88f6aa45528e2a8b14ee00b9fad
branch: task/OTERYN-20260721-e2e-accessibility-stability-soak
pr: 111
status: validating
context_routes:
  - testing
  - architecture
  - ci-repair
  - agent-governance
owned_paths:
  - .github/workflows/acceptance-validation.yml
  - .github/workflows/acceptance-stability.yml
  - .github/workflows/acceptance-soak.yml
  - scripts/acceptance/**
  - docs/testing/E2E_ACCESSIBILITY_STABILITY_SOAK_EVIDENCE.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-accessibility-stability-soak.md
proven:
  - main head at task start was cc63f1c4d4aac7f314c0b234b7bb17a864ed0972 after governance cleanup PR 107
  - issue 110 and draft PR 111 are the dedicated tracker and implementation PR
  - PR 103 and cleanup PR 112 merged to main as ec03f024d98bd9d155639e3ab7e4c25963e7e0c3 and 4f65e91b7d1369a2ed11a0c06077f3f71a0f88da while this task was active
  - the task branch was rebased/synchronized onto current main 4f65e91b7d1369a2ed11a0c06077f3f71a0f88da by reconstructing the exact 14-file PR scope on the current main tree; compare then reported ahead 1 behind 0 before this checkpoint update
  - accessibility-chromium is a zero-retry Chromium project using real Tab Shift+Tab Enter and focus-visible assertions for login recovery account character MFA and admin journeys
  - direct pull-request critical acceptance composes smoke portability responsive resilience and accessibility
  - full acceptance requires the full Chromium baseline plus resilience and accessibility before staging functional classification and visual collection
  - acceptance-validation.yml supports workflow_call with profile run suffix zero-retry and soak-duration inputs while retaining direct PR push and manual triggers
  - acceptance-stability.yml schedules and manually exposes three fail-fast-false fresh critical jobs with zero global Playwright retries and distinct iteration suffixes
  - acceptance-soak.yml schedules and manually exposes a zero-retry read-only soak through the reusable acceptance workflow
  - soak-public.spec.mjs loops home online highscores and servers only and writes latency distribution metrics without performance thresholds
  - soak runtime orchestration samples Laravel serve process-tree RSS and Redis key counts before/after without production access or secrets
  - exact SHA 3bd1e4901a71841bc4593ec7e4efb98866c8c30f passed Acceptance E2E and Visual UX run 29853941922 with smoke portability responsive resilience and accessibility all successful
  - accessibility exact-head evidence is 3 tests 0 failures 0 skipped and 6 seconds wall-clock profile duration
  - artifact acceptance-e2e-critical-29853941922-1-direct digest sha256:df7776df2c3ecb6e3199baab24469e351b3bbef4d099d916095a99f68f183b9a records accessibility_result success
  - durable roadmap test strategy evidence and project state describe accessibility repeat and soak implementation plus their production boundary
  - issue 91 remains production-only and independent
  - no Canary or login-server repository writes were performed
derived:
  - accessibility interaction adds unique browser evidence without unjustified cross-browser multiplication
  - isolated reusable-workflow jobs are the safest repeat model because rate-limit session cache and dependency state are fresh per iteration
  - soak signal should remain calibration-only until repeated scheduled/manual runs establish normal variance
  - repeat and soak mechanisms can merge with first runtime evidence explicitly pending because the current connector has no workflow-dispatch action and neither profile is a required PR blocker
unknown:
  - final required-check outcome on the current-main-synchronized documentation-updated PR head
  - first scheduled/manual three-iteration stability result because no workflow-dispatch action is available in the current connector
  - first scheduled/manual soak latency and RSS baseline because no workflow-dispatch action is available in the current connector
conflicts: []
first_failure:
  marker: portability-webkit privileged CMS flow redirected to login before admin pages assertion on a97a6a770fef0b8cc6d7212ad7039828a0122c87
  evidence: failure context showed final login page; root cause was navigation beginning before MFA completion redirect settled; explicit post-MFA URL synchronization fixed the race and the next portability run passed
rejected_hypotheses:
  - the full secret-sensitive suite should be multiplied across every browser for accessibility
  - repeated-run health can be inferred from Playwright retry success
  - repeat iterations should share one job and rate-limit/cache/session state
  - soak requires production access
  - arbitrary latency or memory thresholds are needed before baseline calibration
  - the first WebKit failure was a CMS product defect or WebKit incompatibility
  - the first accessibility failure was a focus/UI defect
changed_paths:
  - .github/workflows/acceptance-validation.yml
  - .github/workflows/acceptance-stability.yml
  - .github/workflows/acceptance-soak.yml
  - scripts/acceptance/package.json
  - scripts/acceptance/playwright.config.mjs
  - scripts/acceptance/tests/portability-critical.spec.mjs
  - scripts/acceptance/tests/accessibility-critical.spec.mjs
  - scripts/acceptance/tests/soak-public.spec.mjs
  - docs/testing/E2E_ACCESSIBILITY_STABILITY_SOAK_EVIDENCE.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-accessibility-stability-soak.md
validation:
  - command: live repository and existing acceptance profile inspection
    result: PASS
    evidence: repository state current acceptance projects and workflow inspected before implementation
  - command: Acceptance E2E and Visual UX run 29851698242 on a97a6a770fef0b8cc6d7212ad7039828a0122c87
    result: FAIL
    evidence: existing WebKit portability privileged flow exposed an MFA navigation race; no accessibility result was reached
  - command: Acceptance E2E and Visual UX run 29853324831 attempt 2 on e4a5de64887a427dc565e18359b6e99f234783f4
    result: FAIL
    evidence: portability synchronization fix passed; new accessibility Account Overview scenario exposed the same login-navigation race in keyboardLogin
  - command: Acceptance E2E and Visual UX run 29853941922 on 3bd1e4901a71841bc4593ec7e4efb98866c8c30f
    result: PASS
    evidence: smoke portability responsive resilience accessibility and aggregate critical evidence all passed
  - command: artifact acceptance-e2e-critical-29853941922-1-direct
    result: PASS
    evidence: accessibility 3 tests 0 failures 0 skipped; wall-clock 6 seconds; aggregate AUTOMATED_E2E_CRITICAL_PASS
  - command: compare main 4f65e91b7d1369a2ed11a0c06077f3f71a0f88da to synchronized task head 41dc8d8db379c88f6aa45528e2a8b14ee00b9fad
    result: PASS
    evidence: ahead 1 behind 0 with exactly the 14 expected task-owned changed paths
blockers:
  - none
next_action: Verify PR 111 current-main-synchronized final head against all required checks, confirm branch synchronization and clean ownership/review scope, then merge only if the exact-head gate is fully green.
```

## Notes

This task adds controlled staging/repository evidence only. Repeated-run and soak first runtime measurements remain intentionally pending scheduled/manual execution. This task does not change the Production Go-Live Gate or claim `PRODUCTION_PROVEN` behavior.
