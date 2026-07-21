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
  - scripts/acceptance/tests/soak-public.spec.mjs
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
updated_at: 2026-07-21T20:01:00Z
head: b26785fe0ec8db4054b73a0ad6f242b0280cafca
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
  - scripts/acceptance/tests/soak-public.spec.mjs
proven:
  - main head at task start is 0bc273816dcf515cf264652cabe8b8a3c2f95b59
  - no active repository/staging implementation task is listed in ACTIVE_WORK
  - no open pull request overlapped this validation task at preflight
  - draft PR #115 targets main from the dedicated task branch
  - CI, Agent Governance, Platform DB Outage Validation and Phase 7 Production-Like Validation passed on validation head 0b46bda79fabda043f79be2b68ce645e17c2b634
  - comprehensive run 29862854364 passed full Chromium, zero-retry Chromium/Firefox/WebKit portability, desktop/tablet/mobile responsive, dependency failure-restoration-recovery, keyboard-focus accessibility and exploratory visual/accessibility execution
  - run 29862854364 produced 15 full Chromium tests, 12 portability tests, 9 responsive tests, 2 resilience tests and 3 accessibility tests with zero test failures in JUnit evidence
  - visual evidence from run 29862854364 contains 71 screenshots with zero status mismatches, horizontal-overflow surfaces, unlabeled-control surfaces, sampled low-contrast surfaces, focus-not-observed surfaces or raw technical-message surfaces
  - artifact 8508221344 from run 29862854364 has digest sha256:de0d86a8b7c1749c3f23c036bf3599860428adbafa9e2c01875555cef4f962a8
  - first temporary soak attempt failed before product assertions because the validation-only workflow generated an empty APP_KEY from invalid escaped PHP syntax
  - second temporary combined-job design completed all functional and visual profiles but its in-job dependency reset before soak failed
  - fresh isolated soak run 29863729939 reached the real soak test and failed after 13 seconds on the /servers assertion while the captured accessibility tree showed the Acceptance server card correctly rendered Runtime: ONLINE
  - existing soak-public.spec.mjs asserted a standalone exact-text element ONLINE even though the UI exposes Runtime: and ONLINE within the same paragraph
  - soak assertion was corrected to validate Runtime: ONLINE on the Acceptance server article in commit b26785fe0ec8db4054b73a0ad6f242b0280cafca
  - failed fresh soak artifact 8508381167 has digest sha256:a285f53cd7ab71594a5c8b7e10834b5a446e7f814aa29844d3e23a679281120d
  - production execution and external Canary/login-server writes are outside this task
  - repository writes are limited to blakinio/Oteryn-Platform
derived:
  - the fresh soak failure is a reusable E2E locator defect rather than evidence that /servers runtime state is incorrect
  - the permanent soak assertion fix is justified because a real reusable coverage defect was proven by exact-head browser evidence
unknown:
  - outcome and calibration metrics of the corrected 300-second zero-retry soak
conflicts: []
first_failure:
  marker: soak-public /servers locator expected a standalone exact text element ONLINE
  evidence: run 29863729939 job 88746612767; JUnit shows getByText('ONLINE', { exact: true }) timeout while the captured page tree shows the Acceptance server card with Runtime: ONLINE
rejected_hypotheses:
  - product homepage regression: the initial HTTP 500 used an empty APP_KEY caused by the temporary workflow
  - functional or visual regression on 0b46bda79fabda043f79be2b68ce645e17c2b634: comprehensive run 29862854364 passed every executed functional, cross-browser, responsive, resilience, accessibility and visual profile
  - server runtime unavailable during fresh soak: the failure evidence itself shows the Acceptance server rendered Runtime: ONLINE
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-full-production-like-e2e-simulation.md
  - .github/workflows/full-production-like-e2e-simulation.yml
  - scripts/acceptance/tests/soak-public.spec.mjs
validation:
  - command: CI / Agent Governance / Platform DB Outage / Phase 7 on 0b46bda79fabda043f79be2b68ce645e17c2b634
    result: PASS
    evidence: runs 29862854727 / 29862854386 / 29862854558 / 29862854491
  - command: comprehensive browser and visual simulation run 29862854364
    result: PASS
    evidence: all functional and visual steps passed; artifact 8508221344, digest sha256:de0d86a8b7c1749c3f23c036bf3599860428adbafa9e2c01875555cef4f962a8
  - command: fresh isolated soak run 29863729939 before locator correction
    result: FAIL
    evidence: one soak test failed on stale exact-text locator; page evidence rendered Runtime: ONLINE; artifact 8508381167
  - command: corrected exact-head comprehensive plus fresh 300-second soak validation
    result: NOT_RUN
    evidence: soak locator correction committed at b26785fe0ec8db4054b73a0ad6f242b0280cafca; exact-head validation pending
blockers:
  - none
next_action: inspect PR #115 exact-head runs after the soak locator fix and record passing soak calibration evidence or the next first failure
```

## Notes

This task is validation-only. It must not weaken existing gates, introduce production secrets, mutate production, or promote controlled evidence beyond its environment boundary.
