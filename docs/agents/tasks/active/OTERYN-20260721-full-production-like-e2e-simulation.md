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

Execute a fresh, isolated, validation-only production-like simulation on the current Oteryn Platform `main` using the repository's existing deterministic E2E/operations harnesses, maximizing staging-verifiable coverage without touching production or external repositories.

## Acceptance criteria

- [ ] Run the complete primary Chromium functional acceptance baseline against an isolated real Laravel HTTP runtime with MariaDB, Redis and MailHog.
- [ ] Run bounded Chromium/Firefox/WebKit portability coverage.
- [ ] Run bounded desktop/tablet/mobile responsive coverage.
- [ ] Run deterministic dependency failure/restoration/recovery coverage.
- [ ] Run keyboard/focus accessibility interaction coverage.
- [ ] Run the exploratory visual/accessibility collector and preserve non-secret evidence.
- [ ] Run a bounded zero-retry public-surface soak and preserve latency/RSS/Redis-key calibration evidence.
- [ ] Verify the repository's standard CI, Agent Governance, Platform DB Outage Validation and Phase 7 Production-Like Validation on the exact validation head when triggered by the validation PR.
- [ ] Record exact-SHA run/job/artifact evidence and first failure if any.
- [ ] Remove validation-only orchestration before completion; do not merge test-only workflow behavior into the product baseline unless a real reusable gap is proven.
- [ ] Keep all results classified as repository/staging evidence; do not claim `PRODUCTION_PROVEN`.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260721-full-production-like-e2e-simulation.md
  - docs/agents/tasks/archive/OTERYN-20260721-full-production-like-e2e-simulation.md
  - .github/workflows/full-production-like-e2e-simulation.yml
modules:
  - testing
  - CI validation
  - agent governance
dependencies:
  - issue #91 remains the independent production-only go-live gate
  - issue #114 remains the independent first scheduled repeat/soak evidence tracker
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T19:58:00Z
head: 734601f9f7ee3ada809195ea7a51751647f86b4f
branch: task/OTERYN-20260721-full-production-like-e2e-simulation
pr: 115
status: validating
context_routes:
  - testing
  - security
  - ci-repair
  - agent-governance
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260721-full-production-like-e2e-simulation.md
  - docs/agents/tasks/archive/OTERYN-20260721-full-production-like-e2e-simulation.md
  - .github/workflows/full-production-like-e2e-simulation.yml
proven:
  - main head at task start is 0bc273816dcf515cf264652cabe8b8a3c2f95b59
  - no active repository/staging implementation task is listed in ACTIVE_WORK
  - no open pull request overlapped this validation task at preflight
  - the repository already provides exact-SHA production-like acceptance, Phase 7, resilience, accessibility, portability, responsive and soak harnesses
  - draft PR #115 targets main from the dedicated task branch
  - CI, Agent Governance, Platform DB Outage Validation and Phase 7 Production-Like Validation all passed on validation head 0b46bda79fabda043f79be2b68ce645e17c2b634
  - comprehensive run 29862854364 passed full Chromium, zero-retry Chromium/Firefox/WebKit portability, desktop/tablet/mobile responsive, dependency failure-restoration-recovery, keyboard-focus accessibility and exploratory visual/accessibility execution
  - run 29862854364 produced 15 full Chromium tests, 12 portability tests, 9 responsive tests, 2 resilience tests and 3 accessibility tests with zero test failures in their JUnit evidence
  - visual evidence from run 29862854364 contains 71 screenshots with zero status mismatches, horizontal-overflow surfaces, unlabeled-control surfaces, sampled low-contrast surfaces, focus-not-observed surfaces or raw technical-message surfaces
  - artifact 8508221344 from run 29862854364 has digest sha256:de0d86a8b7c1749c3f23c036bf3599860428adbafa9e2c01875555cef4f962a8
  - first temporary soak attempt failed before product assertions because the validation-only workflow generated an empty APP_KEY from invalid escaped PHP syntax
  - second temporary combined-job design completed all functional and visual profiles but its in-job dependency reset before soak failed; soak therefore did not produce valid calibration evidence
  - soak orchestration is now isolated into a fresh independent job with fresh MariaDB, Redis, MailHog, Laravel runtime and corrected ephemeral APP_KEY generation
  - production execution and external Canary/login-server writes are outside this task
  - repository writes are limited to blakinio/Oteryn-Platform
derived:
  - neither temporary orchestration failure is evidence of an Oteryn product regression
  - fresh-job soak isolation avoids coupling soak evidence to state mutated by resilience and exploratory visual scenarios
unknown:
  - outcome of the final fresh-job 300-second soak on the current validation head
conflicts: []
first_failure:
  marker: temporary validation orchestration defects
  evidence: run 29862494074 exposed malformed APP_KEY generation; run 29862854364 later proved all functional/visual profiles green but recorded soak_reset_outcome=failure and soak_outcome=failure before valid soak metrics
rejected_hypotheses:
  - product homepage regression: the initial HTTP 500 used an empty APP_KEY caused by the temporary workflow
  - functional or visual regression on 0b46bda79fabda043f79be2b68ce645e17c2b634: comprehensive run 29862854364 passed every executed functional, cross-browser, responsive, resilience, accessibility and visual profile
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-full-production-like-e2e-simulation.md
  - .github/workflows/full-production-like-e2e-simulation.yml
validation:
  - command: CI / Agent Governance / Platform DB Outage / Phase 7 on 0b46bda79fabda043f79be2b68ce645e17c2b634
    result: PASS
    evidence: runs 29862854727 / 29862854386 / 29862854558 / 29862854491
  - command: comprehensive browser and visual simulation run 29862854364
    result: PASS
    evidence: all functional and visual steps passed; artifact 8508221344, digest sha256:de0d86a8b7c1749c3f23c036bf3599860428adbafa9e2c01875555cef4f962a8
  - command: combined-job soak reset in run 29862854364
    result: FAIL
    evidence: artifact records soak_reset_outcome=failure and no soak-runtime-metrics.json; soak moved to fresh isolated job
  - command: final fresh-job comprehensive plus 300-second soak validation
    result: NOT_RUN
    evidence: fresh-job orchestration committed at 734601f9f7ee3ada809195ea7a51751647f86b4f; exact-head run pending
blockers:
  - none
next_action: inspect PR #115 exact-head comprehensive and independent 300-second soak jobs and record their final artifacts and metrics
```

## Notes

This task is validation-only. It must not weaken existing gates, introduce production secrets, mutate production, or promote controlled evidence beyond its environment boundary.
