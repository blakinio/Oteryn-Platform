---
task_id: OTERYN-20260721-e2e-coverage-hardening
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/adr/0008-risk-based-continuous-e2e-validation.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/testing/E2E_SECURITY_BOUNDARY_EVIDENCE.md
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - .github/workflows/acceptance-validation.yml
  - scripts/acceptance/playwright.config.mjs
search_first:
  - open PRs and active tasks overlapping scripts/acceptance/** or acceptance-validation.yml
  - existing Playwright specs/helpers before adding new harness abstractions
  - existing feature/integration/failure-path tests before duplicating invariants at browser level
optional_reads:
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
  - docs/testing/PRODUCTION_SMOKE_CHECKLIST.md
---

# OTERYN-20260721-e2e-coverage-hardening

## Goal

Establish the durable risk-based E2E hardening architecture and deliver the first bounded release-critical browser slices on top of the already `STAGING_PROVEN` functional acceptance baseline. Keep lower-level race/integrity proofs in deterministic integration tests, preserve secret-safe exact-SHA evidence and keep production-only verification independent.

## Acceptance criteria

- [x] Reconcile the durable test architecture with the delivered Playwright production-like harness.
- [x] Preserve the full primary Chromium exact-SHA acceptance baseline and secret-safe artifact rules.
- [x] Add bounded Chromium/Firefox/WebKit portability coverage for representative critical journeys.
- [x] Add bounded desktop/tablet/mobile responsive coverage without multiplying the complete visual collector.
- [x] Add required browser-visible security coverage for session rotation/cookie attributes and representative foreign ownership manipulation.
- [x] Keep transaction races, locking, uniqueness, ambiguous commits and database integrity in deterministic lower layers unless browser UX adds unique evidence.
- [x] Record exact tested SHA/browser/profile evidence and first-failure root causes without hiding failures behind retries or limiter bypasses.
- [x] Split existing-data migration/upgrade/controlled rollback into issue #98 because it belongs with release/deployment validation and is larger than this bounded browser slice.
- [x] Keep dependency-interruption/recovery, observability correlation, deeper accessibility interaction and soak/flakiness work as additive follow-up slices under `docs/testing/E2E_COVERAGE_ROADMAP.md`.
- [x] Keep final production smoke/E2E under issue #91; never promote staging evidence to `PRODUCTION_PROVEN`.
- [x] Maintain a compact durable checkpoint with exactly one next action.

## Ownership

```yaml
owned_paths:
  - .github/workflows/acceptance-validation.yml
  - scripts/acceptance/**
  - tests/**
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/adr/0008-risk-based-continuous-e2e-validation.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/testing/E2E_SECURITY_BOUNDARY_EVIDENCE.md
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - docs/testing/PRODUCTION_SMOKE_CHECKLIST.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-coverage-hardening.md
modules:
  - testing
  - acceptance-e2e
  - ci
  - security-validation
dependencies:
  - issue #98 owns the follow-up P0 existing-data migration/upgrade/rollback validation slice
  - issue #91 remains the separate production-only execution tracker
  - existing Phase 7 and Platform DB outage validation remain independent evidence layers
blockers:
  - none
cross_repository_tasks:
  - none; Canary/login-server repositories remain read-only unless separately authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T15:08:00Z
head: 470f602a5e02fcfacb0ad2adb2b665338a710dd4
branch: task/OTERYN-20260721-e2e-coverage-hardening
pr: 94
status: investigating
context_routes:
  - testing
  - architecture
  - security
  - ci-repair
  - agent-governance
owned_paths:
  - .github/workflows/acceptance-validation.yml
  - scripts/acceptance/**
  - tests/**
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/adr/0008-risk-based-continuous-e2e-validation.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/testing/E2E_SECURITY_BOUNDARY_EVIDENCE.md
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - docs/testing/PRODUCTION_SMOKE_CHECKLIST.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-coverage-hardening.md
proven:
  - PR 94 is the dedicated implementation PR and the task branch is synchronized with current main before the security-slice documentation commits
  - full primary Chromium production-like acceptance remains preserved
  - bounded portability runs representative critical flows on Chromium Firefox and WebKit with zero configured retries
  - bounded responsive runs representative critical flows on desktop tablet and mobile Chromium profiles
  - corrected portability runs exposed and fixed identity-scoped MFA limiter fixture coupling without clearing bypassing or weakening production limiters
  - browser security smoke proves authentication rotates the browser session identifier and observes HttpOnly SameSite=Lax path=/ on the controlled acceptance HTTP boundary without recording cookie values
  - browser ownership smoke proves an injected foreign account_id cannot override the authenticated Identity server-owned Canary binding during character creation
  - initial security ownership run 29841948505 failed because a Platform-only ready-state fixture did not create the real Canary account row required by character creation; this was fixture misuse rather than an ownership defect
  - corrected security implementation head 4fdbe99b30c5c43c62e41405e6d98cf7d8f3b3d3 passed Acceptance E2E and Visual UX run 29842195691 including smoke portability and responsive profiles
  - the same corrected head passed CI 29842193444 Agent Governance 29842196941 Phase 7 Production-Like Validation 29842195562 and Platform DB Outage Validation 29842194228
  - docs/testing/E2E_SECURITY_BOUNDARY_EVIDENCE.md records the bounded non-secret exact-SHA evidence and production-evidence boundary
  - issue 98 tracks the separate P0 existing-data migration upgrade and controlled rollback slice
  - issue 91 remains the independent Production Go-Live Gate execution tracker
  - no Canary or login-server repository writes were performed
  - session Secure behavior on the final TLS production boundary is not claimed from the plain-HTTP acceptance harness
derived:
  - the first security ownership failure was deterministic fixture misuse because the corrected real registration/provisioning setup passed without application changes
  - P0 browser portability responsive and representative security abuse coverage are now suitable as the bounded required PR signal while the complete secret-sensitive suite remains primary-Chromium only
  - migration rollback resilience observability deeper accessibility and soak work should remain separate bounded slices rather than expanding PR 94 indefinitely
unknown:
  - final required-check outcome on the documentation-updated current PR head
  - long-term repeated-run Firefox/WebKit flakiness beyond the corrected zero-retry measurements already recorded
  - final production TLS Secure-cookie proxy and edge behavior until issue 91 is directly executed
conflicts: []
first_failure:
  marker: security ownership fixture lacked real Canary account row
  evidence: Acceptance E2E run 29841948505 on f9af89dfd017bfa0417d88503277a535e6f9b7a4 failed with user-visible `Your bound game account is unavailable.` before ownership assertion; corrected by using real registration/provisioning fixtures
rejected_hypotheses:
  - full browser E2E should replace deterministic integration/concurrency tests
  - the complete acceptance suite should run across every browser and viewport by default
  - rate-limit cache clearing retries sleeps or middleware bypasses are acceptable ways to make the browser matrix green
  - the first WebKit portability failure was a product browser incompatibility
  - the first security ownership failure was a server-side ownership authorization defect
  - staging evidence can substitute for final production verification
changed_paths:
  - .github/workflows/acceptance-validation.yml
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-coverage-hardening.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/adr/0008-risk-based-continuous-e2e-validation.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/testing/E2E_SECURITY_BOUNDARY_EVIDENCE.md
  - scripts/acceptance/package.json
  - scripts/acceptance/playwright.config.mjs
  - scripts/acceptance/seed-browser-admin.php
  - scripts/acceptance/tests/portability-critical.spec.mjs
  - scripts/acceptance/tests/responsive-critical.spec.mjs
  - scripts/acceptance/tests/security-boundary-critical.spec.mjs
validation:
  - command: node --check scripts/acceptance/tests/security-boundary-critical.spec.mjs
    result: PASS
    evidence: prepared security-boundary spec parsed successfully
  - command: Acceptance E2E and Visual UX run 29841948505 on f9af89dfd017bfa0417d88503277a535e6f9b7a4
    result: FAIL
    evidence: session security scenario passed; ownership scenario failed on invalid fixture setup before ownership assertion
  - command: corrected exact-SHA required checks on 4fdbe99b30c5c43c62e41405e6d98cf7d8f3b3d3
    result: PASS
    evidence: CI Agent Governance Platform DB Outage Phase 7 Production-Like Validation and Acceptance E2E and Visual UX all passed; acceptance smoke portability and responsive steps all passed
blockers:
  - none
next_action: Verify all required checks on the documentation-updated current PR head, then merge PR #94 if the merge gate remains satisfied.
```

## Notes

This PR deliberately stops at the bounded architecture + portability/responsive + representative browser-security slice. P0 migration/rollback is issue #98; production verification is issue #91. Future resilience, observability, deeper accessibility and soak work remain incremental roadmap slices rather than prerequisites for preserving the already-closed Functional Acceptance result.
