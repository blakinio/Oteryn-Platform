---
task_id: OTERYN-20260727-support-legal-acceptance
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/adr/0015-machine-enforced-portal-acceptance-ledger.md
  - docs/testing/PORTAL_ACCEPTANCE_COVERAGE_MATRIX.md
search_first:
  - open pull requests and active tasks owning Support, Legal, localization or acceptance paths
  - typed editorial routes, support-link allowlists, legal-version persistence and exact permission tests
optional_reads:
  - docs/architecture/SECURITY_ARCHITECTURE.md
---

# OTERYN-20260727-support-legal-acceptance

## Goal

Close `support.public-legal-admin` through route-complete public and administrator browser evidence for all typed editorial routes, approved support channels, legal versioning, exact MFA/RBAC and Polish translation recovery.

## Acceptance criteria

- [ ] Every typed public Support/Legal route proves missing, unpublished, published, escaped plain text and EN/PL locale isolation.
- [ ] Support guidance proves only approved allowlisted channels and no stored ticket-submission form.
- [ ] Guest, no-MFA, no-permission and exact `support.content.manage` boundaries are browser-proven.
- [ ] Administrator draft/publish, legal validation, immutable published version, successor version and preserved history are browser-proven.
- [ ] Polish translation publish, stale fail-closed state and recovery are browser-proven.
- [ ] Chromium desktop/tablet/mobile plus bounded public Firefox/WebKit pass with zero retries.
- [ ] The canonical ledger changes to `covered` only after exact-head browser and repository checks pass.
- [ ] No production action or `PRODUCTION_PROVEN` claim is made.

## Ownership

```yaml
owned_paths:
  - .github/workflows/support-legal-acceptance.yml
  - scripts/acceptance/playwright.support-legal.config.mjs
  - scripts/acceptance/seed-browser-support-legal.php
  - scripts/acceptance/tests/support-legal-public-acceptance.spec.mjs
  - scripts/acceptance/tests/support-legal-admin-acceptance.spec.mjs
  - scripts/acceptance/coverage/portal-coverage-manifest.json
  - docs/testing/PORTAL_ACCEPTANCE_COVERAGE_MATRIX.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260727-support-legal-acceptance.md
  - docs/agents/tasks/active/OTERYN-20260727-announcements-acceptance.md
  - docs/agents/tasks/archive/OTERYN-20260727-announcements-acceptance.md
modules:
  - Support
  - Legal
  - Localization
  - Testing / Acceptance E2E
  - Agent governance
dependencies:
  - PR #259 must merge before the conflict-free successor is opened
  - Issue #240
blockers:
  - final Announcements merge
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-27T22:35:00+02:00
head: 4f6b66056df43de6f9c23e639d0455ef4ec5dcd5
branch: test/OTERYN-20260727-support-legal-acceptance
pr: none
status: implementing
context_routes:
  - agent-governance
  - testing
  - web-cms
  - admin-rbac
  - security
  - accessibility
owned_paths:
  - .github/workflows/support-legal-acceptance.yml
  - scripts/acceptance/playwright.support-legal.config.mjs
  - scripts/acceptance/seed-browser-support-legal.php
  - scripts/acceptance/tests/support-legal-public-acceptance.spec.mjs
  - scripts/acceptance/tests/support-legal-admin-acceptance.spec.mjs
  - scripts/acceptance/coverage/portal-coverage-manifest.json
  - docs/testing/PORTAL_ACCEPTANCE_COVERAGE_MATRIX.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260727-support-legal-acceptance.md
  - docs/agents/tasks/active/OTERYN-20260727-announcements-acceptance.md
  - docs/agents/tasks/archive/OTERYN-20260727-announcements-acceptance.md
proven:
  - Lower-layer feature tests cover every typed route, publication boundary, approved links, exact permission, legal immutability and audit redaction.
  - The prepared browser package includes every one of the eight typed public routes and the exact administrator role boundary.
derived:
  - The package can be reconciled onto the post-Announcements main without copying shared ledger or task state from the child branch.
unknown:
  - first exact-head Support and Legal browser result
conflicts: []
first_failure:
  marker: none
  evidence: the browser package has not yet run because its parent Announcements PR is still in final merge gates
rejected_hypotheses:
  - feature tests alone prove the composed public and administrator browser lifecycle
  - a public report form should be added to make Support complete
  - legal history may be replaced in place after publication
changed_paths:
  - .github/workflows/support-legal-acceptance.yml
  - scripts/acceptance/playwright.support-legal.config.mjs
  - scripts/acceptance/seed-browser-support-legal.php
  - scripts/acceptance/tests/support-legal-public-acceptance.spec.mjs
  - scripts/acceptance/tests/support-legal-admin-acceptance.spec.mjs
  - docs/agents/tasks/active/OTERYN-20260727-support-legal-acceptance.md
validation:
  - command: repository and feature-test inspection
    result: PASS
    evidence: typed routes, controllers, public presenters, approved support link policy, legal version action and translation action inspected
blockers:
  - final Announcements merge
next_action: Rebuild this package on post-Announcements main, open a draft PR and execute the exact-SHA Support and Legal workflow while the ledger remains planned.
```

## Notes

This package closes only the delivered Support and Legal contract. It does not create a ticket system, deploy to production or change the homepage-template selector.
