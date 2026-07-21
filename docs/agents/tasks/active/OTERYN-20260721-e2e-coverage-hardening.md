---
task_id: OTERYN-20260721-e2e-coverage-hardening
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
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

Expand Oteryn Platform validation beyond the already `STAGING_PROVEN` functional acceptance baseline using a risk-based, layered E2E hardening program. Add browser/system tests only where they prove behavior that lower layers cannot prove efficiently, while preserving exact-SHA evidence, secret-safe diagnostics and the separation between staging evidence and final production verification.

## Acceptance criteria

- [x] Reconcile the durable test architecture with the already-delivered Playwright production-like acceptance harness.
- [x] Preserve the current exact-SHA Chromium production-like acceptance baseline and its secret-safe artifact rules.
- [ ] Implement the highest-value P0 coverage from `docs/testing/E2E_COVERAGE_ROADMAP.md`, splitting follow-up PRs when required to keep changes bounded.
- [x] Add deterministic browser portability coverage for a bounded critical smoke subset across Chromium, Firefox and WebKit, unless a concrete compatibility or CI constraint is evidenced and documented.
- [x] Add responsive/mobile browser coverage for critical public, Identity/account and privileged flows at representative desktop, tablet and mobile viewports without duplicating the full visual collector on every project/browser combination.
- [ ] Add browser-visible security-boundary regressions where the browser layer adds unique evidence, including unauthorized/foreign authority rejection and security-sensitive session/cookie behavior that is observable without exposing secrets.
- [ ] Add controlled dependency-interruption/recovery scenarios only where the production-like harness can inject them deterministically and prove fail-closed behavior plus recovery; do not duplicate existing Phase 7 or Platform DB outage evidence without a new assertion.
- [x] Keep concurrency, transaction races, DB integrity and ambiguous-commit invariants primarily in deterministic integration tests; add E2E only for user-visible outcomes that cannot be proven below the browser/system boundary.
- [ ] Define and implement a migration/upgrade/rollback validation slice for a representative existing-data state without using production data.
- [ ] Add observability assertions for correlation/audit/log outcomes where sanitized, deterministic evidence is available.
- [x] Define soak/flakiness execution as a non-blocking scheduled/manual profile unless evidence justifies promoting a bounded subset into required PR CI.
- [x] Keep final production smoke/E2E under issue #91 and `docs/testing/PRODUCTION_SMOKE_CHECKLIST.md`; never relabel staging evidence as `PRODUCTION_PROVEN`.
- [x] Tie durable evidence to exact tested SHA/browser/profile and keep raw traces/screenshots disabled for secret-bearing flows unless sanitized by construction.
- [x] Update task checkpoint and relevant architecture/testing docs with implemented coverage, deferred items and exactly one next action.

## Ownership

```yaml
owned_paths:
  - .github/workflows/acceptance-validation.yml
  - scripts/acceptance/**
  - tests/**
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/ROADMAP.md
  - docs/architecture/adr/0008-risk-based-continuous-e2e-validation.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
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
  - issue #91 remains the separate production-only execution tracker
  - existing Phase 7 production-like validation and Platform DB outage validation remain independent evidence layers
blockers:
  - current main advanced after final-slice validation and the task branch is now 2 commits behind; synchronize and revalidate the combined state before merge readiness
cross_repository_tasks:
  - none; Canary/login-server repositories remain read-only unless separately authorized
```

## Context checkpoint

`checkpoint_version` must match `shared_checkpoint_contract.version` in `docs/agents/GOVERNANCE_CONTRACT.json`. Validate the completed checkpoint with `python tools/agents/checkpoint.py <task-path> --require-checkpoint`.

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T14:40:12Z
head: 6e5ba437280da55b1d99ebee65d020948b4890cd
branch: task/OTERYN-20260721-e2e-coverage-hardening
pr: 94
status: blocked
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
  - docs/architecture/ROADMAP.md
  - docs/architecture/adr/0008-risk-based-continuous-e2e-validation.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - docs/testing/PRODUCTION_SMOKE_CHECKLIST.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-coverage-hardening.md
proven:
  - PR 94 remains the open draft implementation PR on branch task/OTERYN-20260721-e2e-coverage-hardening and was mergeable when rechecked after main advanced
  - current main advanced from 990ebdbbdbeb7c2c1671d715b49e4e17bea7785c to efcedeb16649262ace0045520e44eeb2f3364cd1 after final-slice validation; the task branch is now 26 commits ahead and 2 commits behind with merge base 990ebdbbdbeb7c2c1671d715b49e4e17bea7785c
  - the two new main commits add public portal visual-identity changes in public/css/portal.css, public/images/oteryn-sigil.svg, resources/views/game/layout.blade.php and resources/views/home.blade.php plus an archived task record
  - the new main commits have no direct file-path overlap with the PR 94 12-file diff, but they modify public UI surfaces exercised by the new portability/responsive acceptance coverage, so the combined state has not yet been exact-head tested
  - no other open user PR or active task overlapped scripts/acceptance/** or .github/workflows/acceptance-validation.yml at continuation preflight
  - existing production-like Playwright acceptance retains exact-SHA execution, one worker and the full primary Chromium 1440x1000 baseline
  - full acceptance evidence remains STAGING_PROVEN for the currently delivered staging-verifiable functional surface; final Production Go-Live Gate remains PENDING PRODUCTION VERIFICATION under issue 91
  - existing acceptance helpers already provide sanitized browser diagnostics, deterministic identities/fixtures, TOTP support and exact-tested-SHA attachments, so no duplicate general fixture abstraction was introduced
  - existing feature and real-MariaDB integration coverage retains deterministic ownership for CMS authorization/publication invariants and character transaction, locking, uniqueness, race and ambiguous-recovery correctness
  - existing Phase 7 and Platform DB outage workflows already cover production-like deployment/privilege and deterministic Platform DB outage/recovery boundaries, so this slice does not duplicate those invariants in browser E2E
  - the bounded implementation adds a preserved chromium-primary project, Chromium/Firefox/WebKit portability projects and Chromium desktop/tablet/mobile responsive projects with dedicated critical specs rather than multiplying the full suite
  - the portability subset covers public navigation/game data, Identity login/logout, authenticated Account Overview, MFA-confirmed privileged access, authorization denial and deterministic seeded CMS/public visibility
  - the responsive subset covers public navigation, Identity entry forms, authenticated Account Overview, MFA challenge and privileged administrator surfaces with horizontal-overflow/accessibility smoke at desktop 1440x1000, tablet 820x1180 and mobile 390x844
  - raw Playwright trace/screenshot/video remain disabled for the new secret-bearing portability/responsive flows; richer automatic artifacts remain limited to the pre-existing non-secret smoke or sanitized-by-construction paths
  - workflow evidence records exact tested SHA, execution profile, browsers, Playwright projects, viewport profiles, wall-clock profile durations and zero configured retries; only the full profile can assert FUNCTIONAL_ACCEPTANCE_STAGING_PROVEN
  - profile-specific JUnit is preserved before subsequent profile invocations and includes Playwright project identity in test names for browser/viewport-level measurement
  - browser validation touches Identity/MFA/admin trust boundaries only as test coverage; no application authentication/authorization semantics, schema, session contract, Canary/login-server compatibility or production configuration changed, and no rollback is required
  - Agent Governance failure on the original checkpoint was fixed by replacing unsupported status ready-for-p0-implementation with a supported checkpoint status; subsequent governance runs passed
  - first critical CI run 29837186868 passed primary Chromium smoke, then failed only the portability WebKit authorization-denial scenario because repeated reuse of the same seeded identity consumed the identity-scoped MFA enrollment limiter across browser projects
  - failure artifact for run 29837186868 recorded HTTP 429 Too Many Requests on the sixth identity-mfa-enrollment request; AppServiceProvider defines identity-mfa-enrollment as 5 requests per minute keyed by authenticated identity plus source IP
  - the rate-limit root cause was fixed by seeding unique regular identities per browser project and unique MFA-confirmed platform_admin identities per browser/viewport project, without clearing or bypassing production rate limiters
  - corrected exact-SHA acceptance run 29838591467 on d6b800da4e212fce7986aabe80d8c461c65cf020 passed smoke, portability and responsive profiles with zero configured retries
  - run 29838591467 measured smoke 6 seconds wall-clock, portability 25 seconds wall-clock and responsive 9 seconds wall-clock; portability JUnit test times were Chromium 3.006 seconds, Firefox 5.467 seconds and WebKit 11.488 seconds
  - final-slice exact-SHA acceptance run 29839315020 on 28ed7f2675ef170ec7f2559513eb7819f2a5756c also passed smoke, portability and responsive profiles with zero configured retries
  - run 29839315020 measured smoke 5 seconds wall-clock, portability 34 seconds wall-clock and responsive 10 seconds wall-clock; portability JUnit test times were Chromium 3.034 seconds, Firefox 5.532 seconds and WebKit 20.225 seconds
  - final-slice responsive JUnit on 28ed7f2675ef170ec7f2559513eb7819f2a5756c contained 9 passing tests with zero failures/errors/skips and test times desktop 1.963 seconds, tablet 1.944 seconds and mobile 2.021 seconds
  - two corrected exact-SHA matrix runs completed without failures, skips or retries; observed portability wall-clock varied from 25 to 34 seconds and WebKit test time varied from 11.488 to 20.225 seconds, so repeated-run stability remains a measured limitation rather than a proven property
  - all five workflows on head 6e5ba437280da55b1d99ebee65d020948b4890cd passed before the subsequent main drift: CI 29839723184, Agent Governance 29839723232, Platform DB Outage Validation 29839723401, Phase 7 Production-Like Validation 29839723221 and Acceptance E2E and Visual UX 29839723472
  - docs/architecture/TEST_STRATEGY.md and docs/testing/E2E_COVERAGE_ROADMAP.md record the implemented profile architecture, exact measured durations from the first corrected run, first-run fixture-coupling failure classification, zero-retry policy and deferred broader P0/P1/P2 work
  - PR 94 changed-file scope contains exactly 12 expected acceptance, architecture/testing and agent-governance files and no Canary or login-server repository changes
derived:
  - the first failed portability run exposed shared test-state coupling rather than a WebKit product compatibility defect
  - unique per-project regular and privileged identities preserve the same browser-visible assertions while respecting production rate limiters instead of adding retries, sleeps, cache clears or middleware bypasses
  - WebKit is the dominant measured cost within the bounded portability profile and showed material execution-time variance across the two corrected runs; this supports keeping the full secret-sensitive suite off the Firefox/WebKit matrix until scheduled repeated-run evidence justifies expansion
  - two corrected zero-retry runs support acceptance of this bounded implementation slice but do not prove long-term flake-free behavior
  - full cross-product execution of every secret-sensitive flow across every browser and viewport remains unjustified by the current measured evidence
  - because current main changes public layout/home surfaces that are exercised by the new tests, branch-head green evidence obtained before that drift is insufficient for merge readiness even though there is no direct changed-file conflict
unknown:
  - outcome and timing of the bounded critical profile after synchronizing PR 94 with current main efcedeb16649262ace0045520e44eeb2f3364cd1
  - long-term repeated-run flakiness characteristics of Firefox/WebKit beyond the two corrected zero-retry exact-SHA runs
  - whether broader full-suite Firefox/WebKit execution would uncover additional defects sufficient to justify its higher cost and secret-sensitive surface multiplication
  - which remaining dependency-interruption cases add unique evidence beyond existing Phase 7 and outage workflows until the P1 implementation-level search
conflicts:
  - main advanced after the final-slice green run and changed public UI surfaces covered by the new E2E subset; synchronize before merge readiness
first_failure:
  marker: Acceptance portability WebKit MFA enrollment rate-limit coupling
  evidence: run 29837186868 job 88656529958; portability-critical authorization-denial scenario reached a 429 Too Many Requests page because the same seeded identity generated six MFA enrollment requests across three browser projects against a 5-per-minute identity-scoped limiter
rejected_hypotheses:
  - full browser E2E should replace deterministic integration/concurrency tests
  - staging acceptance evidence can substitute for final production verification
  - the entire acceptance suite should run on every browser and viewport by default
  - a new general acceptance fixture/helper layer is required for the first portability/responsive slice
  - the first WebKit failure demonstrates a WebKit rendering or product-authorization defect
  - acceptance should clear rate-limit cache or add retries to force the browser matrix green
  - no direct changed-file overlap with new main commits is sufficient to treat pre-drift acceptance evidence as merge-ready combined-state evidence
changed_paths:
  - .github/workflows/acceptance-validation.yml
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260721-e2e-coverage-hardening.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/adr/0008-risk-based-continuous-e2e-validation.md
  - docs/testing/E2E_COVERAGE_ROADMAP.md
  - scripts/acceptance/package.json
  - scripts/acceptance/playwright.config.mjs
  - scripts/acceptance/seed-browser-admin.php
  - scripts/acceptance/tests/portability-critical.spec.mjs
  - scripts/acceptance/tests/responsive-critical.spec.mjs
validation:
  - command: continuation repository/live-state and overlap inspection
    result: PASS
    evidence: PR 94 retained as the only implementation PR for this task; no cross-repository writes were performed
  - command: lower-layer duplicate-coverage inspection
    result: PASS
    evidence: existing CMS feature tests, character real-MariaDB integration tests, Phase 7 production-like workflow and Platform DB outage workflow inspected before adding browser scenarios
  - command: node --check on prepared Playwright config and new portability/responsive specs
    result: PASS
    evidence: exact prepared JavaScript contents written to the task branch parsed successfully
  - command: python -m json.tool on prepared scripts/acceptance/package.json
    result: PASS
    evidence: exact prepared package manifest written to the task branch parsed successfully
  - command: YAML parse on prepared .github/workflows/acceptance-validation.yml
    result: PASS
    evidence: exact prepared workflow content written to the task branch parsed successfully; GitHub Actions execution provided semantic validation
  - command: php -l on prepared scripts/acceptance/seed-browser-admin.php
    result: PASS
    evidence: exact prepared browser-admin fixture content written to the task branch has no PHP syntax errors
  - command: Acceptance E2E and Visual UX critical run 29837186868 on a90a7526fa32adccacd19f83223a4271933e422e
    result: FAIL
    evidence: smoke passed; portability failed only on WebKit authorization-denial MFA setup with HTTP 429; root cause classified as shared identity rate-limit fixture coupling and corrected without weakening coverage
  - command: Acceptance E2E and Visual UX critical run 29838591467 on d6b800da4e212fce7986aabe80d8c461c65cf020
    result: PASS
    evidence: smoke PASS in 6 seconds; portability 12/12 PASS in 25 seconds with Chromium/Firefox/WebKit project-labelled JUnit and zero configured retries; responsive 9/9 PASS in 9 seconds across desktop/tablet/mobile with zero configured retries
  - command: final-slice required checks on 28ed7f2675ef170ec7f2559513eb7819f2a5756c
    result: PASS
    evidence: CI, Agent Governance, Platform DB Outage Validation, Phase 7 Production-Like Validation and Acceptance E2E and Visual UX all completed successfully; final acceptance repeated the bounded matrix with zero configured retries
  - command: checkpoint-head required checks on 6e5ba437280da55b1d99ebee65d020948b4890cd
    result: PASS
    evidence: CI, Agent Governance, Platform DB Outage Validation, Phase 7 Production-Like Validation and Acceptance E2E and Visual UX all completed successfully before main advanced
  - command: live main comparison after checkpoint-head validation
    result: BLOCKED
    evidence: current main efcedeb16649262ace0045520e44eeb2f3364cd1 is two commits ahead of the merge base and modifies public layout/home surfaces covered by the new acceptance subset; PR branch remains mergeable but is 2 commits behind and combined-state E2E is not yet proven
blockers:
  - synchronize PR 94 with current main efcedeb16649262ace0045520e44eeb2f3364cd1 and rerun exact-head required checks before any merge decision
next_action: Synchronize PR #94 with current main efcedeb16649262ace0045520e44eeb2f3364cd1 without losing the 12-file bounded slice, then rerun exact-head required checks and remeasure the critical portability/responsive profile before any merge decision.
```

## Notes

This task is a continuous verification hardening track, not a reopening of Phase 7 and not a substitute for issue #91. Prefer the smallest deterministic layer that proves each invariant. Browser E2E should prove composed user/system behavior, not reimplement every unit, feature or database race test in Playwright.
