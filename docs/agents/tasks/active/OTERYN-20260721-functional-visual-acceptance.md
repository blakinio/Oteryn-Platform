---
task_id: OTERYN-20260721-functional-visual-acceptance
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/MODULE_CATALOG.md
search_first:
  - routes/web.php
  - resources/views/**
  - public/css/**
  - tests/**
  - .github/workflows/**
  - open PRs and active tasks for overlapping acceptance intent
optional_reads:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
---

# OTERYN-20260721-functional-visual-acceptance

## Goal

Execute the independent Visual / UI / UX Acceptance pass for every currently delivered Oteryn Platform web surface on exact production-candidate code, with responsive browser evidence and accessibility/keyboard checks. Consume the separately owned Functional Acceptance result from `OTERYN-20260721-functional-acceptance-validation` / PR #66 rather than duplicating its matrix, and keep the two acceptance gates independent in the final launch-readiness conclusion.

## Acceptance criteria

- [ ] Inventory every currently delivered rendered web surface and meaningful visual state from live routes/controllers/views, including public, identity, account/character, admin/RBAC/CMS/audit, error, empty, validation and authorization-denied states.
- [ ] Do not duplicate `docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md` or functional acceptance work owned by PR #66; reference its exact evidence/result when combining final gates.
- [ ] Produce a Visual / UX Acceptance Matrix for every delivered surface covering desktop, tablet where materially different, mobile, navigation, typography, spacing, hierarchy, forms, tables, empty/loading/dependency-failure/validation/error states, accessibility, keyboard navigation, responsive behavior and defects.
- [ ] Classify each surface using the repository taxonomy when present; otherwise use `PRODUCTION_READY`, `FUNCTIONAL_BUT_NEEDS_POLISH`, `UX_BLOCKER`, or `VISUAL_BLOCKER`.
- [ ] Assess the effective design system for colors, typography, spacing, cards, buttons, inputs, tables, alerts, badges/statuses, navigation, pagination and responsive breakpoints; explicitly identify a technical MVP when that is what the implementation provides.
- [ ] Execute browser-based production-like validation on an exact SHA and capture representative desktop and mobile screenshot evidence for homepage, news, online, highscores, servers, character search/detail, login/register, MFA, account/character creation, admin, CMS, audit and representative error/empty states; add tablet evidence where the layout materially differs.
- [ ] Validate UX criteria including location/orientation, navigation consistency, primary/secondary actions, form labels/messages, visible validation, operation confirmation, dead ends, auth/account/admin flow continuity, small-screen horizontal overflow, table usability, long-content resilience, empty states and safe failure messaging.
- [ ] Validate at minimum semantic headings, form labels, keyboard navigation, visible focus, color contrast, link/button distinction, table semantics, justified ARIA and no critical color-only information.
- [ ] Do not downgrade functionally passing behavior solely for visual defects; record independent visual/UX gaps.
- [ ] Create a bounded UI/UX follow-up task record for launch-blocking or polish gaps without mixing visual repair work into backend/security/integration changes.
- [ ] Do not declare public launch readiness unless both the separately owned Functional Acceptance gate and this Visual / UX Acceptance gate satisfy their independent criteria.

## Ownership

```yaml
owned_paths:
  - .github/workflows/acceptance-validation.yml
  - scripts/acceptance/**
  - docs/acceptance/**
  - docs/agents/tasks/active/OTERYN-20260721-functional-visual-acceptance.md
  - docs/agents/tasks/active/OTERYN-20260721-ui-ux-launch-readiness.md
modules:
  - VisualUXAcceptance
  - WebCMS
  - PublicGameData
  - Identity
  - Accounts
  - Characters
  - Admin
  - RBAC
  - Audit
dependencies:
  - main@221a13f6d7fba28ba765d67594a5cce4bf9523c4
  - PR #66 / OTERYN-20260721-functional-acceptance-validation owns the Functional Acceptance Matrix and production smoke checklist
  - OTERYN-20260720-phase7-production-evidence-collection provides existing staging evidence only; final production-only task remains independently blocked
blockers:
  - none
cross_repository_tasks:
  - authoritative Platform game-login bridge remains outside this visual acceptance task unless separately authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T22:20:27Z
head: 5b5bbba2b5073147469d2d0469a9b89db64f7206
branch: task/OTERYN-20260721-functional-visual-acceptance
pr: 67
status: investigating
context_routes:
  - testing
  - web-cms
  - auth-identity
  - accounts-characters
  - public-game-data
  - admin-rbac
  - security
  - agent-governance
owned_paths:
  - .github/workflows/acceptance-validation.yml
  - scripts/acceptance/**
  - docs/acceptance/**
  - docs/agents/tasks/active/OTERYN-20260721-functional-visual-acceptance.md
  - docs/agents/tasks/active/OTERYN-20260721-ui-ux-launch-readiness.md
proven:
  - Current main head at task start is 221a13f6d7fba28ba765d67594a5cce4bf9523c4.
  - PR #66 independently owns functional acceptance evidence under docs/testing/**; this task must not duplicate that work or claim those paths.
  - Existing Phase 7 controlled production-like evidence does not constitute visual/UX acceptance or final production proof.
  - Current public UI source uses a small shared Blade/CSS layer with a dark palette, basic cards/forms/tables and public navigation; rendering alone is not accepted as production-ready visual evidence.
  - Identity, MFA, password and character-create views are standalone minimally styled HTML documents rather than members of the public or administrator layout.
  - Administrator surfaces use the shared basic stylesheet and navigation but expose dense tables without an explicit small-screen table strategy in the current CSS.
derived:
  - Visual/UX acceptance must remain a separate gate from PR #66 functional acceptance.
  - Visual fixes, if required, should be executed in a separate bounded UI/UX task after defects are evidenced.
unknown:
  - complete delivered visual-state inventory on current main
  - desktop/mobile/tablet browser behavior on exact acceptance SHA
  - screenshot evidence for authenticated and privileged flows
  - final per-surface visual quality classifications
  - whether Visual / UX Acceptance can PASS without a follow-up implementation task
conflicts: []
first_failure:
  marker: overlapping functional acceptance intent
  evidence: open PR #66 was discovered after PR #67 creation and owns docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md plus the functional acceptance inventory; PR #67 scope has been narrowed to independent visual/UI/UX acceptance to remove the overlap
rejected_hypotheses:
  - Existing Phase 7 functional regression evidence proves production-ready UI: rejected because it does not validate visual quality, responsive behavior, keyboard UX or screenshot evidence.
  - PR #67 should produce a second Functional Acceptance Matrix: rejected because PR #66 already owns that bounded work.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-functional-visual-acceptance.md
validation:
  - command: live GitHub overlap preflight against open PRs #65, #66 and #67
    result: PASS
    evidence: PR #67 is now path-disjoint from PR #66 functional acceptance and PR #65 Phase 7/go-live status ownership
blockers:
  - none
next_action: Complete the rendered view/state inventory, then add the smallest browser-based production-like visual acceptance harness capable of generating exact-SHA desktop/mobile screenshot and UX/accessibility evidence without modifying product behavior.
```

## Notes

PR #66 is the independent source for the Functional Acceptance result. PR #67 must stay visual/UI/UX-focused and may reference, but not edit or duplicate, PR #66-owned functional acceptance artifacts. The active Phase 7/go-live work owns shared project-status paths; this task intentionally does not modify them. Acceptance evidence must not be mislabeled as final production proof.
