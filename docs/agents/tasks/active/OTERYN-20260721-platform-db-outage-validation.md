---
task_id: OTERYN-20260721-platform-db-outage-validation
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
  - .github/workflows/platform-db-outage-validation.yml
search_first:
  - open PRs and active tasks overlapping Platform DB outage validation or platform-db-outage-validation.yml
  - existing production-like dependency-failure checks reusable for Platform DB outage proof
optional_reads:
  - .github/workflows/phase7-production-like-validation.yml
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
---

# OTERYN-20260721-platform-db-outage-validation

## Goal

Close functional acceptance follow-up issue #71 by proving deterministic, fail-closed Platform database outage behavior against the running production-like Oteryn Platform release, without changing production behavior or overlapping the active visual/functional acceptance harness in PR #67.

## Acceptance criteria

- [x] Start an exact-SHA production-like running release with the Platform DB endpoint intentionally unavailable while keeping the rest of the controlled environment intact.
- [x] Exercise a representative Platform-owned state-changing browser flow over HTTP using a real CSRF token and session cookie.
- [x] Prove the mutation does not report success and does not create Platform state while the Platform DB is unavailable.
- [x] Prove the HTTP response does not expose stack traces, application key, submitted password, reset/token material, database password or connection secrets.
- [x] Prove bounded request/server logs do not contain the submitted password, application key or database password.
- [x] Prove the normal production-like release remains healthy after the isolated outage probe and recovery requires no data repair.
- [x] Keep issue #68-#70 work path-disjoint from active PR #67.
- [x] Record exact matrix-ready evidence for final functional-acceptance closure without promoting any production fact to `PRODUCTION_PROVEN`; defer the aggregate matrix status edit until PR #67 resolves the concurrently owned #68-#70 live-E2E evidence.
- [x] Pass required CI, Agent Governance, Phase 7 production-like validation and dedicated outage-validation checks on the validated implementation head.

## Ownership

```yaml
owned_paths:
  - .github/workflows/platform-db-outage-validation.yml
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
updated_at: 2026-07-21T08:55:00+02:00
head: 1baae017dbfcbc47f2092655b11645f02903cf07
branch: task/OTERYN-20260721-platform-db-outage-validation
pr: 73
status: ready
context_routes:
  - testing
  - security
  - database
  - agent-governance
owned_paths:
  - .github/workflows/platform-db-outage-validation.yml
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260721-platform-db-outage-validation.md
  - docs/agents/tasks/active/OTERYN-20260721-functional-acceptance-validation.md
  - docs/agents/tasks/archive/OTERYN-20260721-functional-acceptance-validation.md
  - docs/agents/tasks/archive/OTERYN-20260721-platform-db-outage-validation.md
proven:
  - PR #66 merged the durable functional acceptance matrix and identified controlled Platform DB outage proof as gap FAV-04 / issue #71.
  - PR #67 is an active, separate acceptance task that owns .github/workflows/acceptance-validation.yml and scripts/acceptance/** and already overlaps issues #68 through #70; PR #73 is path-disjoint from those acceptance implementation paths.
  - The production-like web session driver defaults to file, allowing the outage-isolated production-mode server to render a CSRF-bearing registration form before the registration mutation reaches Platform DB persistence.
  - Dedicated Platform DB Outage Validation run 29807221248 / #2 succeeded on exact SHA 1baae017dbfcbc47f2092655b11645f02903cf07.
  - The successful outage run required a CSRF-valid registration mutation to return 5xx rather than success or 419 while DB_PORT was redirected to an unavailable endpoint.
  - The successful outage run verified Platform Identity row count remained unchanged across the failed mutation.
  - The successful outage run verified the HTTP response excluded stack trace, SQLSTATE/PDO transport diagnostics, APP_KEY, DB password and submitted password.
  - The successful outage run verified the server log excluded APP_KEY, DB password and submitted password.
  - The successful outage run stopped the isolated outage process, started a normal DB-backed production-mode server and successfully served /news and /health with migration status intact.
  - Non-secret artifact platform-db-outage-evidence-29807221248 has artifact id 8485962469 and digest sha256:e4f91980b116ac1928665941a334d42103b7053045dfde43a10d15c275e64a64.
  - The same exact implementation head passed CI #824, Agent Governance #744 and Phase 7 Production-Like Validation #70.
  - Issue #71 contains the exact non-secret evidence summary and remains classified STAGING_PROVEN only.
  - The completed functional acceptance inventory task from PR #66 has been copied unchanged to docs/agents/tasks/archive/ and removed from the active task set on this branch.
derived:
  - Issue #71 is closed from an engineering-evidence perspective once PR #73 merges; final production Platform DB outage behavior remains UNKNOWN until directly observed in production and is not required to be destructively injected there.
  - Aggregate matrix edits should be performed in the final functional-acceptance closure after PR #67 lands, so #68-#70 evidence and #71 evidence are reconciled once rather than through conflicting concurrent status edits.
unknown:
  - final production Platform DB outage behavior and operator observability, which remain production-only facts
conflicts: []
first_failure:
  marker: none
  evidence: dedicated outage validation, CI, governance and Phase 7 validation all passed on exact implementation SHA 1baae017dbfcbc47f2092655b11645f02903cf07
rejected_hypotheses:
  - PR #67 should be modified to add this proof: rejected because it is another active task with a distinct acceptance workflow/scripts ownership boundary.
  - Stopping the shared MariaDB service is necessary: rejected because an isolated server process can override only its Platform DB endpoint and avoid destabilizing unrelated dependency evidence.
  - The broad Phase 7 workflow must be edited: rejected because a dedicated exact-SHA outage workflow produced narrower evidence with lower regression risk.
changed_paths:
  - .github/workflows/platform-db-outage-validation.yml
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/archive/OTERYN-20260721-functional-acceptance-validation.md
  - docs/agents/tasks/active/OTERYN-20260721-functional-acceptance-validation.md
  - docs/agents/tasks/active/OTERYN-20260721-platform-db-outage-validation.md
validation:
  - command: live GitHub preflight for merged PR #66, open PR #67, issue #71, ACTIVE_WORK and current production-like validation ownership
    result: PASS
    evidence: PR #73 is path-disjoint from PR #67 acceptance workflow/scripts and issue #71 remained explicitly unproven before this task
  - command: Platform DB Outage Validation run 29807221248 / #2 on 1baae017dbfcbc47f2092655b11645f02903cf07
    result: PASS
    evidence: CSRF-valid live HTTP mutation failed closed with unchanged Platform state, bounded non-sensitive response/log behavior and successful DB-backed recovery read
  - command: CI run 29807221246 / #824 on 1baae017dbfcbc47f2092655b11645f02903cf07
    result: PASS
    evidence: required repository CI completed successfully
  - command: Agent Governance run 29807221276 / #744 on 1baae017dbfcbc47f2092655b11645f02903cf07
    result: PASS
    evidence: task/checkpoint governance completed successfully
  - command: Phase 7 Production-Like Validation run 29807221399 / #70 on 1baae017dbfcbc47f2092655b11645f02903cf07
    result: PASS
    evidence: established production-like validation remained green with the new isolated outage workflow present
blockers:
  - none
next_action: Merge PR #73 after current-head checks remain green, close issue #71, then execute the non-overlapping FAV-05 repository regression task while PR #67 continues to own #68-#70 live acceptance evidence.
```

## Notes

This task changes validation/evidence only and found no runtime product defect. No production endpoint, secret or real credential is committed. Aggregate Functional Acceptance remains pending until the independently owned #68-#70 evidence and FAV-05 regressions are reconciled in a final closure.
