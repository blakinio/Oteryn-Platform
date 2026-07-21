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
  - docs/acceptance/VISUAL_UX_ACCEPTANCE_MATRIX.md and the OTERYN-20260721-ui-ux-launch-readiness follow-up definition
optional_reads:
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/TEST_STRATEGY.md
---

# OTERYN-20260721-ui-architecture-direction

## Goal

Create the durable information architecture, UI architecture, visual direction, minimal design system, responsive strategy and per-surface blueprint for the currently delivered Oteryn Platform frontend so the subsequent UI implementation task can execute a production-ready redesign without inventing product structure or misrepresenting undelivered features.

This task is architecture/documentation only. It does not redesign all screens, change backend behavior, alter security or authorization semantics, or claim Visual / UX Acceptance PASS.

## Acceptance criteria

- [x] Inventory every currently delivered user-facing public, Identity/account, character and administrator surface from current routes, code and acceptance evidence.
- [x] Mark missing or logically future navigation destinations as `FUTURE-READY` rather than delivered functionality.
- [x] Define final Information Architecture for Public Portal, Account Center and Admin Console.
- [x] Define desktop, tablet and mobile shell behavior, including responsive tables and long-content containment.
- [x] Define durable modern dark-fantasy MMORPG visual direction and explicit art/UI asset boundaries.
- [x] Define minimal reusable design tokens and component vocabulary.
- [x] Define branded error, empty, dependency and authorization-denied state architecture.
- [x] Provide a Surface Blueprint for every delivered screen/state identified by acceptance evidence.
- [x] Provide a bounded future implementation sequence split into small slices rather than one redesign PR.
- [x] Record the durable shell/IA decision in an ADR.
- [x] Keep Visual / UX Acceptance explicitly `FAIL`/not reclassified until implementation and screenshot acceptance rerun.
- [x] Keep account overview and provisioning-status UI clearly separated from current delivery when no current route/view exists.

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
  - main@517968539bdfd7d189677b669bf0899c35fccec1
  - merged Functional Acceptance Matrix in docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
  - merged Visual UX Acceptance Matrix and follow-up OTERYN-20260721-ui-ux-launch-readiness definition from PR #67
blockers:
  - none for architecture documentation
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T09:56:11+02:00
head: fd72b8540de5aae4f04f47ee732d12e37ffaafe1
branch: task/OTERYN-20260721-ui-architecture-direction
pr: 76
status: ready
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
  - Current routes deliver Home, registration, login/logout, password recovery/reset/change, MFA challenge/settings/enrollment/disable, character creation, Admin dashboard/news/pages/roles/audit, News list/detail, managed public pages, Highscores, embedded character search plus Character detail, Guild detail, Online and Servers.
  - Current routes do not deliver a general Account Center/overview route, a dedicated provisioning-status screen, a Guilds index, or a standalone Character Search results screen.
  - Current public and admin shells share a minimal stylesheet; Identity/account flows are standalone browser-default pages.
  - PR #67 merged to main as 517968539bdfd7d189677b669bf0899c35fccec1 and records Visual / UX Acceptance as FAIL plus the separate implementation follow-up OTERYN-20260721-ui-ux-launch-readiness.
  - docs/design/INFORMATION_ARCHITECTURE.md defines CURRENT, FUTURE-READY and DEPENDENCY IA without advertising missing routes as delivered.
  - docs/design/VISUAL_DIRECTION.md defines the modern dark-fantasy MMORPG direction plus explicit UI/art asset boundaries and placeholder-safe composition.
  - docs/design/DESIGN_SYSTEM.md defines semantic color, typography, spacing, layout and reusable component contracts.
  - docs/design/RESPONSIVE_STRATEGY.md defines intentional wide-desktop/desktop/tablet/mobile composition, responsive table patterns and long-content containment.
  - docs/design/UI_ARCHITECTURE.md defines Public, Identity, Account and Admin shells; product error/state architecture; per-surface blueprints; and bounded implementation slices.
  - ADR 0008 records the durable shell/IA decision and preserves backend/security/authorization boundaries.
  - Visual / UX Acceptance is intentionally not reclassified; final PASS remains dependent on implementation and exact-final-SHA browser evidence.
  - Open PR #75 owns docs/agents/ACTIVE_WORK.md; this task intentionally does not edit that shared index.
derived:
  - The architecture/design source of truth is path-disjoint from the acceptance harness and from the future product UI implementation paths.
  - Account Center IA can be implemented incrementally, but Account Overview/provisioning status/character list require separately bounded authoritative read/controller dependencies where current application surfaces do not expose the necessary data.
unknown:
  - final per-surface Visual / UX classifications after implementation and browser acceptance rerun
conflicts: []
first_failure:
  marker: visual UX launch gate
  evidence: docs/acceptance/VISUAL_UX_ACCEPTANCE_MATRIX.md classifies the currently delivered UI as FAIL and identifies navigation, account orientation, responsive overflow, error recovery and design-system blockers
rejected_hypotheses:
  - The OTERYN-20260721-ui-ux-launch-readiness implementation should be performed on the former PR #67 acceptance branch: rejected because governance requires a dedicated task branch for substantial implementation work.
  - Account Overview and provisioning status can be documented as CURRENT: rejected because no current route/view delivers those screens.
  - A permanent three-column public shell should be used everywhere: rejected because table-heavy/smaller-desktop surfaces require wider main content and responsive recomposition.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-ui-architecture-direction.md
  - docs/architecture/adr/0008-oteryn-frontend-information-and-shell-architecture.md
  - docs/design/DESIGN_SYSTEM.md
  - docs/design/INFORMATION_ARCHITECTURE.md
  - docs/design/RESPONSIVE_STRATEGY.md
  - docs/design/UI_ARCHITECTURE.md
  - docs/design/VISUAL_DIRECTION.md
validation:
  - command: live GitHub preflight against main, open PRs, current routes, Functional Acceptance Matrix, Visual UX Acceptance Matrix, current shells and stylesheet
    result: PASS
    evidence: delivered versus missing surfaces are explicitly separated; design paths are disjoint from acceptance implementation paths; ACTIVE_WORK overlap is avoided
  - command: cross-document architecture consistency review
    result: PASS
    evidence: all five design documents use the same Public Portal / Account Center / Admin split, CURRENT/FUTURE-READY/DEPENDENCY semantics, responsive table requirements and no-fake-route rule
  - command: PR #76 changed-file review
    result: PASS
    evidence: only the owned task record, ADR 0008 and docs/design source-of-truth files are changed
  - command: Agent Governance run 29812297209 / #774 on fd72b8540de5aae4f04f47ee732d12e37ffaafe1
    result: PASS
    evidence: governance validation completed successfully before final checkpoint update
  - command: CI / production-like validation on current documentation head
    result: PENDING
    evidence: CI, Phase 7 Production-Like Validation and Platform DB Outage Validation were still in progress on the pre-checkpoint documentation head; no PASS is claimed here
blockers:
  - none for design architecture completion; merge readiness remains subject to required current-head GitHub checks
next_action: Verify required GitHub checks on the current PR #76 head and merge the documentation task only if the merge gate remains satisfied.
```

## Notes

`docs/agents/ACTIVE_WORK.md` is intentionally not edited because open PR #75 owns that shared path. The architecture task consumes the now-merged PR #67 visual evidence but does not modify its acceptance workflow/scripts or the implementation follow-up task record.
