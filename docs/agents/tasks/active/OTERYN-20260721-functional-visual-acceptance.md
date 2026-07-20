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
optional_reads:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
---

# OTERYN-20260721-functional-visual-acceptance

## Goal

Build and execute a complete acceptance pass for the currently delivered Oteryn Platform surface on the exact current production-candidate code: preserve the already proven functional staging evidence, add any missing functional acceptance coverage, and independently validate visual quality, responsive UX and accessibility with representative browser screenshot evidence from a controlled production-like environment.

## Acceptance criteria

- [ ] Inventory every currently delivered web surface and meaningful state from live routes/controllers/views, including public, identity, account/character, admin/RBAC/CMS/audit, error, empty, validation and authorization-denied states.
- [ ] Produce a Functional Acceptance Matrix tied to exact tested SHA(s), preserving `STAGING_PROVEN` only where existing or new evidence actually proves the behavior.
- [ ] Produce an independent Visual / UX Acceptance Matrix for every delivered surface covering desktop, tablet where materially different, mobile, navigation, typography, spacing, hierarchy, forms, tables, empty/loading/dependency-failure/validation/error states, accessibility, keyboard navigation, responsive behavior and defects.
- [ ] Classify each surface using the repository taxonomy when present; otherwise use `PRODUCTION_READY`, `FUNCTIONAL_BUT_NEEDS_POLISH`, `UX_BLOCKER`, or `VISUAL_BLOCKER`.
- [ ] Assess the effective design system for colors, typography, spacing, cards, buttons, inputs, tables, alerts, badges/statuses, navigation, pagination and responsive breakpoints; explicitly identify a technical MVP when that is what the implementation provides.
- [ ] Execute browser-based production-like validation on an exact SHA and capture representative desktop and mobile screenshot evidence for homepage, news, online, highscores, servers, character search/detail, login/register, MFA, account/character creation, admin, CMS, audit and representative error/empty states; add tablet evidence where the layout materially differs.
- [ ] Validate UX criteria including location/orientation, navigation consistency, primary/secondary actions, form labels/messages, visible validation, operation confirmation, dead ends, auth/account/admin flow continuity, small-screen horizontal overflow, table usability, long-content resilience, empty states and safe failure messaging.
- [ ] Validate at minimum semantic headings, form labels, keyboard navigation, visible focus, color contrast, link/button distinction, table semantics, justified ARIA and no critical color-only information.
- [ ] Do not downgrade functionally passing behavior solely for visual defects; record independent visual/UX gaps.
- [ ] Create bounded UI/UX follow-up task records for launch-blocking or polish gaps without mixing visual repair work into backend/security/integration changes.
- [ ] Do not declare public launch readiness unless both Functional Acceptance and Visual / UX Acceptance gates satisfy their independent criteria.

## Ownership

```yaml
owned_paths:
  - .github/workflows/acceptance-validation.yml
  - scripts/acceptance/**
  - docs/acceptance/**
  - docs/agents/tasks/active/OTERYN-20260721-functional-visual-acceptance.md
  - docs/agents/tasks/active/OTERYN-20260721-ui-ux-launch-readiness.md
modules:
  - AcceptanceValidation
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
  - OTERYN-20260720-phase7-production-evidence-collection (staging-verifiable functional evidence only; final production-only task remains independently blocked)
blockers:
  - none
cross_repository_tasks:
  - authoritative Platform game-login bridge remains outside this acceptance task unless separately authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T22:16:04Z
head: 221a13f6d7fba28ba765d67594a5cce4bf9523c4
branch: task/OTERYN-20260721-functional-visual-acceptance
pr: none
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
  - Existing Phase 7 controlled production-like evidence classifies critical implemented functional flows as STAGING_PROVEN, but does not constitute visual/UX acceptance or final production proof.
  - Current public UI source uses a small shared Blade/CSS layer with a dark palette, basic cards/forms/tables and public navigation; rendering alone is not accepted as production-ready visual evidence.
derived:
  - Visual/UX acceptance must remain a separate gate from existing functional staging evidence.
  - Visual fixes, if required, should be executed in a separate bounded UI/UX task after defects are evidenced.
unknown:
  - complete delivered view/state inventory on current main
  - desktop/mobile/tablet browser behavior on exact acceptance SHA
  - screenshot evidence for authenticated and privileged flows
  - final per-surface visual quality classifications
  - whether Visual / UX Acceptance can PASS without a follow-up implementation task
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Existing Phase 7 functional regression evidence proves production-ready UI: rejected because it does not validate visual quality, responsive behavior, keyboard UX or screenshot evidence.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-functional-visual-acceptance.md
validation:
  - command: not-run
    result: NOT_RUN
    evidence: acceptance harness and matrices are not yet implemented
blockers:
  - none
next_action: Enumerate every rendered web view and meaningful state from routes/controllers/views, then add the smallest browser-based production-like acceptance harness capable of generating exact-SHA desktop/mobile screenshot evidence without modifying product behavior.
```

## Notes

The active Phase 7 production-evidence task owns `docs/agents/ACTIVE_WORK.md` and `docs/agents/PROJECT_STATE.md`; this task intentionally does not modify those shared paths while that ownership remains active. Acceptance evidence must not be mislabeled as final production proof.
