# Oteryn Platform Active Work

Convenience index only. Individual active task records, live PRs and Git state are authoritative.

## Active tasks

- `OTERYN-20260721-e2e-coverage-hardening` — draft PR #94; risk-based continuous E2E hardening on top of the existing `STAGING_PROVEN` functional acceptance baseline. Current architecture slice defines bounded cross-browser/responsive/security/migration priorities while preserving lower-layer concurrency/integrity evidence and the separate production gate.

## Closed acceptance and release-preparation follow-ups

- PR #67 / `517968539bdfd7d189677b669bf0899c35fccec1` — issues #68-#70 closed with exact-SHA production-like browser acceptance evidence classified `STAGING_PROVEN`.
- PR #73 / `06d8d94aafd73de996eb4ea93705e8a45fbadafb` — issue #71 closed with controlled Platform DB outage evidence classified `STAGING_PROVEN` for that staging failure path only.
- PR #74 / `24eaa4ca5e38bb255db95a989c0ff02e954360f3` — issue #72 closed with focused CMS publication-state and privileged-audit regressions; no runtime defect found.
- PR #75 / `4fc6fcccea00bdd8d7679595b92d189cb572dd35` — final Functional Acceptance matrix reconciliation merged; FAV-01 through FAV-05 are closed for the delivered staging-verifiable scope.
- PR #77 / `1e6e21f0963406d4e58c39b347a49cfa4529bd1c` — delivered-surface UI/UX remediation merged with clean browser Visual/Accessibility evidence.
- PR #86 / `5d3628f8c6ba2e454246f24947ebe08ca93cf684` — issue #81 closed; authenticated Account Overview and provisioning-status UX delivered with full production-like browser evidence.
- PR #92 / `c18432df6b387932aa04e1eb269677c9078d9063` — fail-closed non-secret Production Go-Live verification evidence packet prepared; actual production execution remains issue #91.

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: COMPLETE**

The E2E coverage-hardening task is a continuous verification track and does not reopen either completed phase.

## Operational release state

- **Production Readiness: STAGING_PROVEN**
- **Functional Acceptance: STAGING_PROVEN for the currently delivered staging-verifiable functional surface**
- **Visual / UX Acceptance: PASS for the currently delivered staging-verifiable launch scope**
- **Production Go-Live Gate: PENDING PRODUCTION VERIFICATION**
- **Production Verification: REQUIRED BEFORE GO-LIVE**

No staging evidence may be promoted to `PRODUCTION_PROVEN`.

## Next work

Two independent tracks are allowed:

1. **Repository/staging continuous verification** — continue `OTERYN-20260721-e2e-coverage-hardening` / PR #94 using ADR 0008 and `docs/testing/E2E_COVERAGE_ROADMAP.md`. Start with the bounded P0 browser portability/responsive hardening slice and split follow-ups when the implementation becomes too broad.
2. **Production-only verification** — issue #91 remains the single production execution tracker. Resume it only when the exact final deployed production SHA, explicit production deployment/verification authorization and access to collect sanitized production evidence are available. Then execute `docs/operations/PRODUCTION_READINESS_CHECKLIST.md`, record results in `docs/operations/PRODUCTION_VERIFICATION_EVIDENCE.md`, and run `docs/testing/PRODUCTION_SMOKE_CHECKLIST.md` against that exact deployment.

The repository/staging hardening track must not claim production proof and does not block waiting for production access.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate and requires explicit authorization before external repository writes if it is part of launch scope.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
