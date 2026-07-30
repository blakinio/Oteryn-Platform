---
task_id: OTERYN-20260730-viewport-browser-evidence-dimensions
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/TEST_STRATEGY.md
  - scripts/acceptance/coverage/portal-coverage-manifest.json
  - scripts/acceptance/coverage/validate-portal-coverage.mjs
  - scripts/acceptance/playwright.config.mjs
  - scripts/acceptance/package.json
search_first:
  - Issue #326, Issue #347 and open PRs touching scripts/acceptance/coverage, Playwright profiles or portal acceptance workflows
  - existing viewport/browser declarations and stable Playwright evidence markers
  - reusable coverage validators and negative-fixture harnesses
optional_reads:
  - docs/testing/PRODUCT_COMPLETENESS_FRONTEND_AUDIT_2026-07-30.md
  - docs/testing/PORTAL_ACCEPTANCE_COVERAGE_MATRIX.md
---

# OTERYN-20260730-viewport-browser-evidence-dimensions

## Goal

Deliver Issue #347 as the next bounded slice of parent #326: fail-closed machine linkage from every declared viewport and browser/profile requirement to exact executable Playwright evidence, while preserving risk-based browser scope and production nonclaims.

## Acceptance criteria

- [ ] Every covered rendered surface has an explicit dimension policy or a bounded supporting-endpoint exclusion.
- [ ] Canonical viewport and browser/profile identifiers are validated against an allowlist.
- [ ] Every declared viewport maps to an exact evidence file, stable marker and executable Playwright project or npm profile.
- [ ] Critical rendered surfaces prove Chromium desktop, tablet and mobile in blocking zero-retry profiles.
- [ ] Firefox/WebKit coverage or bounded risk-based exclusion rationale is explicit.
- [ ] Missing files, markers, projects, npm profiles and orphan mappings fail closed.
- [ ] Deterministic negative fixtures cover missing mobile evidence, unknown profile, missing portability rationale and orphan mapping.
- [ ] Strict Portal Acceptance executes the new validator on the exact PR SHA.
- [ ] Parent #326 remains open for the still-unproven full state/data/error/media Cartesian matrix.
- [ ] No staging or production proof is claimed.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260730-viewport-browser-evidence-dimensions.md
  - docs/agents/PROJECT_STATE.md
  - docs/testing/PRODUCT_COMPLETENESS_FRONTEND_AUDIT_2026-07-30.md
  - scripts/acceptance/coverage/portal-evidence-dimensions.json
  - scripts/acceptance/coverage/validate-portal-evidence-dimensions.mjs
  - scripts/acceptance/coverage/test-portal-evidence-dimensions.mjs
  - scripts/acceptance/package.json
  - .github/workflows/portal-acceptance-contract.yml
modules:
  - Testing
  - AgentGovernance
  - ProductArchitecture
dependencies:
  - Issue #326
  - completed Issue #340 / PR #341
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-30T09:05:00Z
head: 8e613c00503c0874e69e2085c740f87f4a87e002
branch: test/OTERYN-20260730-viewport-browser-evidence-dimensions
pr: none
status: investigating
context_routes:
  - agent-governance
  - testing
  - architecture
  - web-cms
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260730-viewport-browser-evidence-dimensions.md
  - docs/agents/PROJECT_STATE.md
  - docs/testing/PRODUCT_COMPLETENESS_FRONTEND_AUDIT_2026-07-30.md
  - scripts/acceptance/coverage/portal-evidence-dimensions.json
  - scripts/acceptance/coverage/validate-portal-evidence-dimensions.mjs
  - scripts/acceptance/coverage/test-portal-evidence-dimensions.mjs
  - scripts/acceptance/package.json
  - .github/workflows/portal-acceptance-contract.yml
proven:
  - Current portal coverage validation accepts arbitrary non-empty viewport and browser strings and only requires aggregate stable evidence markers.
  - Playwright defines canonical Chromium desktop/tablet/mobile, bounded Firefox/WebKit portability and specialized profiles.
  - No open PR currently owns scripts/acceptance/coverage or the planned dimension-ledger paths.
derived:
  - A separate dimension ledger and validator can close this evidence-contract gap without changing product runtime or multiplying secret-sensitive flows across all browsers.
unknown:
  - Exact per-surface dimension mappings and justified portability exclusions still need to be reconciled against current evidence files and executable profiles.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Treat a non-empty browsers or viewports array as proof that each dimension executed.
  - Expand every secret-sensitive lifecycle across every browser merely to satisfy a matrix.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260730-viewport-browser-evidence-dimensions.md
validation:
  - command: not-run
    result: NOT_RUN
    evidence: task record only; implementation not started
blockers:
  - none
next_action: Inventory every current portal surface, its declared dimensions, stable evidence markers and executable Playwright/npm profiles before designing the fail-closed ledger.
```
