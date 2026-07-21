# Oteryn Platform Active Work

Convenience index only. Individual active task records, live PRs and Git state are authoritative.

## Active tasks

None.

## Closed acceptance and release-preparation follow-ups

- PR #67 / `517968539bdfd7d189677b669bf0899c35fccec1` — issues #68-#70 closed with exact-SHA production-like browser acceptance evidence classified `STAGING_PROVEN`.
- PR #73 / `06d8d94aafd73de996eb4ea93705e8a45fbadafb` — issue #71 closed with controlled Platform DB outage evidence classified `STAGING_PROVEN` for that staging failure path only.
- PR #74 / `24eaa4ca5e38bb255db95a989c0ff02e954360f3` — issue #72 closed with focused CMS publication-state and privileged-audit regressions; no runtime defect found.
- PR #75 / `4fc6fcccea00bdd8d7679595b92d189cb572dd35` — final Functional Acceptance matrix reconciliation merged; FAV-01 through FAV-05 are closed for the delivered staging-verifiable scope.
- PR #77 / `1e6e21f0963406d4e58c39b347a49cfa4529bd1c` — delivered-surface UI/UX remediation merged with clean browser Visual/Accessibility evidence.
- PR #86 / `5d3628f8c6ba2e454246f24947ebe08ca93cf684` — issue #81 closed; authenticated Account Overview and provisioning-status UX delivered with full production-like browser evidence.
- PR #92 / `c18432df6b387932aa04e1eb269677c9078d9063` — fail-closed non-secret Production Go-Live verification evidence packet prepared; actual production execution remains issue #91.
- PR #94 / `26ff602696c597aac0833415b0a47af5d427a52d` — risk-based E2E architecture plus required bounded Chromium/Firefox/WebKit portability, desktop/tablet/mobile responsive and representative browser-security coverage merged.
- PR #99 / `21d67c7e7edb533f9765ff96417f2ab2fbb1aea8` — issue #98 closed; existing Phase 7 release validation now includes isolated synthetic existing-data upgrade, candidate smoke, old-code rollback smoke against the post-upgrade database and candidate redeploy smoke with durable `STAGING_PROVEN` evidence.
- PR #100 / `8a4fd46db04d2476b6fea7fb47fdd58443548ac3` — archived the completed migration/rollback validation task and closed its governance lifecycle.
- PR #102 / `ee235cbbdd379a5047fede98ff79a0e35e22ce76` — issue #101 closed; Phase 7 now proves exact response `X-Request-ID` to matching structured request-completion log correlation with bounded method/status agreement and non-secret `STAGING_PROVEN` evidence.
- PR #104 / `18bd5b2c3b4496677cc58df41fd50c6387e9e6f8` — archived the completed observability-correlation task and closed its governance lifecycle.
- PR #106 / `8030f98d7280c16705f34f2d29c8ebd7fc85f285` — issue #105 closed; required acceptance now includes zero-retry Chromium public dependency resilience proving Canary DB and Redis failure, deterministic restoration and successful browser recovery.

## Current project phase

**Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**

**Phase 7 — Production hardening and operations: COMPLETE**

The E2E coverage-hardening programme is continuous verification and does not reopen either completed phase.

## Operational release state

- **Production Readiness: STAGING_PROVEN**
- **Functional Acceptance: STAGING_PROVEN for the currently delivered staging-verifiable functional surface**
- **Visual / UX Acceptance: PASS for the currently delivered staging-verifiable launch scope**
- **Production Go-Live Gate: PENDING PRODUCTION VERIFICATION**
- **Production Verification: REQUIRED BEFORE GO-LIVE**

No staging evidence may be promoted to `PRODUCTION_PROVEN`.

## Next work

No repository/staging E2E task is currently active.

Further accessibility-interaction, repeated-run and soak slices remain optional incremental work under ADR 0008 and `docs/testing/E2E_COVERAGE_ROADMAP.md`. Additional resilience work should start only for a distinct recovery gap beyond current Canary/Redis, Phase 7, Platform DB outage, feature and integration evidence.

Issue #91 remains the single production execution tracker. Resume it only when the exact final deployed production SHA, explicit production deployment/verification authorization and access to collect sanitized production evidence are available.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate and requires explicit authorization before external repository writes if it is part of launch scope.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
