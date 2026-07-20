---
task_id: OTERYN-20260721-ui-ux-launch-readiness
required_reads:
  - AGENTS.md
  - docs/acceptance/VISUAL_UX_ACCEPTANCE_MATRIX.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/TEST_STRATEGY.md
search_first:
  - resources/views/**
  - public/css/**
  - routes/web.php
  - scripts/acceptance/**
optional_reads:
  - docs/testing/FUNCTIONAL_ACCEPTANCE_MATRIX.md
---

# OTERYN-20260721-ui-ux-launch-readiness

## Goal

Resolve the evidenced launch-blocking Visual / UI / UX defects without changing backend business logic, security contracts, database ownership, Canary integration semantics or authorization behavior. Establish a coherent Oteryn presentation/navigation layer, resilient responsive behavior and product-owned error/empty states, then rerun the browser acceptance evidence and reclassify every delivered surface.

## Acceptance criteria

- [ ] Public, Identity, MFA, account/character and administrator surfaces use coherent Oteryn visual foundations without changing their functional/security semantics.
- [ ] Public navigation exposes appropriate Login/Register or authenticated Account entry points and indicates current location; administrator/account navigation provides clear orientation and a usable logout path.
- [ ] Identity/account flows provide logical cross-navigation between login, registration, password recovery, MFA/settings and account actions without creating authentication/security shortcuts.
- [ ] Character creation uses user-facing option labels rather than raw numeric values and is reachable from a coherent account navigation context.
- [ ] No required desktop/tablet/mobile acceptance viewport has unexplained document-level horizontal overflow.
- [ ] Highscores, guild, CMS, RBAC and audit tables use an intentional small-screen strategy such as contained horizontal scrolling or a responsive transformation; the whole document must not widen.
- [ ] Long names, emails, channel names, messages, audit metadata and MFA provisioning content wrap or scroll inside bounded components without breaking page layout.
- [ ] MFA enrollment has a production-usable presentation for authenticator setup, including bounded provisioning data and clear manual/copy/QR hierarchy where implementable without weakening secret handling.
- [ ] Product-owned 403, 404 and 503 views are CSP-compatible, preserve safe generic messaging, provide appropriate recovery navigation and expose no raw technical exception/secret data.
- [ ] Buttons and actions have consistent primary/secondary/destructive hierarchy, including administrator role removal and MFA disable actions.
- [ ] Forms, selects, alerts, statuses, badges, navigation and pagination use shared reusable patterns rather than browser-default or framework-default presentation.
- [ ] Administrator list empty states are explicit and actionable where appropriate.
- [ ] Existing semantic headings, labels, table semantics, keyboard access, visible focus, contrast and non-color-only status communication are preserved or improved.
- [ ] Visual UX Acceptance browser evidence is rerun on the exact final task SHA for desktop/mobile and materially distinct tablet surfaces, with updated screenshots/artifact and per-surface classifications.
- [ ] Visual / UX Acceptance is not marked PASS while any evidenced `UX_BLOCKER` or `VISUAL_BLOCKER` remains.

## Ownership

```yaml
owned_paths:
  - resources/views/layouts/**
  - resources/views/game/**
  - resources/views/news/**
  - resources/views/pages/**
  - resources/views/identity/**
  - resources/views/characters/**
  - resources/views/admin/**
  - resources/views/errors/**
  - public/css/app.css
  - docs/agents/tasks/active/OTERYN-20260721-ui-ux-launch-readiness.md
modules:
  - WebCMS
  - PublicGameData
  - IdentityPresentation
  - AccountPresentation
  - AdminPresentation
  - ErrorPresentation
dependencies:
  - docs/acceptance/VISUAL_UX_ACCEPTANCE_MATRIX.md
  - acceptance browser harness in PR #67 or its merged successor
  - separate account/provisioning-state task if the required dashboard/status surface cannot be rendered from existing routes/read data
blockers:
  - account/provisioning status UX may require a separately authorized controller/read-model task; do not add backend/data behavior under this UI task
cross_repository_tasks: []
```

## Explicit non-goals

- No Canary schema/write-contract changes.
- No provisioning algorithm, retry semantics or binding-state changes.
- No authentication/session/MFA security-policy changes.
- No RBAC authorization changes.
- No database privilege changes.
- No business-logic changes hidden inside view work.

## Evidence-driven defect set

1. Public navigation does not expose authentication/account entry points and no active-state pattern exists.
2. Login/register/password/MFA/character-create surfaces are standalone browser-default pages rather than a coherent Oteryn account shell.
3. Sixteen captured surface/viewport combinations have document-level horizontal overflow.
4. MFA enrollment overflows mobile on raw provisioning URI and lacks production-grade setup affordances.
5. 403/404/503 states are dead-end framework/default pages; 403/503 framework inline styles are rejected by the application CSP.
6. Character creation exposes numeric sex values and has no account/dashboard context.
7. Admin news/forms/roles/audit are not usable responsively; role actions lack strong destructive hierarchy and audit metadata is raw/dense.
8. Current stylesheet has no responsive breakpoint system or reusable token/component design system.
9. Administrator empty states and pagination presentation are inconsistent/technical-MVP quality.
10. Account dashboard/provisioning status/recovery is not delivered; if implementation requires backend/read-model work, create and complete a separate bounded account task before claiming full visual acceptance.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T00:45:00+02:00
head: 1df15189baddf680c573428061781c25c3045df3
branch: task/OTERYN-20260721-functional-visual-acceptance
pr: 67
status: ready
context_routes:
  - web-cms
  - auth-identity
  - accounts-characters
  - public-game-data
  - admin-rbac
  - security
  - testing
owned_paths:
  - resources/views/layouts/**
  - resources/views/game/**
  - resources/views/news/**
  - resources/views/pages/**
  - resources/views/identity/**
  - resources/views/characters/**
  - resources/views/admin/**
  - resources/views/errors/**
  - public/css/app.css
  - docs/agents/tasks/active/OTERYN-20260721-ui-ux-launch-readiness.md
proven:
  - Visual UX Acceptance run 29784023395 completed successfully and produced 71 screenshots on unchanged product UI code relative to main baseline 221a13f6d7fba28ba765d67594a5cce4bf9523c4.
  - Evidence found 16 document-level horizontal-overflow surface/viewport combinations, zero unlabeled controls, zero sampled low-contrast core surfaces and no detected raw technical exception text.
  - Public/admin styled surfaces and standalone identity/account surfaces are visually inconsistent.
  - Product-owned responsive breakpoints and error views are absent in the current delivered UI.
derived:
  - Visual fixes can be performed independently of functional backend/security contracts for the majority of evidenced blockers.
  - Account/provisioning status may require a separate bounded non-UI dependency and must not be invented in Blade/CSS.
unknown:
  - exact final account/provisioning status presentation until the underlying available read state is confirmed
  - final per-surface classifications after implementation and rerun
conflicts: []
first_failure:
  marker: visual UX launch gate
  evidence: docs/acceptance/VISUAL_UX_ACCEPTANCE_MATRIX.md classifies the current Visual / UX Acceptance result as FAIL
rejected_hypotheses:
  - Rendering without server errors is sufficient visual acceptance: rejected by responsive, navigation, error-state and design-system evidence.
  - UI task should change provisioning/backend behavior to make the dashboard possible: rejected; backend/read-model work requires a separate bounded task.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-ui-ux-launch-readiness.md
validation:
  - command: execute only after this task begins on its own branch
    result: NOT_RUN
    evidence: follow-up task definition only
blockers:
  - separate account/provisioning-state dependency if required by existing product data boundaries
next_action: Start from the merged acceptance evidence, implement the smallest coherent shared shell/navigation/design-system foundation first, then fix responsive table/long-content/error-state defects in bounded slices while rerunning browser evidence after each slice.
```

## Notes

This task is intentionally a presentation-layer follow-up. Any need for new account/provisioning read models, controller actions, mutation semantics or data ownership changes must be split into a separate bounded task before implementation.
