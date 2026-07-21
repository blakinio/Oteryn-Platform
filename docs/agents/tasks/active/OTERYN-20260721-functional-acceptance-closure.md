---
task_id: OTERYN-20260721-functional-acceptance-closure
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - docs/testing/PRODUCTION_SMOKE_CHECKLIST.md
search_first:
  - PR #67 and issues #68 #69 #70 for final live-acceptance evidence
  - open PRs and active tasks overlapping functional acceptance matrix ownership
optional_reads:
  - docs/acceptance/VISUAL_UX_ACCEPTANCE_MATRIX.md
  - docs/agents/ACTIVE_WORK.md
---

# OTERYN-20260721-functional-acceptance-closure

## Goal

Reconcile the Functional Acceptance Matrix once all bounded follow-up evidence is merged, classify the current delivered functional surface honestly, close satisfied follow-up issues, and leave only final-production smoke checks or concrete unresolved defects.

## Acceptance criteria

- [x] Revalidate merged evidence for issue #71 Platform DB outage behavior.
- [x] Revalidate merged evidence for issue #72 CMS publication-state and audit-data regressions.
- [ ] Consume merged exact-SHA live acceptance evidence for issue #68 Identity/MFA/session/CSRF flows.
- [ ] Consume merged exact-SHA live acceptance evidence for issue #69 provisioning/binding/character/public flows.
- [ ] Consume merged exact-SHA live acceptance evidence for issue #70 admin/RBAC/CMS/audit flows.
- [ ] Update `docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md` once from merged evidence only.
- [ ] Mark overall Functional Acceptance `STAGING_PROVEN` only if every staging-verifiable critical flow and required failure/authorization path is directly proven.
- [ ] Preserve all production smoke facts as non-passing until final production execution.
- [ ] Pass required exact-head repository checks before merge.

## Ownership

```yaml
owned_paths:
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - docs/testing/PRODUCTION_SMOKE_CHECKLIST.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-functional-acceptance-closure.md
  - docs/agents/tasks/archive/OTERYN-20260721-functional-acceptance-closure.md
modules:
  - testing
  - agent-governance
dependencies:
  - merged PR #66 functional acceptance inventory
  - merged PR #73 issue #71 evidence
  - merged PR #74 issue #72 regressions
  - PR #67 / issues #68-#70
blockers:
  - PR #67 is still open and its exact current head has failing Acceptance E2E and Agent Governance checks; its active task owns the acceptance workflow/scripts paths.
cross_repository_tasks:
  - authoritative Platform game-login bridge remains separately authorized if required for launch scope
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T09:35:00+02:00
head: 585f2cd2ba2a9bbcaad4829ea75b5d5732644661
branch: task/OTERYN-20260721-functional-acceptance-closure
pr: none
status: blocked
context_routes:
  - testing
  - security
  - auth-identity
  - accounts-characters
  - admin-rbac
  - web-cms
  - agent-governance
owned_paths:
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - docs/testing/PRODUCTION_SMOKE_CHECKLIST.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-functional-acceptance-closure.md
  - docs/agents/tasks/archive/OTERYN-20260721-functional-acceptance-closure.md
proven:
  - PR #73 merged as 06d8d94aafd73de996eb4ea93705e8a45fbadafb and issue #71 is closed with controlled production-like Platform DB outage evidence classified STAGING_PROVEN for that staging failure path only.
  - PR #74 merged as 24eaa4ca5e38bb255db95a989c0ff02e954360f3 and issue #72 is closed; focused news/page publication-state and privileged-audit regressions passed without runtime product changes.
  - PR #67 remains open and owns .github/workflows/acceptance-validation.yml plus scripts/acceptance/** for the live acceptance path covering issues #68-#70.
  - PR #67 current head 0055e40ed2d70d157476026428daa57570cc8176 has CI and Phase 7 validation success but Acceptance E2E and Agent Governance failures.
  - PR #67 task record classifies the remaining integrated browser failures as three assertion-quality harness failures rather than proven product defects.
  - This closure task does not claim or edit PR #67-owned acceptance workflow/scripts paths.
derived:
  - Issues #71 and #72 no longer block aggregate functional acceptance.
  - Aggregate Functional Acceptance cannot honestly become STAGING_PROVEN until PR #67 issues #68-#70 evidence is green and merged or equivalent direct evidence is provided through a non-overlapping successor after ownership is released.
unknown:
  - final exact-SHA merged evidence for issues #68 #69 #70
conflicts: []
first_failure:
  marker: live acceptance evidence ownership
  evidence: PR #67 current head has failing Acceptance E2E and Agent Governance checks and remains an active task owning the required live acceptance harness paths
rejected_hypotheses:
  - Issue #71 remains a blocker: rejected by merged PR #73 and closed issue #71 evidence.
  - Issue #72 remains a blocker: rejected by merged PR #74 and closed issue #72 regressions.
  - This closure task should modify PR #67-owned scripts to force completion: rejected by active-task path ownership and concurrency governance.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260721-cms-audit-regressions.md
  - docs/agents/tasks/active/OTERYN-20260721-cms-audit-regressions.md
validation:
  - command: live GitHub revalidation of PR #67 current head and issues #71/#72
    result: BLOCKED
    evidence: #71 and #72 are closed; PR #67 remains open with failing Acceptance E2E and Agent Governance checks
blockers:
  - PR #67 must release or complete ownership with green exact-SHA live acceptance evidence for #68-#70 before aggregate matrix closure.
next_action: Revalidate PR #67 immediately after its owner resolves the current harness/governance failures; if it merges with green evidence, update the Functional Acceptance Matrix and close #68-#70, otherwise record the exact remaining defect without overclaiming STAGING_PROVEN.
```

## Notes

Production smoke remains separate. No staging evidence in this task may be promoted to `PRODUCTION_PROVEN`.
