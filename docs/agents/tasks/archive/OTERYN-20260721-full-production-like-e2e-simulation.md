---
task_id: OTERYN-20260721-full-production-like-e2e-simulation
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/PROJECT_STATE.md
search_first:
  - open PRs and active tasks overlapping acceptance or production-like validation
  - existing acceptance and Phase 7 validation workflows before adding orchestration
optional_reads:
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
---

# OTERYN-20260721-full-production-like-e2e-simulation

## Goal

Execute a fresh, isolated, validation-only production-like simulation on Oteryn Platform and close any reusable E2E defect proven by the run, without touching production or external repositories.

## Acceptance criteria

- [x] Run the complete primary Chromium functional acceptance baseline against an isolated real Laravel HTTP runtime with MariaDB, Redis and MailHog.
- [x] Run bounded Chromium/Firefox/WebKit portability coverage.
- [x] Run bounded desktop/tablet/mobile responsive coverage.
- [x] Run deterministic dependency failure/restoration/recovery coverage.
- [x] Run keyboard/focus accessibility interaction coverage.
- [x] Run the exploratory visual/accessibility collector and preserve non-secret evidence.
- [x] Run a bounded zero-retry public-surface soak and preserve latency/RSS/Redis-key calibration evidence.
- [x] Verify standard CI, Agent Governance, Platform DB Outage Validation, Acceptance E2E/Visual and Phase 7 Production-Like Validation on the exact validation head.
- [x] Record exact-SHA run/job/artifact evidence and first failures.
- [x] Remove validation-only orchestration before completion; retain only the proven reusable soak assertion correction.
- [x] Keep all results classified as repository/staging evidence; do not claim `PRODUCTION_PROVEN`.

## Final result

Exact validated head: `f7a5d121d826a0c545bdd193b0d97bee31f63bc0`.

All required exact-head workflows passed:

- CI `29864046542`;
- Agent Governance `29864046246`;
- Platform DB Outage Validation `29864047063`;
- Acceptance E2E and Visual UX `29864046519`;
- Phase 7 Production-Like Validation `29864045809`;
- Full Production-Like E2E Simulation `29864046786`.

The final comprehensive job passed:

- full primary Chromium: 15 tests, 0 failures, 0 skipped, 0 errors;
- Chromium/Firefox/WebKit portability: 12 tests, 0 failures;
- desktop/tablet/mobile responsive: 9 tests, 0 failures;
- dependency failure/restoration/recovery: 2 tests, 0 failures;
- keyboard/focus accessibility: 3 tests, 0 failures;
- zero global Playwright retries for the dedicated simulation profiles;
- exploratory Visual/Accessibility: 71 screenshots;
- 0 status mismatches;
- 0 horizontal-overflow surfaces;
- 0 unlabeled-control surfaces;
- 0 sampled low-contrast surfaces;
- 0 focus-not-observed surfaces;
- 0 raw technical-message surfaces;
- 6 browser console-error surfaces are the intentional 403/404/503 response pages and have 0 page errors.

Comprehensive artifact:

- artifact `8508662689`;
- digest `sha256:ff7c676f163f3002d444b9748f5cdd9adb58eb7cd0eb7c887189677e86d6e0d2`.

The final independent public soak passed:

- target duration: 300 seconds;
- actual browser duration: 300 seconds;
- measured outer duration: 302 seconds;
- iterations: 466;
- requests: 1861;
- latency min/p50/p95/max: 22.826 / 96.196 / 259.483 / 424.335 ms;
- Laravel process-tree RSS start/end/max: 181300 / 181844 / 183140 KiB;
- Redis key count before/after: 1 / 1;
- no arbitrary performance budget was introduced.

Per-route soak calibration:

- `/`: 466 requests, p50 165.039 ms, p95 290.709 ms, max 424.335 ms;
- `/online`: 465 requests, p50 108.56 ms, p95 141.861 ms, max 284.731 ms;
- `/highscores`: 465 requests, p50 78.482 ms, p95 132.053 ms, max 225.88 ms;
- `/servers`: 465 requests, p50 78.518 ms, p95 129.613 ms, max 233.169 ms.

Soak artifact:

- artifact `8508621624`;
- digest `sha256:e9cd247f5ea6814e847e522843813c5ab467c94f7d3abaa43dfe09c8df7ac8a6`.

## Defect found and corrected

The fresh isolated soak exposed a reusable E2E locator defect in `scripts/acceptance/tests/soak-public.spec.mjs`.

The `/servers` page correctly rendered the Acceptance server card with `Runtime: ONLINE`, but the soak test required a standalone exact-text `ONLINE` element. The captured accessibility tree proved the runtime was present and healthy. The assertion was corrected to validate `Runtime: ONLINE` within the Acceptance server article. The corrected 300-second zero-retry soak then passed on the final exact validation head.

Earlier temporary validation-only orchestration failures were not product regressions:

- the first temporary soak attempt generated an empty `APP_KEY` because of malformed `php -r` escaping;
- the combined-job experiment attempted to bootstrap already-mutated dependencies before soak and was replaced with a fresh isolated soak job.

The temporary workflow `.github/workflows/full-production-like-e2e-simulation.yml` is removed before completion and is not part of the intended merged baseline.

## Classification

- Production Readiness remains `STAGING_PROVEN`.
- Functional Acceptance remains `STAGING_PROVEN` for the delivered staging-verifiable scope.
- Visual / UX Acceptance remains `PASS` for the delivered staging-verifiable launch scope.
- Production Go-Live Gate remains `PENDING PRODUCTION VERIFICATION` under issue #91.
- No evidence from this task is `PRODUCTION_PROVEN`.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T20:11:00Z
head: f7a5d121d826a0c545bdd193b0d97bee31f63bc0
branch: task/OTERYN-20260721-full-production-like-e2e-simulation
pr: 115
status: ready
context_routes:
  - testing
  - security
  - ci-repair
  - agent-governance
proven:
  - exact-head comprehensive production-like E2E simulation passed
  - exact-head independent 300-second zero-retry public soak passed
  - standard exact-head CI, governance, DB outage, acceptance and Phase 7 workflows passed
  - one reusable soak locator defect was corrected and revalidated
  - temporary validation-only workflow is removed before merge
  - production and external repositories were not modified
unknown:
  - production-only facts tracked by issue #91
  - long-term scheduled stability/soak variance tracked independently by issue #114
conflicts: []
first_failure:
  marker: soak-public /servers locator required standalone exact text ONLINE
  evidence: failed fresh soak run 29863729939; captured page tree rendered Runtime: ONLINE; corrected final soak run 29864046786 passed
changed_paths:
  - scripts/acceptance/tests/soak-public.spec.mjs
  - docs/agents/tasks/archive/OTERYN-20260721-full-production-like-e2e-simulation.md
validation:
  - command: exact-head required workflows on f7a5d121d826a0c545bdd193b0d97bee31f63bc0
    result: PASS
    evidence: runs 29864046542 / 29864046246 / 29864047063 / 29864046519 / 29864045809 / 29864046786
  - command: independent 300-second zero-retry public soak
    result: PASS
    evidence: artifact 8508621624, digest sha256:e9cd247f5ea6814e847e522843813c5ab467c94f7d3abaa43dfe09c8df7ac8a6
blockers:
  - issue #91 remains production-only and intentionally unresolved here
next_action: merge PR #115 after final cleanup-head required checks pass; resume issue #91 only with explicit production authorization and exact deployed production release
```
