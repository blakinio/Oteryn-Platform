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

- [ ] Publishing then unpublishing an existing news post hides it from public detail and list while persisting the unpublished state.
- [ ] Editing an existing managed page persists changed content.
- [ ] Unpublishing the edited managed page hides it from the public route.
- [ ] Representative privileged audit records do not contain plaintext passwords, password hashes, reset tokens or hashes, TOTP secrets, MFA recovery codes or hashes, or the application key.
- [ ] Existing CMS/RBAC/MFA authorization behavior remains unchanged.
- [ ] No runtime product behavior is changed unless a focused failing regression proves a defect; any unrelated defect is split into a separate bounded task.
- [ ] Remain path-disjoint from PR #67 acceptance workflow/scripts.
- [ ] Pass required exact-head CI, Agent Governance, Phase 7 Production-Like Validation and Platform DB Outage Validation.

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
updated_at: 2026-07-21T09:05:00+02:00
head: 665d1904f27e2e1b13657608c5230dc0cea43357
branch: task/OTERYN-20260721-cms-audit-regressions
pr: none
status: implementing
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
  - Existing AdminCmsManagementTest proves draft-to-published news, published managed-page creation, draft/future public hiding, unauthorized CMS denial and MFA enforcement, but lacks focused unpublish and existing-page edit assertions.
  - SaveNewsPost and SaveManagedPage both persist nullable published_at and audit only server-controlled slug/published metadata.
  - Existing AdminAuditTest proves bounded permission-protected visibility but lacks a dedicated assertion that privileged audit storage excludes security secrets.
  - PR #73 merged as 06d8d94aafd73de996eb4ea93705e8a45fbadafb and issue #71 is closed with STAGING_PROVEN evidence.
  - The completed Platform DB outage task record has been copied to archive and removed from the active set on this branch.
derived:
  - Focused feature tests should be sufficient to close FAV-05 if current implementation behaves as inspected; no application change is expected.
unknown:
  - whether focused unpublish/edit tests expose any latent CMS state-transition defect
  - whether constructing representative credential/reset/MFA sensitive state reveals unexpected audit leakage
conflicts: []
first_failure:
  marker: FAV-05 focused regression coverage
  evidence: functional acceptance matrix records news unpublish, managed-page edit/unpublish and audit-secret exclusion as DERIVED/UNKNOWN rather than directly tested
rejected_hypotheses:
  - PR #67 should own these repository tests: rejected because its active ownership is acceptance workflow/scripts and visual evidence, while these focused PHP feature tests are path-disjoint.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260721-platform-db-outage-validation.md
  - docs/agents/tasks/active/OTERYN-20260721-platform-db-outage-validation.md
validation:
  - command: live overlap search against open PRs and active acceptance work
    result: PASS
    evidence: no open PR found claiming the two focused PHP feature-test paths; PR #67 remains path-disjoint
blockers:
  - none
next_action: Add the three focused regression cases to the existing CMS/admin feature-test suites, then use exact-head CI to determine whether product behavior already satisfies them.
```

## Notes

Tests should assert security properties without printing or committing real secrets. Test-only synthetic values are permitted only when clearly non-production and must not appear in durable evidence output.
