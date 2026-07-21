# Oteryn Platform Active Work

Convenience index only. Individual active task records, live PRs and Git state are authoritative.

## Active tasks

- `OTERYN-20260721-account-overview-provisioning-status`
  - issue: #81
  - PR: #86
  - status: validating for merge
  - result: Account Overview and provisioning-status candidate PASS; full browser and Visual/Accessibility evidence is green.

- `OTERYN-20260721-functional-visual-acceptance`
  - PR #67 merged as `517968539bdfd7d189677b669bf0899c35fccec1`
  - status: completed; active task record is pending archive by its owning task flow
  - result: browser Functional Acceptance evidence delivered; baseline Visual / UX Acceptance findings were remediated by merged PR #77 and the remaining missing Account Overview surface is delivered by PR #86 candidate.

## Closed functional-acceptance follow-ups

- PR #67 / `517968539bdfd7d189677b669bf0899c35fccec1` — issues #68-#70 closed with exact-SHA production-like browser acceptance evidence classified `STAGING_PROVEN`.
- PR #73 / `06d8d94aafd73de996eb4ea93705e8a45fbadafb` — issue #71 closed with controlled Platform DB outage evidence classified `STAGING_PROVEN` for that staging failure path only.
- PR #74 / `24eaa4ca5e38bb255db95a989c0ff02e954360f3` — issue #72 closed with focused CMS publication-state and privileged-audit regressions; no runtime defect found.
- PR #75 / `4fc6fcccea00bdd8d7679595b92d189cb572dd35` — final Functional Acceptance matrix reconciliation merged; FAV-01 through FAV-05 are closed for the delivered staging-verifiable scope.

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: COMPLETE**

## Operational release state

- **Production Readiness: STAGING_PROVEN**
- **Functional Acceptance: STAGING_PROVEN for the currently delivered staging-verifiable functional surface**
- **Visual / UX Acceptance: PASS for the PR #86 candidate based on composed PR #77 remediation plus full issue #81 production-like browser evidence**
- **Production Go-Live Gate: PENDING PRODUCTION VERIFICATION**
- **Production Verification: REQUIRED BEFORE GO-LIVE**

No staging evidence may be promoted to `PRODUCTION_PROVEN`.

## Next work

Merge PR #86 after its current-head checks pass, close issue #81 from merged evidence, archive the completed account-overview task, then keep production-only smoke separate until the exact final deployed SHA and production authorization are available.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate and requires explicit authorization before external repository writes if it is part of launch scope.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
