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
updated_at: 2026-07-21T19:39:41Z
head: 605338fe4b1e2dae3383c434d0d39cd3a51388a2
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
  - temporary comprehensive validation orchestration is committed on the dedicated task branch
  - draft PR #115 targets main from the dedicated task branch
  - production execution and external Canary/login-server writes are outside this task
  - repository writes are limited to blakinio/Oteryn-Platform
derived:
  - PR #115 can provide fresh exact-head staging evidence while keeping production state untouched
unknown:
  - outcome of the fresh comprehensive validation run
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses: []
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-full-production-like-e2e-simulation.md
  - .github/workflows/full-production-like-e2e-simulation.yml
validation:
  - command: Full Production-Like E2E Simulation plus standard PR validation workflows
    result: NOT_RUN
    evidence: PR #115 opened; exact-head runs pending
blockers:
  - none
next_action: inspect PR #115 exact-head workflow runs and classify the first failure or record passing evidence
```

## Notes

This task is validation-only. It must not weaken existing gates, introduce production secrets, mutate production, or promote controlled evidence beyond its environment boundary.
