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
  - merged acceptance browser harness from PR #67
  - merged UI architecture and design-system contract from PR #76
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
updated_at: 2026-07-21T10:42:00+02:00
head: e7b69cf646fdd6c5495a7f19e1d1ba98012bf987
branch: task/OTERYN-20260721-ui-ux-launch-readiness
pr: 77
status: validating
context_routes:
  - web-cms
  - auth-identity
  - accounts-characters
  - public-game-data
  - admin-rbac
  - security
  - testing
  - agent-governance
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
  - PR #67 is merged and records Functional Acceptance as STAGING_PROVEN while Visual UX Acceptance remains FAIL.
  - Final integrated acceptance evidence on validated implementation SHA 4c3ec48dc5c13c6b37169291be25e09f3856de4d passed 12 Playwright tests with zero failures and retained the visual blocker set.
  - PR #76 merged the authoritative UI architecture, design-system and responsive-strategy documentation before implementation validation.
  - PR #77 now contains a shared public shell, identity/account-operation shell, administrator console shell, responsive design-system CSS, bounded MFA provisioning presentation, contained table/long-content strategies and product-owned 403/404/503 views.
  - Acceptance E2E and Visual UX smoke run 29814890198 passed on implementation head e7b69cf646fdd6c5495a7f19e1d1ba98012bf987.
  - Platform DB Outage Validation run 29814890246 passed on implementation head e7b69cf646fdd6c5495a7f19e1d1ba98012bf987.
derived:
  - The presentation fixes remain path-bounded and do not require changes to backend/security contracts.
  - Account Overview/provisioning status cannot be invented under this task because no current route/read model delivers those surfaces.
unknown:
  - final per-surface Visual UX classifications after exact-head full browser rerun
conflicts: []
first_failure:
  marker: repository regression compatibility
  evidence: CI run 29814890184 and Phase 7 Production-Like Validation run 29814890320 fail only at composer test / exact-SHA critical regression suite while acceptance smoke and Platform DB outage validation pass; presentation landmark compatibility is being narrowed without changing behavior
rejected_hypotheses:
  - Rendering without server errors is sufficient visual acceptance: rejected by responsive, navigation, error-state and design-system evidence.
  - UI task should change provisioning/backend behavior to make the dashboard possible: rejected; backend/read-model work requires a separate bounded task.
  - The implementation should reuse PR #67 or PR #76 branches: rejected because substantial implementation requires its own dedicated task branch.
changed_paths:
  - public/css/app.css
  - resources/views/game/layout.blade.php
  - resources/views/home.blade.php
  - resources/views/game/highscores.blade.php
  - resources/views/game/guild.blade.php
  - resources/views/game/online.blade.php
  - resources/views/game/servers.blade.php
  - resources/views/news/index.blade.php
  - resources/views/identity/layout.blade.php
  - resources/views/identity/login.blade.php
  - resources/views/identity/register.blade.php
  - resources/views/identity/forgot-password.blade.php
  - resources/views/identity/reset-password.blade.php
  - resources/views/identity/change-password.blade.php
  - resources/views/identity/mfa/challenge.blade.php
  - resources/views/identity/mfa/settings.blade.php
  - resources/views/identity/mfa/recovery-codes.blade.php
  - resources/views/characters/create.blade.php
  - resources/views/admin/layout.blade.php
  - resources/views/admin/dashboard.blade.php
  - resources/views/admin/news/index.blade.php
  - resources/views/admin/news/form.blade.php
  - resources/views/admin/pages/index.blade.php
  - resources/views/admin/pages/form.blade.php
  - resources/views/admin/roles/index.blade.php
  - resources/views/admin/audit/index.blade.php
  - resources/views/errors/layout.blade.php
  - resources/views/errors/403.blade.php
  - resources/views/errors/404.blade.php
  - resources/views/errors/503.blade.php
  - docs/agents/tasks/active/OTERYN-20260721-ui-ux-launch-readiness.md
validation:
  - command: lean live-state preflight against main, merged PR #67 evidence and overlapping design work
    result: PASS
    evidence: no overlapping presentation implementation ownership was found; PR #76 merged design-only guidance before final validation
  - command: automatic PR acceptance smoke on e7b69cf646fdd6c5495a7f19e1d1ba98012bf987
    result: PASS
    evidence: Acceptance E2E and Visual UX run 29814890198 completed successfully after restoring accepted public landmarks
  - command: automatic PR Platform DB outage validation on e7b69cf646fdd6c5495a7f19e1d1ba98012bf987
    result: PASS
    evidence: Platform DB Outage Validation run 29814890246 completed successfully
  - command: automatic PR CI and Phase 7 regression on e7b69cf646fdd6c5495a7f19e1d1ba98012bf987
    result: FAIL
    evidence: CI run 29814890184 fails only Run tests; Phase 7 run 29814890320 reaches and fails only Run exact-SHA critical regression suite
blockers:
  - separate account/provisioning-state dependency if required by existing product data boundaries
next_action: Resolve the remaining exact PHPUnit presentation-regression assertions, obtain clean exact-head CI/governance/production-like/smoke gates, then run a full exact-SHA Visual UX browser evidence profile before final classification.
```

## Notes

This task is intentionally a presentation-layer follow-up. Any need for new account/provisioning read models, controller actions, mutation semantics or data ownership changes must be split into a separate bounded task before implementation.
