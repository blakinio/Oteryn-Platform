---
task_id: OTERYN-20260721-platform-db-outage-validation
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
  - .github/workflows/phase7-production-like-validation.yml
search_first:
  - open PRs and active tasks overlapping Platform DB outage validation or phase7-production-like-validation.yml
  - existing production-like dependency-failure checks reusable for Platform DB outage proof
optional_reads:
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
---

# OTERYN-20260721-platform-db-outage-validation

## Goal

Close functional acceptance follow-up issue #71 by proving deterministic, fail-closed Platform database outage behavior against the running production-like Oteryn Platform release, without changing production behavior or overlapping the active visual/functional acceptance harness in PR #67.

## Acceptance criteria

- [ ] Start an exact-SHA production-like running release with the Platform DB endpoint intentionally unavailable while keeping the rest of the controlled environment intact.
- [ ] Exercise a representative Platform-owned state-changing browser flow over HTTP using a real CSRF token and session cookie.
- [ ] Prove the mutation does not report success and does not create Platform state while the Platform DB is unavailable.
- [ ] Prove the HTTP response does not expose stack traces, application key, submitted password, reset/token material, database password or connection secrets.
- [ ] Prove bounded request/server logs do not contain the submitted password, application key or database password.
- [ ] Prove the normal production-like release remains healthy after the isolated outage probe and recovery requires no data repair.
- [ ] Keep issue #68-#70 work path-disjoint from active PR #67.
- [ ] Update the functional acceptance matrix only from direct exact-SHA evidence; do not promote any production fact to `PRODUCTION_PROVEN`.
- [ ] Pass required CI, Agent Governance and production-like validation checks on the final PR head.

## Ownership

```yaml
owned_paths:
  - .github/workflows/phase7-production-like-validation.yml
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-platform-db-outage-validation.md
  - docs/agents/tasks/active/OTERYN-20260721-functional-acceptance-validation.md
  - docs/agents/tasks/archive/OTERYN-20260721-functional-acceptance-validation.md
  - docs/agents/tasks/archive/OTERYN-20260721-platform-db-outage-validation.md
modules:
  - PlatformOperations
  - testing
  - security
  - agent-governance
dependencies:
  - merged PR #66 / functional acceptance matrix
  - issue #71
  - merged PR #63 production-like validation harness
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T08:30:00+02:00
head: 3e04a69a724c4a23ae851fa809aae0dcbdff47d5
branch: task/OTERYN-20260721-platform-db-outage-validation
pr: none
status: implementing
context_routes:
  - testing
  - security
  - database
  - agent-governance
owned_paths:
  - .github/workflows/phase7-production-like-validation.yml
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-platform-db-outage-validation.md
  - docs/agents/tasks/active/OTERYN-20260721-functional-acceptance-validation.md
  - docs/agents/tasks/archive/OTERYN-20260721-functional-acceptance-validation.md
  - docs/agents/tasks/archive/OTERYN-20260721-platform-db-outage-validation.md
proven:
  - PR #66 merged the durable functional acceptance matrix and identified controlled Platform DB outage proof as gap FAV-04 / issue #71.
  - PR #67 is an active, separate acceptance task that owns .github/workflows/acceptance-validation.yml and scripts/acceptance/** and already overlaps issues #68 through #70; this task intentionally does not edit those paths.
  - The existing Phase 7 production-like workflow already starts an exact-SHA running release, validates safe representative errors/logging and uses a real MariaDB Platform DB, but it does not intentionally make the Platform DB unavailable for a live HTTP mutation.
  - The production-like web session driver defaults to file, so an outage-isolated server can render a CSRF-bearing registration form before the subsequent registration mutation reaches the unavailable Platform DB.
  - The completed functional acceptance inventory task from PR #66 has been copied unchanged to docs/agents/tasks/archive/ and removed from the active task set on this branch.
derived:
  - A second isolated `php artisan serve` process with an invalid Platform `DB_PORT` can exercise the requested outage without stopping the shared MariaDB service or contaminating the normal production-like release.
unknown:
  - exact HTTP status/body/log behavior produced by a CSRF-valid registration POST when the Platform DB endpoint is unavailable
conflicts: []
first_failure:
  marker: Platform DB outage acceptance evidence
  evidence: no current exact-SHA production-like workflow step intentionally exercises a Platform-owned HTTP mutation with the Platform DB unavailable
rejected_hypotheses:
  - PR #67 should be modified to add this proof: rejected because it is another active task with a distinct acceptance workflow/scripts ownership boundary.
  - Stopping the shared MariaDB service is necessary: rejected because an isolated server process can override only its Platform DB endpoint and avoid destabilizing unrelated dependency evidence.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260721-functional-acceptance-validation.md
  - docs/agents/tasks/active/OTERYN-20260721-functional-acceptance-validation.md
validation:
  - command: live GitHub preflight for merged PR #66, open PR #67, issue #71, ACTIVE_WORK and current Phase 7 production-like workflow
    result: PASS
    evidence: no overlapping ownership found for .github/workflows/phase7-production-like-validation.yml; PR #67 explicitly leaves Platform DB outage proof incomplete
blockers:
  - none
next_action: Add an isolated CSRF-valid registration outage probe to the Phase 7 production-like workflow, assert safe response/log behavior and confirm the normal release remains healthy afterward.
```

## Notes

This task changes validation/evidence only unless the outage probe reveals a real product defect. Any runtime behavior fix must be split into the smallest separately reviewed bounded task. No production endpoint, secret or real credential may be committed.
