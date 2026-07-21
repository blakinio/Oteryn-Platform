---
task_id: OTERYN-20260721-cms-audit-regressions
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - tests/Feature/Cms/AdminCmsManagementTest.php
  - tests/Feature/Admin/AdminAuditTest.php
search_first:
  - open PRs and active tasks overlapping CMS state-transition tests or admin audit regressions
  - existing CMS/audit test helpers and sensitive-data handling
optional_reads:
  - app/Cms/Actions/SaveNewsPost.php
  - app/Cms/Actions/SaveManagedPage.php
  - app/Audit/AdminAuditRecorder.php
  - app/Admin/AdminRoleManager.php
---

# OTERYN-20260721-cms-audit-regressions

## Goal

Close functional acceptance follow-up issue #72 with focused repository regressions for news unpublish, managed-page edit/unpublish and privileged audit sensitive-data exclusion, without changing product behavior unless a focused regression proves a real defect.

## Acceptance criteria

- [x] Publishing then unpublishing an existing news post hides it from public detail and list while persisting the unpublished state.
- [x] Editing an existing managed page persists changed content.
- [x] Unpublishing the edited managed page hides it from the public route.
- [x] Representative privileged audit records do not contain plaintext passwords, password hashes, reset tokens or hashes, TOTP secrets, MFA recovery codes or hashes, encrypted MFA state, or the application key.
- [x] Existing CMS/RBAC/MFA authorization behavior remains unchanged.
- [x] No runtime product behavior was changed; focused regressions passed against the existing implementation.
- [x] Remain path-disjoint from PR #67 acceptance workflow/scripts.
- [x] Pass required exact-head CI, Agent Governance, Phase 7 Production-Like Validation and Platform DB Outage Validation on the validated implementation head.

## Ownership

```yaml
owned_paths:
  - tests/Feature/Cms/AdminCmsManagementTest.php
  - tests/Feature/Admin/AdminAuditTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-cms-audit-regressions.md
  - docs/agents/tasks/active/OTERYN-20260721-platform-db-outage-validation.md
  - docs/agents/tasks/archive/OTERYN-20260721-platform-db-outage-validation.md
  - docs/agents/tasks/archive/OTERYN-20260721-cms-audit-regressions.md
modules:
  - CMS
  - Admin
  - testing
  - security
  - agent-governance
dependencies:
  - merged PR #66 functional acceptance matrix
  - merged PR #73 Platform DB outage evidence
  - issue #72
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T09:20:00+02:00
head: 06d87d36d60db58a9377528960de19314a2c003f
branch: task/OTERYN-20260721-cms-audit-regressions
pr: 74
status: ready
context_routes:
  - testing
  - web-cms
  - admin-rbac
  - security
  - agent-governance
owned_paths:
  - tests/Feature/Cms/AdminCmsManagementTest.php
  - tests/Feature/Admin/AdminAuditTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-cms-audit-regressions.md
  - docs/agents/tasks/active/OTERYN-20260721-platform-db-outage-validation.md
  - docs/agents/tasks/archive/OTERYN-20260721-platform-db-outage-validation.md
  - docs/agents/tasks/archive/OTERYN-20260721-cms-audit-regressions.md
proven:
  - Issue #72 is the remaining non-overlapping repository-test gap from PR #66 while PR #67 separately owns live acceptance evidence for #68-#70.
  - Added a focused news state-transition regression proving an existing published post becomes unpublished through the authorized admin update route, persists published_at=null, disappears from public detail and list, and writes bounded published=false audit metadata.
  - Added a focused managed-page regression proving an existing published page can be edited, persists changed title/body, becomes unpublished through the authorized admin update route, disappears from the public route, and writes bounded published=false audit metadata.
  - Added a privileged audit regression that establishes synthetic password, password hash, plain/stored reset token, TOTP secret, encrypted TOTP state, recovery code, recovery-code hash, encrypted recovery-code state and application key material, performs representative role-management and CMS privileged mutations, then proves none of those values occur in persisted admin audit records.
  - No runtime application file was changed; the existing CMS and audit implementation satisfied all new regressions.
  - Validated implementation SHA 06d87d36d60db58a9377528960de19314a2c003f passed CI run 29807822818 / #836, Agent Governance run 29807822835 / #756, Phase 7 Production-Like Validation run 29807822809 / #81 and Platform DB Outage Validation run 29807822803 / #11.
  - PR #74 remains path-disjoint from PR #67 acceptance workflow/scripts.
  - PR #73 merged as 06d8d94aafd73de996eb4ea93705e8a45fbadafb and issue #71 is closed with STAGING_PROVEN evidence.
  - The completed Platform DB outage task record has been copied to archive and removed from the active set on this branch.
derived:
  - FAV-05 is a coverage gap only, not a product defect; issue #72 can close once PR #74 merges.
  - Aggregate Functional Acceptance should still wait for the independently owned #68-#70 live acceptance evidence from PR #67 before the matrix is reconciled once against merged main.
unknown:
  - final merged evidence state of PR #67 / issues #68-#70
conflicts: []
first_failure:
  marker: none
  evidence: all focused regressions and required validation workflows passed on implementation SHA 06d87d36d60db58a9377528960de19314a2c003f
rejected_hypotheses:
  - PR #67 should own these repository tests: rejected because its active ownership is acceptance workflow/scripts and visual evidence, while these focused PHP feature tests are path-disjoint.
  - Runtime CMS or audit code needs a fix: rejected because all new focused regressions passed without application changes.
changed_paths:
  - tests/Feature/Cms/AdminCmsManagementTest.php
  - tests/Feature/Admin/AdminAuditTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/archive/OTERYN-20260721-platform-db-outage-validation.md
  - docs/agents/tasks/active/OTERYN-20260721-platform-db-outage-validation.md
  - docs/agents/tasks/active/OTERYN-20260721-cms-audit-regressions.md
validation:
  - command: live overlap search against open PRs and active acceptance work
    result: PASS
    evidence: no open PR claims the two focused PHP feature-test paths; PR #67 remains path-disjoint
  - command: CI run 29807822818 / #836 on 06d87d36d60db58a9377528960de19314a2c003f
    result: PASS
    evidence: formatting, level-10 static analysis and full test suite including all new regressions passed
  - command: Agent Governance run 29807822835 / #756 on 06d87d36d60db58a9377528960de19314a2c003f
    result: PASS
    evidence: task/checkpoint governance passed
  - command: Phase 7 Production-Like Validation run 29807822809 / #81 on 06d87d36d60db58a9377528960de19314a2c003f
    result: PASS
    evidence: established production-like validation remained green
  - command: Platform DB Outage Validation run 29807822803 / #11 on 06d87d36d60db58a9377528960de19314a2c003f
    result: PASS
    evidence: controlled outage evidence remained green
blockers:
  - none
next_action: Merge PR #74 after final current-head checks remain green, close issue #72, then revalidate PR #67 and issues #68-#70 before creating one final functional-acceptance reconciliation task.
```

## Notes

Tests assert security properties using synthetic test-only values and do not print those values into durable evidence. No production secret or endpoint is committed.
