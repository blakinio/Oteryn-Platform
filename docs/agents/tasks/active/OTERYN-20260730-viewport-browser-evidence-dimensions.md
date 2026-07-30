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
- [x] Canonical viewport and browser/profile identifiers are validated against an allowlist.
- [x] Every declared viewport mapping requires an exact evidence file, stable marker and executable Playwright project or npm profile.
- [ ] Critical rendered surfaces prove Chromium desktop, tablet and mobile in blocking zero-retry profiles.
- [x] Firefox/WebKit coverage or bounded risk-based exclusion rationale is explicit.
- [x] Missing files, markers, projects, npm profiles and orphan mappings fail closed.
- [x] Deterministic negative fixtures cover missing mobile evidence, unknown profile, missing portability rationale and orphan mapping.
- [ ] Strict Portal Acceptance executes the new validator on the exact PR SHA.
- [x] Parent #326 remains open for the still-unproven full state/data/error/media Cartesian matrix.
- [x] No staging or production proof is claimed.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260730-viewport-browser-evidence-dimensions.md
  - docs/agents/PROJECT_STATE.md
  - docs/testing/PRODUCT_COMPLETENESS_FRONTEND_AUDIT_2026-07-30.md
  - scripts/acceptance/coverage/portal-dimension-evidence.json
  - scripts/acceptance/coverage/validate-dimension-evidence.mjs
  - scripts/acceptance/coverage/test-dimension-evidence.mjs
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
updated_at: 2026-07-30T09:24:00Z
head: 3833a500ec03361086d3fd41596dad8690d7bd30
branch: test/OTERYN-20260730-viewport-browser-evidence-dimensions
pr: 349
status: implementing
context_routes:
  - agent-governance
  - testing
  - architecture
  - web-cms
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260730-viewport-browser-evidence-dimensions.md
  - docs/agents/PROJECT_STATE.md
  - docs/testing/PRODUCT_COMPLETENESS_FRONTEND_AUDIT_2026-07-30.md
  - scripts/acceptance/coverage/portal-dimension-evidence.json
  - scripts/acceptance/coverage/validate-dimension-evidence.mjs
  - scripts/acceptance/coverage/test-dimension-evidence.mjs
  - scripts/acceptance/package.json
  - .github/workflows/portal-acceptance-contract.yml
proven:
  - Current portal coverage validation accepts arbitrary non-empty viewport and browser strings and only requires aggregate stable evidence markers.
  - Playwright defines canonical Chromium desktop/tablet/mobile, bounded Firefox/WebKit portability and specialized zero-retry module profiles.
  - Events, Announcements, Community Data, Support/Moderation, Support/Legal, Wiki and Editorial Media have executable specialized viewport projects; some also have bounded Firefox/WebKit projects.
  - Marketplace acceptance proves three responsive viewport sizes inside Chromium but its current bounded-portability declaration is not tied to an executable Firefox or WebKit project.
  - PR #349 now contains a fail-closed dimension validator and nine deterministic fixture cases for missing mappings, unknown identifiers, missing rationale, missing evidence and orphan references.
derived:
  - A separate dimension ledger can close the evidence-contract gap without changing product runtime or multiplying secret-sensitive flows across all browsers.
  - Surface migration must distinguish direct module projects, standard representative portability and explicit risk-based portability exclusions.
unknown:
  - Exact per-surface mappings for all covered surfaces still need to be written into portal-dimension-evidence.json and reconciled against current executable profiles.
conflicts: []
first_failure:
  marker: none
  evidence: exact-SHA workflows for 3833a500ec03361086d3fd41596dad8690d7bd30 are queued or running; validator fixtures are not yet wired into strict Portal Acceptance.
rejected_hypotheses:
  - Treat a non-empty browsers or viewports array as proof that each dimension executed.
  - Expand every secret-sensitive lifecycle across every browser merely to satisfy a matrix.
  - Preserve Marketplace bounded-portability without an executable Firefox/WebKit mapping.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260730-viewport-browser-evidence-dimensions.md
  - scripts/acceptance/coverage/validate-dimension-evidence.mjs
  - scripts/acceptance/coverage/test-dimension-evidence.mjs
  - scripts/acceptance/package.json
validation:
  - command: GitHub exact-SHA workflows for 3833a500ec03361086d3fd41596dad8690d7bd30
    result: RUNNING
    evidence: Portal Acceptance Contract run 30530293148 plus repository workflows started on the exact head
blockers:
  - none
next_action: Add portal-dimension-evidence.json mappings for every covered surface, run the fixture profile, then wire both validator and fixtures into strict Portal Acceptance.
```
