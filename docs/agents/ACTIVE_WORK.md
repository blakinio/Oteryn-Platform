# Oteryn Platform Active Work

Convenience index only. Individual active task records, live PRs and Git state are authoritative.

## Active tasks

- `OTERYN-20260721-functional-acceptance-closure`
  - branch: `task/OTERYN-20260721-functional-acceptance-closure`
  - status: blocked on PR #67 / issues #68-#70
  - goal: reconcile the Functional Acceptance Matrix once the independently owned live acceptance evidence is green and merged.

- `OTERYN-20260721-functional-visual-acceptance`
  - branch: `task/OTERYN-20260721-functional-visual-acceptance`
  - draft PR: #67
  - ownership: `.github/workflows/acceptance-validation.yml`, `scripts/acceptance/**`, `docs/acceptance/**`
  - current blocker: exact current head still has failing Acceptance E2E and Agent Governance checks; this task owns the remaining #68-#70 live acceptance evidence path.

## Closed functional-acceptance follow-ups

- PR #73 / `06d8d94aafd73de996eb4ea93705e8a45fbadafb` — issue #71 Platform DB outage path closed with controlled staging-only `STAGING_PROVEN` evidence.
- PR #74 / `24eaa4ca5e38bb255db95a989c0ff02e954360f3` — issue #72 CMS publication-state and privileged-audit regressions closed; no runtime defect found.

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: COMPLETE**

## Operational release state

- **Production Readiness: STAGING_PROVEN**
- **Functional Acceptance: PENDING final #68-#70 live evidence reconciliation**
- **Visual / UX Acceptance: FAIL on current delivered frontend; separate UI/UX follow-up required**
- **Production Go-Live Gate: PENDING PRODUCTION VERIFICATION**
- **Production Verification: REQUIRED BEFORE GO-LIVE**

No staging evidence may be promoted to `PRODUCTION_PROVEN`.

## Next work

PR #67 must complete or release its active acceptance-harness ownership with green exact-SHA evidence for #68-#70. Then resume `OTERYN-20260721-functional-acceptance-closure`, update the durable matrix once from merged evidence, and close satisfied issues.

Production-only smoke remains separate and must run only against the final deployed SHA.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate and requires explicit authorization before external repository writes if it is part of launch scope.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
