---
task_id: OTERYN-20260721-e2e-public-dependency-recovery
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/adr/0008-risk-based-continuous-e2e-validation.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/testing/E2E_PUBLIC_DEPENDENCY_RECOVERY_EVIDENCE.md
  - scripts/acceptance/bootstrap-production-like.sh
  - scripts/acceptance/playwright.config.mjs
  - scripts/acceptance/tests/resilience-critical.spec.mjs
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

- [x] Reuse the existing Playwright production-like acceptance harness and exact-SHA Laravel HTTP runtime.
- [x] Add a dedicated Chromium `resilience` profile with zero retries; do not multiply the scenario across Firefox/WebKit or viewports.
- [x] Prove `/online` known-good -> controlled Canary read SELECT denial -> HTTP 503 without sensitive diagnostics -> grant restoration -> successful public online-data read.
- [x] Prove `/servers` known-good live Redis runtime -> controlled reversible removal of the runtime principal's `HMGET` permission -> bounded runtime-unavailable UI -> ACL restoration -> live runtime data visible again.
- [x] Restore every mutated MariaDB grant and Redis ACL in `finally`/equivalent cleanup even when assertions fail.
- [x] Keep the full primary Chromium acceptance baseline and existing portability/responsive profiles unchanged.
- [x] Integrate the resilience profile into required `critical` PR validation and full acceptance execution without weakening Functional Acceptance classification rules.
- [x] Record exact tested SHA, project/profile, zero-retry policy and measured resilience duration in non-secret acceptance evidence.
- [x] Do not clear/bypass rate limits, weaken failure handling or move transaction/race correctness out of deterministic lower layers.
- [x] Keep issue #91 independent and perform no production action.
- [x] Perform no Canary/login-server repository writes.
- [x] Update durable roadmap/test strategy/evidence and leave exactly one next action.

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
updated_at: 2026-07-21T16:24:00Z
head: 43533770039f495752cb2915ed4cf01f06690e3e
branch: task/OTERYN-20260721-e2e-public-dependency-recovery
pr: 106
status: ready
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
  - issue 105 is the dedicated tracker and PR 106 is the dedicated implementation PR
  - existing public-game-data acceptance proved Canary SELECT denial but lacked a post-restoration browser request proving recovery
  - existing Redis acceptance and Phase 7 covered missing malformed or unavailable runtime behavior but lacked browser-visible ACL denial restoration and live recovery
  - resilience-critical.spec.mjs runs serially on resilience-chromium with zero retries and uses acceptance-scoped dependencies only
  - Canary resilience proves initial HTTP 200 and Acceptance Hero visibility then controlled cluster_sessions SELECT denial HTTP 503 bounded diagnostics grant restoration and successful subsequent HTTP 200 with Acceptance Hero visible again
  - Redis resilience proves initial ONLINE runtime and one player then controlled removal of HMGET from the acceptance runtime user bounded runtime-unavailable UI ACL restoration and subsequent ONLINE runtime recovery
  - both MariaDB and Redis mutations are restored in finally cleanup so later validation is not contaminated
  - the full primary Chromium acceptance baseline remains separate from resilience and the portability responsive browser matrices remain bounded
  - required pull-request critical execution now composes smoke portability responsive and resilience; any profile failure makes the aggregate critical result fail
  - full acceptance now requires both the primary full baseline and resilience before FUNCTIONAL_ACCEPTANCE_STAGING_PROVEN or visual collection can succeed
  - durable acceptance evidence records resilience project result duration and zero-retry policy without storing credentials or dependency secrets
  - first implementation head 7f21ac65bad1da9514d0e1d6ade48a2da9ee8918 passed Acceptance E2E and Visual UX run 29847628355 including smoke portability responsive and resilience
  - the same head passed CI 29847629469 Agent Governance 29847629232 Phase 7 Production-Like Validation 29847629405 and Platform DB Outage Validation 29847628752
  - acceptance artifact acceptance-e2e-critical-29847628355-1 id 8502051195 digest sha256:87fa7d58515961c9fbd9c69632d8a114684727d532f0c82442065a940511a46e records resilience_result success
  - first implementation run measured smoke 9 seconds portability 23 seconds responsive 10 seconds and resilience 3 seconds wall-clock with resilience retries configured to zero
  - the real acceptance runtime observed Redis HMGET ACL removal immediately enough to exercise the bounded unavailable UI and successful restoration without service restart
  - docs/testing/E2E_PUBLIC_DEPENDENCY_RECOVERY_EVIDENCE.md records the exact-SHA recovery evidence and production boundary
  - docs/testing/E2E_COVERAGE_ROADMAP.md and docs/architecture/TEST_STRATEGY.md now record resilience-chromium as an implemented required bounded profile rather than deferred work
  - issue 91 remains production-only and no staging result is promoted to PRODUCTION_PROVEN
  - no Canary or login-server repository writes were performed
derived:
  - successful post-restoration browser requests close a distinct evidence gap beyond failure-only tests
  - single-browser resilience gives the composed recovery signal without unjustified cross-browser multiplication
  - additional resilience scenarios should be added only when they prove a new recovery boundary beyond Canary read Redis runtime Phase 7 and Platform DB outage evidence
unknown:
  - final required-check outcome on the documentation-updated current PR head
  - production dependency HA failover recovery timing grants ACLs and network behavior until issue 91 is directly executed
  - long-term repeated-run stability of the resilience profile beyond current exact-SHA evidence
conflicts: []
first_failure:
  marker: none
  evidence: first integrated resilience run passed both dependency recovery scenarios without product or harness workaround
rejected_hypotheses:
  - stopping the entire Redis service is required to test recoverable runtime failure
  - failure-only browser evidence proves dependency recovery
  - the resilience scenario should run across every browser and viewport
  - production dependency access is needed for this staging mechanism proof
  - retries or dependency reset shortcuts are required to make recovery validation stable
changed_paths:
  - .github/workflows/acceptance-validation.yml
  - scripts/acceptance/package.json
  - scripts/acceptance/playwright.config.mjs
  - scripts/acceptance/tests/resilience-critical.spec.mjs
  - docs/testing/E2E_PUBLIC_DEPENDENCY_RECOVERY_EVIDENCE.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-public-dependency-recovery.md
validation:
  - command: repository failure-coverage and dependency-harness inspection
    result: PASS
    evidence: existing browser failure paths Redis ACL bootstrap controller/service behavior and Phase 7 dependency checks inspected before implementation
  - command: Acceptance E2E and Visual UX run 29847628355 on 7f21ac65bad1da9514d0e1d6ade48a2da9ee8918
    result: PASS
    evidence: smoke portability responsive and resilience steps passed; aggregate critical evidence reports AUTOMATED_E2E_CRITICAL_PASS
  - command: CI run 29847629469 on 7f21ac65bad1da9514d0e1d6ade48a2da9ee8918
    result: PASS
    evidence: required repository CI passed
  - command: Agent Governance run 29847629232 on 7f21ac65bad1da9514d0e1d6ade48a2da9ee8918
    result: PASS
    evidence: governance checks passed
  - command: Phase 7 Production-Like Validation run 29847629405 on 7f21ac65bad1da9514d0e1d6ade48a2da9ee8918
    result: PASS
    evidence: independent production-like validation remained green
  - command: Platform DB Outage Validation run 29847628752 on 7f21ac65bad1da9514d0e1d6ade48a2da9ee8918
    result: PASS
    evidence: independent Platform DB outage validation remained green
blockers:
  - none
next_action: Verify all required checks on the documentation-updated current PR head and merge PR #106 only if the exact-head merge gate remains satisfied.
```

## Notes

This task proves restoration and user-visible recovery in the controlled acceptance environment. It does not claim production dependency failover, HA, recovery timing or `PRODUCTION_PROVEN` behavior.