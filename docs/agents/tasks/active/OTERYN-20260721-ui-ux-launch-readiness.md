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
  - tests/Feature/HomeTest.php
  - scripts/acceptance/tests/helpers.mjs
  - scripts/acceptance/tests/character-boundaries-acceptance.spec.mjs
  - scripts/acceptance/tests/admin-acceptance.spec.mjs
  - scripts/acceptance/tests/mfa-security-acceptance.spec.mjs
  - scripts/acceptance/tests/password-change-acceptance.spec.mjs
  - scripts/acceptance/tests/password-recovery-acceptance.spec.mjs
  - docs/agents/tasks/active/OTERYN-20260721-ui-ux-launch-readiness.md
modules:
  - WebCMS
  - PublicGameData
  - IdentityPresentation
  - AccountPresentation
  - AdminPresentation
  - ErrorPresentation
  - AcceptanceTesting
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
updated_at: 2026-07-21T11:18:00+02:00
head: 25bdd8d0158aa1990a7507aef3a558ae6871d524
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
  - tests/Feature/HomeTest.php
  - scripts/acceptance/tests/helpers.mjs
  - scripts/acceptance/tests/character-boundaries-acceptance.spec.mjs
  - scripts/acceptance/tests/admin-acceptance.spec.mjs
  - scripts/acceptance/tests/mfa-security-acceptance.spec.mjs
  - scripts/acceptance/tests/password-change-acceptance.spec.mjs
  - scripts/acceptance/tests/password-recovery-acceptance.spec.mjs
  - docs/agents/tasks/active/OTERYN-20260721-ui-ux-launch-readiness.md
proven:
  - PR #67 is merged and records Functional Acceptance as STAGING_PROVEN while the baseline Visual UX Acceptance remains FAIL.
  - PR #76 merged the authoritative UI architecture, design-system and responsive-strategy documentation before implementation validation.
  - PR #77 contains the bounded presentation remediation: shared public and identity shells, administrator console shell, responsive design-system CSS, bounded MFA provisioning presentation, contained table/long-content strategies, user-facing character options and product-owned 403/404/503 views.
  - Exact implementation SHA 6e1fbe57713a1268edb5fc7d30a34775c0dcb7b2 passed CI run 29816366835, Agent Governance run 29816366846, Phase 7 Production-Like Validation run 29816366923, Platform DB Outage Validation run 29816366901 and acceptance smoke run 29816366979.
  - The only PHPUnit regression found during implementation was an obsolete HomeTest assertion for removed technical copy; the regression test now checks player-facing home landmarks.
  - Full run 29816563931 identified stale MFA-secret and ambiguous character-form harness locators; both were corrected without weakening security or rate limits.
  - Full run 29817101655 then progressed further and identified four remaining pre-redesign text/heading assertions only: administrator dashboard heading, product-owned 403 heading and two MFA settings messages.
  - Those four acceptance landmarks are now aligned to stable semantic headings and alerts in the redesigned UI; no backend or authorization expectation was changed.
derived:
  - 429 responses observed in earlier failed runs followed retry attempts after harness assertion failures and do not establish a product rate-limit defect.
  - Account Overview/provisioning status cannot be invented under this task because no current route/read model delivers those surfaces.
unknown:
  - final per-surface Visual UX classifications after the next corrected exact-head full browser rerun
conflicts: []
first_failure:
  marker: full visual acceptance harness presentation landmarks
  evidence: full run 29817101655 failed before visual capture only on four old UI text/heading expectations while HTTP statuses and the underlying flows reached the expected redesigned surfaces
rejected_hypotheses:
  - Rendering without server errors is sufficient visual acceptance: rejected by responsive, navigation, error-state and design-system evidence.
  - UI task should change provisioning/backend behavior to make the dashboard possible: rejected; backend/read-model work requires a separate bounded task.
  - Product security rate limits should be weakened to make the full harness pass: rejected; only deterministic presentation locators are corrected.
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
  - tests/Feature/HomeTest.php
  - scripts/acceptance/tests/helpers.mjs
  - scripts/acceptance/tests/character-boundaries-acceptance.spec.mjs
  - scripts/acceptance/tests/admin-acceptance.spec.mjs
  - scripts/acceptance/tests/mfa-security-acceptance.spec.mjs
  - scripts/acceptance/tests/password-change-acceptance.spec.mjs
  - scripts/acceptance/tests/password-recovery-acceptance.spec.mjs
  - docs/agents/tasks/active/OTERYN-20260721-ui-ux-launch-readiness.md
validation:
  - command: exact-head native gates on 640bf8671b465356cc2fe07ef228e91124b8f0d6
    result: PASS
    evidence: CI run 29817101671; Agent Governance run 29817101763; Phase 7 Production-Like Validation run 29817101666; Platform DB Outage Validation run 29817101829; Acceptance smoke run 29817086741
  - command: full acceptance profile on 640bf8671b465356cc2fe07ef228e91124b8f0d6
    result: FAIL
    evidence: run 29817101655 failed only on four stale pre-redesign presentation assertions; all are corrected on the current branch for rerun
blockers:
  - separate account/provisioning-state dependency if required by existing product data boundaries
next_action: Validate the final presentation-landmark harness corrections on the current exact head, update the full-profile validation ref, rerun full production-like browser and visual evidence, then classify delivered surfaces and the separate missing account/provisioning surface without overclaiming launch readiness.
```

## Notes

This task is intentionally a presentation-layer follow-up. Test changes are bounded presentation-regression and acceptance-locator compatibility updates only. Any need for new account/provisioning read models, controller actions, mutation semantics or data ownership changes must be split into a separate bounded task before implementation.
