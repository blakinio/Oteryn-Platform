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
updated_at: 2026-07-21T19:44:50Z
head: d2df6e730b6b502bde68e23c249da01bbbd0378c
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
  - CI run 29862494021 passed on validation head 62e4a28352ac00174e3ff7b17374c28db0fd4f7d
  - Agent Governance run 29862493873 passed on validation head 62e4a28352ac00174e3ff7b17374c28db0fd4f7d
  - Platform DB Outage Validation run 29862493825 passed on validation head 62e4a28352ac00174e3ff7b17374c28db0fd4f7d
  - Phase 7 Production-Like Validation run 29862494061 passed on validation head 62e4a28352ac00174e3ff7b17374c28db0fd4f7d
  - first temporary soak run failed before product assertions because the validation-only workflow generated an empty APP_KEY from invalid escaped PHP syntax
  - the temporary APP_KEY orchestration defect is corrected on branch head d2df6e730b6b502bde68e23c249da01bbbd0378c
  - production execution and external Canary/login-server writes are outside this task
  - repository writes are limited to blakinio/Oteryn-Platform
derived:
  - the initial homepage HTTP 500 in the temporary soak job was a validation-harness defect and is not evidence of a product regression
unknown:
  - outcome of the corrected fresh comprehensive validation run
conflicts: []
first_failure:
  marker: temporary full-production-like-e2e-simulation APP_KEY generation
  evidence: run 29862494074 job 88742473925 returned HTTP 500 for /; artifact 8507887932 showed 2-second soak failure; invalid php -r escaping reproduced with exit 255 and corrected in commit d2df6e730b6b502bde68e23c249da01bbbd0378c
rejected_hypotheses:
  - product homepage regression: the failure path used an empty APP_KEY caused by the temporary validation workflow before application behavior could be meaningfully assessed
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-full-production-like-e2e-simulation.md
  - .github/workflows/full-production-like-e2e-simulation.yml
validation:
  - command: CI / Agent Governance / Platform DB Outage / Phase 7 on 62e4a28352ac00174e3ff7b17374c28db0fd4f7d
    result: PASS
    evidence: runs 29862494021 / 29862493873 / 29862493825 / 29862494061
  - command: temporary comprehensive simulation run 29862494074
    result: FAIL
    evidence: harness-only APP_KEY generation defect; product result not classified from this run
  - command: corrected comprehensive simulation workflow
    result: NOT_RUN
    evidence: corrected orchestration committed at d2df6e730b6b502bde68e23c249da01bbbd0378c; exact-head rerun pending
blockers:
  - none
next_action: inspect the corrected PR #115 exact-head workflow runs and classify the first product or harness failure, or record passing evidence
```

## Notes

This task is validation-only. It must not weaken existing gates, introduce production secrets, mutate production, or promote controlled evidence beyond its environment boundary.
