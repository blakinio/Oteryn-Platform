---
task_id: OTERYN-20260721-e2e-public-dependency-recovery
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/adr/0008-risk-based-continuous-e2e-validation.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - scripts/acceptance/bootstrap-production-like.sh
  - scripts/acceptance/playwright.config.mjs
  - scripts/acceptance/tests/public-game-data-acceptance.spec.mjs
  - .github/workflows/acceptance-validation.yml
search_first:
  - open PRs and active tasks overlapping scripts/acceptance public dependency recovery or acceptance-validation.yml
  - existing public-game-data failure and Phase 7 dependency failure evidence before adding scenarios
  - reversible acceptance-scoped MariaDB grant and Redis ACL mechanisms before failure injection
optional_reads:
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
---

# OTERYN-20260721-e2e-public-dependency-recovery

## Goal

Close issue #105 with a bounded P1 Chromium resilience profile proving known-good public dependency behavior, deterministic fail-closed user-visible degradation, restoration of the affected acceptance-scoped dependency boundary and successful browser recovery on the same exact application SHA.

## Acceptance criteria

- [ ] Reuse the existing Playwright production-like acceptance harness and exact-SHA Laravel HTTP runtime.
- [ ] Add a dedicated Chromium `resilience` profile with zero retries; do not multiply the scenario across Firefox/WebKit or viewports.
- [ ] Prove `/online` known-good -> controlled Canary read SELECT denial -> HTTP 503 without sensitive diagnostics -> grant restoration -> successful public online-data read.
- [ ] Prove `/servers` known-good live Redis runtime -> controlled reversible removal of the runtime principal's `HMGET` permission -> bounded runtime-unavailable UI -> ACL restoration -> live runtime data visible again.
- [ ] Restore every mutated MariaDB grant and Redis ACL in `finally`/equivalent cleanup even when assertions fail.
- [ ] Keep the full primary Chromium acceptance baseline and existing portability/responsive profiles unchanged.
- [ ] Integrate the resilience profile into required `critical` PR validation and full acceptance execution without weakening Functional Acceptance classification rules.
- [ ] Record exact tested SHA, project/profile, zero-retry policy and measured resilience duration in non-secret acceptance evidence.
- [ ] Do not clear/bypass rate limits, weaken failure handling or move transaction/race correctness out of deterministic lower layers.
- [ ] Keep issue #91 independent and perform no production action.
- [ ] Perform no Canary/login-server repository writes.
- [ ] Update durable roadmap/test strategy/evidence and leave exactly one next action.

## Ownership

```yaml
owned_paths:
  - .github/workflows/acceptance-validation.yml
  - scripts/acceptance/**
  - docs/testing/E2E_PUBLIC_DEPENDENCY_RECOVERY_EVIDENCE.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-public-dependency-recovery.md
modules:
  - testing
  - acceptance-e2e
  - resilience
  - ci
dependencies:
  - issue #105
  - existing acceptance-scoped MariaDB/Redis dependency harness
  - existing public game-data failure semantics
  - issue #91 remains production-only and independent
blockers:
  - none
cross_repository_tasks:
  - none; Canary/login-server repositories remain read-only
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T16:10:00Z
head: 18bd5b2c3b4496677cc58df41fd50c6387e9e6f8
branch: task/OTERYN-20260721-e2e-public-dependency-recovery
pr: none
status: investigating
context_routes:
  - testing
  - architecture
  - public-game-data
  - canary-integration
  - ci-repair
  - agent-governance
owned_paths:
  - .github/workflows/acceptance-validation.yml
  - scripts/acceptance/**
  - docs/testing/E2E_PUBLIC_DEPENDENCY_RECOVERY_EVIDENCE.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-public-dependency-recovery.md
proven:
  - issue 105 is the dedicated tracker for public dependency failure recovery
  - no repository staging E2E task was active before this task
  - existing public-game-data acceptance already proves Canary SELECT denial yields HTTP 503 and restores the grant in cleanup but does not perform a post-restoration browser request proving recovery
  - existing Redis acceptance proves missing and malformed runtime state and Phase 7 proves unavailable Redis behavior at the reader boundary but browser acceptance does not prove controlled runtime-command denial followed by restoration and successful live-data recovery
  - acceptance Redis user is acceptance-scoped with key pattern cluster:channel:*:runtime and commands HMGET PTTL PING SELECT
  - Redis ACL SETUSER command rules support removing one command with -<command> and restoring it with +<command>; the task will mutate only the acceptance-scoped runtime user
  - /servers catches runtime dependency exceptions and renders a bounded runtime-unavailable snapshot while configured metadata remains available
  - /online converts Canary query failures into HTTP 503
  - issue 91 remains production-only and no staging result may be promoted to PRODUCTION_PROVEN
derived:
  - a single Chromium recovery profile adds unique composed evidence without cross-browser multiplication
  - Redis HMGET permission denial is reversible and exercises the real runtime reader failure path without killing the shared Redis service
  - successful post-restoration browser requests are the missing assertion that distinguishes recovery evidence from existing failure-only evidence
unknown:
  - whether the current phpredis connection observes incremental HMGET ACL removal immediately in the acceptance runtime until exact-SHA execution
  - exact bounded /servers rendering after ACL denial until browser execution confirms the expected unavailable snapshot
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - stopping the entire Redis service is required to test recoverable runtime failure
  - failure-only browser evidence proves dependency recovery
  - the resilience scenario should run across every browser and viewport
  - production dependency access is needed for this staging mechanism proof
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-e2e-public-dependency-recovery.md
validation:
  - command: repository failure-coverage and dependency-harness inspection
    result: PASS
    evidence: existing public-game-data browser failure path, Redis ACL bootstrap, controller/service fail-closed behavior and Phase 7 dependency checks inspected before implementation
blockers:
  - none
next_action: Implement the dedicated Chromium resilience spec/project and wire it into critical/full acceptance evidence with deterministic cleanup.
```

## Notes

This task proves restoration and user-visible recovery in the controlled acceptance environment. It does not claim production dependency failover, HA, recovery timing or `PRODUCTION_PROVEN` behavior.