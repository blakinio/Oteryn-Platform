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
  - main@3f3582fc74e1ae348c222d76255ed8b9823e8536
  - merged PR #76 architecture/design source of truth
  - merged Functional Acceptance Matrix
  - merged Visual UX Acceptance Matrix and OTERYN-20260721-ui-ux-launch-readiness follow-up definition
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T10:07:00+02:00
head: 3f3582fc74e1ae348c222d76255ed8b9823e8536
branch: task/OTERYN-20260721-ui-architecture-handover
pr: none
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
  - PR #76 was squash-merged to main as 3f3582fc74e1ae348c222d76255ed8b9823e8536.
  - The merged source of truth consists of INFORMATION_ARCHITECTURE.md, VISUAL_DIRECTION.md, UI_ARCHITECTURE.md, DESIGN_SYSTEM.md, RESPONSIVE_STRATEGY.md and ADR 0008.
  - Current routes do not deliver a general Account Center overview, dedicated provisioning-status screen, Guilds index or standalone Character Search results screen; the design documentation does not represent them as CURRENT.
  - Visual UX Acceptance remains FAIL and was not reclassified by this architecture task.
  - PR #76 head cec3d667362ded450498b0b0ca042ec19247d9df passed CI #856, Phase 7 Production-Like Validation #98, Platform DB Outage Validation #28 and Agent Governance #776.
  - PR #76 had no unresolved review threads or submitted review blockers before merge.
derived:
  - The architecture/design task is complete and the next implementation agent can use the merged design documents and ADR 0008 without rediscovering visual direction or information architecture.
  - Account Overview, provisioning-status and authoritative account character-list UI remain separate bounded backend/read-model dependencies where current routes do not expose the required surface.
unknown:
  - final per-surface Visual UX classifications after UI implementation and exact-final-SHA browser acceptance rerun
conflicts: []
first_failure:
  marker: Agent Governance checkpoint validation on 3592abdccb1293e952eb8ae9b30e860270e2d674
  evidence: run 29812365675 rejected unsupported validation result PENDING; checkpoint-only fix cec3d667362ded450498b0b0ca042ec19247d9df then passed Agent Governance run 29812555881 / #776
rejected_hypotheses:
  - Account Overview and provisioning status can be documented as CURRENT: rejected because no current route/view delivers those screens.
  - A permanent three-column public shell should be used everywhere: rejected because table-heavy and smaller-desktop surfaces require wider main content and responsive recomposition.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-ui-architecture-direction.md
  - docs/architecture/adr/0008-oteryn-frontend-information-and-shell-architecture.md
  - docs/design/DESIGN_SYSTEM.md
  - docs/design/INFORMATION_ARCHITECTURE.md
  - docs/design/RESPONSIVE_STRATEGY.md
  - docs/design/UI_ARCHITECTURE.md
  - docs/design/VISUAL_DIRECTION.md
validation:
  - command: PR #76 current-head GitHub checks on cec3d667362ded450498b0b0ca042ec19247d9df
    result: PASS
    evidence: CI #856, Phase 7 Production-Like Validation #98, Platform DB Outage Validation #28 and Agent Governance #776 all completed successfully
  - command: PR #76 review-thread and review blocker inspection
    result: PASS
    evidence: no review threads and no submitted reviews were present
  - command: squash merge PR #76 with expected head cec3d667362ded450498b0b0ca042ec19247d9df
    result: PASS
    evidence: merged to main as 3f3582fc74e1ae348c222d76255ed8b9823e8536
  - command: compare 3f3582fc74e1ae348c222d76255ed8b9823e8536 to main after merge
    result: PASS
    evidence: identical, ahead_by 0, behind_by 0
blockers:
  - none
next_action: Transition to the existing OTERYN-20260721-ui-ux-launch-readiness implementation task on its own dedicated branch, archiving this completed architecture task record as part of that handoff.
```

## Notes

The durable architecture source of truth is merged. This record remains active only for compact handoff into the separately bounded UI/UX implementation task; Visual / UX Acceptance remains FAIL until implementation and exact-final-SHA acceptance evidence prove otherwise.
