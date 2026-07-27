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
  - PR #259 merged as d08062c653a137e1359b5626fda635b170704cd8
  - Issue #240
blockers:
  - prepared package must be transplanted onto the fresh post-Announcements branch before opening a PR
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-27T20:40:18Z
head: 4dd52785da3361394485d2c719f95f37222ecd54
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
  - PR #259 merged to main as d08062c653a137e1359b5626fda635b170704cd8 after all thirteen exact-head workflows passed.
  - The prepared Support and Legal package exists on head 4dd52785da3361394485d2c719f95f37222ecd54 and contains one workflow, one Playwright matrix, one isolated seed and two browser specifications.
  - The prep branch is exactly at 4dd52785da3361394485d2c719f95f37222ecd54; live compare reported identical with zero commits ahead or behind that SHA.
  - The prep branch diverges from current main with merge base 05d08714a0b87ee8a453d01bded605ff42de8bbc, is one commit behind and contains forty-four ancestor commits, so it is not a safe PR head.
  - Fresh branch test/OTERYN-20260727-support-legal-acceptance-v2 exists at d08062c653a137e1359b5626fda635b170704cd8 and contains no transplanted Support and Legal files yet.
  - Lower-layer feature evidence covers all eight typed public routes, publication boundaries, approved support links, legal-version immutability, exact permission and audit redaction.
  - The affected trust boundary is public editorial reading plus privileged administrator mutation guarded by auth, confirmed MFA and exact support.content.manage permission.
  - No Canary or login-server schema, protocol or session compatibility changes are involved; no migration or production rollback is required.
  - Only synthetic acceptance configuration is used; no secret or production-only configuration is present in the prepared files.
derived:
  - Opening a PR from the prep branch would reintroduce already-merged Announcements history and unrelated changes.
  - The safe continuation is a narrow file transplant onto the existing v2 branch followed by a draft PR while the ledger remains planned.
unknown:
  - The first exact-head Support and Legal browser result on the post-Announcements base has not run.
  - Prepared browser assertions may require focused adjustment after their first deterministic workflow artifact.
conflicts: []
first_failure:
  marker: stale-prep-branch-after-announcements-merge
  evidence: compare d08062c653a137e1359b5626fda635b170704cd8...test/OTERYN-20260727-support-legal-acceptance returned diverged, ahead_by 44, behind_by 1 and merge base 05d08714a0b87ee8a453d01bded605ff42de8bbc.
rejected_hypotheses:
  - The prep branch can be opened directly as a clean successor PR: live compare proves it carries unrelated pre-merge ancestry.
  - Feature tests alone close the composed browser contract: the canonical ledger still records route-complete Playwright evidence as the remaining gap.
  - A public ticket-submission form is required for completion: the delivered contract intentionally routes users only to approved allowlisted channels and stores no ticket payload.
changed_paths:
  - .github/workflows/support-legal-acceptance.yml
  - scripts/acceptance/playwright.support-legal.config.mjs
  - scripts/acceptance/seed-browser-support-legal.php
  - scripts/acceptance/tests/support-legal-public-acceptance.spec.mjs
  - scripts/acceptance/tests/support-legal-admin-acceptance.spec.mjs
  - docs/agents/tasks/active/OTERYN-20260727-support-legal-acceptance.md
validation:
  - command: compare 4dd52785da3361394485d2c719f95f37222ecd54...test/OTERYN-20260727-support-legal-acceptance
    result: PASS
    evidence: GitHub reported identical, ahead_by 0 and behind_by 0.
  - command: compare d08062c653a137e1359b5626fda635b170704cd8...test/OTERYN-20260727-support-legal-acceptance
    result: FAIL
    evidence: GitHub reported diverged, ahead_by 44, behind_by 1 and merge base 05d08714a0b87ee8a453d01bded605ff42de8bbc.
  - command: Support and Legal source and lower-layer test inspection
    result: PASS
    evidence: typed routes, public presenters, allowlisted links, legal version action, translation action, exact middleware and feature tests were inspected.
  - command: Support and Legal Acceptance workflow
    result: NOT_RUN
    evidence: the package has not yet been transplanted to the clean post-Announcements branch and has no PR.
blockers:
  - prepared files are on the stale prep branch rather than the clean v2 branch
next_action: Copy only the five Support and Legal acceptance files plus this active task onto test/OTERYN-20260727-support-legal-acceptance-v2, archive the merged Announcements task, update ACTIVE_WORK narrowly and open a draft PR while leaving the ledger planned.
```

## Notes

This package closes only the delivered Support and Legal contract. It does not create a ticket system, deploy to production or change the homepage-template selector.
