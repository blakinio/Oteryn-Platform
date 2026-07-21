---
task_id: OTERYN-20260721-ui-architecture-direction
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/SYSTEM_ARCHITECTURE.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - routes/web.php
search_first:
  - open PRs and active tasks overlapping docs/design, presentation architecture or Visual UX acceptance ownership
  - current public, identity/account and admin Blade shells plus public/css/app.css
  - PR #67 Visual UX Acceptance Matrix and follow-up UI/UX task definition
optional_reads:
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/TEST_STRATEGY.md
---

# OTERYN-20260721-ui-architecture-direction

## Goal

Create the durable information architecture, UI architecture, visual direction, minimal design system, responsive strategy and per-surface blueprint for the currently delivered Oteryn Platform frontend so the subsequent UI implementation task can execute a production-ready redesign without inventing product structure or misrepresenting undelivered features.

This task is architecture/documentation only. It does not redesign all screens, change backend behavior, alter security or authorization semantics, or claim Visual / UX Acceptance PASS.

## Acceptance criteria

- [ ] Inventory every currently delivered user-facing public, Identity/account, character and administrator surface from current routes, code and acceptance evidence.
- [ ] Mark missing or logically future navigation destinations as `FUTURE-READY` rather than delivered functionality.
- [ ] Define final Information Architecture for Public Portal, Account Center and Admin Console.
- [ ] Define desktop, tablet and mobile shell behavior, including responsive tables and long-content containment.
- [ ] Define durable modern dark-fantasy MMORPG visual direction and explicit art/UI asset boundaries.
- [ ] Define minimal reusable design tokens and component vocabulary.
- [ ] Define branded error, empty, dependency and authorization-denied state architecture.
- [ ] Provide a Surface Blueprint for every delivered screen/state identified by acceptance evidence.
- [ ] Provide a bounded future implementation sequence split into small slices rather than one redesign PR.
- [ ] Record the durable shell/IA decision in an ADR.
- [ ] Keep Visual / UX Acceptance explicitly `FAIL`/not reclassified until implementation and screenshot acceptance rerun.
- [ ] Keep account overview and provisioning-status UI clearly separated from current delivery when no current route/view exists.

## Ownership

```yaml
owned_paths:
  - docs/design/**
  - docs/architecture/adr/0008-oteryn-frontend-information-and-shell-architecture.md
  - docs/agents/tasks/active/OTERYN-20260721-ui-architecture-direction.md
modules:
  - architecture
  - WebCMS
  - PublicGameData
  - IdentityPresentation
  - AccountPresentation
  - AdminPresentation
  - ErrorPresentation
dependencies:
  - main@24eaa4ca5e38bb255db95a989c0ff02e954360f3
  - merged Functional Acceptance Matrix in docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - open PR #67 Visual UX Acceptance evidence and its follow-up task definition
blockers:
  - none for architecture documentation
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T09:47:12+02:00
head: 24eaa4ca5e38bb255db95a989c0ff02e954360f3
branch: task/OTERYN-20260721-ui-architecture-direction
pr: none
status: implementing
context_routes:
  - architecture
  - web-cms
  - auth-identity
  - accounts-characters
  - public-game-data
  - admin-rbac
  - testing
  - agent-governance
owned_paths:
  - docs/design/**
  - docs/architecture/adr/0008-oteryn-frontend-information-and-shell-architecture.md
  - docs/agents/tasks/active/OTERYN-20260721-ui-architecture-direction.md
proven:
  - main head at task start is 24eaa4ca5e38bb255db95a989c0ff02e954360f3.
  - Current routes deliver Home, registration, login/logout, password recovery/reset/change, MFA challenge/settings/enrollment/disable, character creation, Admin dashboard/news/pages/roles/audit, News list/detail, managed public pages, Highscores, embedded character search plus Character detail, Guild detail, Online and Servers.
  - Current routes do not deliver a general Account Center/overview route, a dedicated provisioning-status screen, a Guilds index, or a standalone Character Search results screen.
  - Current public and admin shells share a minimal stylesheet; Identity/account flows are standalone browser-default pages.
  - PR #67 records Visual / UX Acceptance as FAIL and defines the separate implementation follow-up OTERYN-20260721-ui-ux-launch-readiness.
  - PR #75 currently owns docs/agents/ACTIVE_WORK.md; this task intentionally does not edit that shared index.
derived:
  - A documentation-first architecture slice is path-disjoint from PR #67 acceptance harness and the future presentation implementation paths it defines.
  - Account Center IA can be defined as a target shell while Current/Future-Ready labels prevent an undelivered overview/provisioning route from being represented as already available.
unknown:
  - final per-surface Visual / UX classifications after implementation and browser acceptance rerun
conflicts: []
first_failure:
  marker: visual UX launch gate
  evidence: PR #67 Visual UX Acceptance Matrix classifies the currently delivered UI as FAIL and identifies navigation, account orientation, responsive overflow, error recovery and design-system blockers
rejected_hypotheses:
  - The existing OTERYN-20260721-ui-ux-launch-readiness definition should be implemented directly on PR #67 branch: rejected because governance requires one task branch per substantial task and PR #67 owns acceptance harness/evidence work.
  - Account Overview and provisioning status can be documented as CURRENT: rejected because no current route/view delivers those screens.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-ui-architecture-direction.md
validation:
  - command: live GitHub preflight against main, open PRs #67/#75, current routes, Functional Acceptance Matrix, current shells and stylesheet
    result: PASS
    evidence: documentation paths are disjoint; ACTIVE_WORK overlap is avoided; delivered versus missing surfaces are explicitly identified
blockers:
  - none
next_action: Write the five design source-of-truth documents and ADR 0008 from current routes, acceptance evidence and existing architecture boundaries, then validate cross-document consistency and open a draft PR.
```

## Notes

`docs/agents/ACTIVE_WORK.md` is intentionally not edited because open PR #75 owns that shared path. This task consumes PR #67 visual evidence but does not modify its acceptance workflow, scripts, matrix or implementation follow-up task record.
