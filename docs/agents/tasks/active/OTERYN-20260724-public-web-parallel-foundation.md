---
task_id: OTERYN-20260724-public-web-parallel-foundation
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/agents/tasks/TASK_TEMPLATE.md
  - docs/architecture/PUBLIC_WEBSITE_EXPANSION_PLAN.md
  - docs/architecture/WIKI_IMPLEMENTATION_PLAN.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/acceptance/VISUAL_UX_ACCEPTANCE_MATRIX.md
search_first:
  - docs/agents/tasks/active/**
  - open pull requests affecting homepage, layouts, routes, permissions or public CSS
  - routes/web.php
  - routes/modules/**
  - resources/views/home*.blade.php
  - resources/views/game/layout.blade.php
  - resources/navigation/public/**
  - app/Cms/PublicNewsQuery.php
  - app/PublicGameData/**
  - app/Admin/AdminPermission.php
optional_reads:
  - PR #139 history
---

# OTERYN-20260724-public-web-parallel-foundation

## Goal

Deliver Slice 1 from `docs/architecture/PUBLIC_WEBSITE_EXPANSION_PLAN.md`: promote the approved homepage to `/`, establish the responsive public shell, consume existing CMS and PublicGameData boundaries, and create module-local route/navigation conventions plus centrally reserved exact permission keys for parallel feature agents.

## Acceptance criteria

- [x] `/` uses the approved homepage visual foundation through a production controller and composed view model.
- [x] Character search remains available and uses the existing PublicGameData route.
- [x] World status and online count use only existing configured-channel and runtime snapshot boundaries.
- [x] Latest news uses the existing published-only CMS boundary.
- [x] Homepage blocks distinguish `AVAILABLE`, `EMPTY`, `STALE` and `UNAVAILABLE` without fabricated values.
- [x] Shared public header and footer are responsive, keyboard reachable and expose guest/authenticated states.
- [x] Header, footer and homepage expose only registered routes.
- [x] Module route and public-navigation contributions no longer require edits to the central route/bootstrap or shared layout files.
- [x] Planned exact permission keys are registered and persisted without role grants or wildcard authority.
- [x] Parallel path ownership is documented in `docs/contracts/PUBLIC_PORTAL_EXTENSION_CONTRACT.md` and this checkpoint.
- [ ] Focused tests, formatting, PHPStan, full required tests and exact-head CI pass.
- [ ] Desktop, tablet, mobile and keyboard browser acceptance pass on the exact head.

## Ownership

```yaml
owned_paths:
  - routes/web.php
  - routes/modules/public-portal.php
  - app/Http/Controllers/PublicPortal/PublicHomeController.php
  - app/PublicPortal/**
  - app/Cms/PublicNewsQuery.php
  - app/Admin/AdminPermission.php
  - database/migrations/2026_07_24_150100_reserve_public_module_permissions.php
  - resources/views/home.blade.php
  - resources/views/game/layout.blade.php
  - resources/views/game/partials/public-header.blade.php
  - resources/views/game/partials/public-footer.blade.php
  - resources/navigation/public/core.php
  - public/css/public-shell.css
  - public/css/home-production.css
  - tests/Feature/HomeTest.php
  - tests/Feature/HomePreviewTest.php
  - tests/Feature/PublicPortal/PublicPortalExtensionTest.php
  - tests/Feature/Admin/PublicModulePermissionReservationTest.php
  - scripts/acceptance/tests/responsive-critical.spec.mjs
  - scripts/acceptance/tests/accessibility-critical.spec.mjs
  - docs/contracts/PUBLIC_PORTAL_EXTENSION_CONTRACT.md
  - docs/agents/tasks/active/OTERYN-20260724-public-web-parallel-foundation.md
modules:
  - PublicPortal
  - CMS
  - PublicGameData
  - AdminRBAC
  - AcceptanceTesting
dependencies:
  - PR #142 merged
  - PR #143 merged
  - PR #139 approved homepage comparison implementation
blockers:
  - none
cross_repository_tasks:
  - none
```

## Parallel path ownership contract

Future feature agents own module-local paths and must not modify the shared foundation paths above during normal feature delivery:

| Feature | Route file | Navigation file | Feature implementation |
|---|---|---|---|
| Downloads | `routes/modules/downloads.php` | `resources/navigation/public/downloads.php` | `app/Downloads/**` |
| Events | `routes/modules/events.php` | `resources/navigation/public/events.php` | `app/Events/**` |
| Support | `routes/modules/support.php` | `resources/navigation/public/support.php` | `app/Support/**` |
| Wiki | `routes/modules/wiki.php` | `resources/navigation/public/wiki.php` | `app/Wiki/**` |
| Additional PublicGameData | `routes/modules/public-game-data-<feature>.php` | `resources/navigation/public/public-game-data-<feature>.php` | separately approved `app/PublicGameData/**` paths |

The following exact permission keys are reserved centrally with no role grants: `portal.access`, `portal.announcements.manage`, `portal.settings.manage`, `downloads.manage`, `events.manage`, `events.publish`, `support.content.manage`, `wiki.access`, `wiki.articles.manage`, `wiki.categories.manage`, `wiki.publish`.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T15:04:30Z
head: fcd8fd441f7624906626e28ebeb3f7e29b73caf0
branch: feat/OTERYN-20260724-public-web-parallel-foundation
pr: 146
status: validating
context_routes:
  - agent-governance
  - architecture
  - web-cms
  - public-game-data
  - admin-rbac
  - security
  - testing
owned_paths:
  - routes/web.php
  - routes/modules/public-portal.php
  - app/Http/Controllers/PublicPortal/PublicHomeController.php
  - app/PublicPortal/**
  - app/Cms/PublicNewsQuery.php
  - app/Admin/AdminPermission.php
  - database/migrations/2026_07_24_150100_reserve_public_module_permissions.php
  - resources/views/home.blade.php
  - resources/views/game/layout.blade.php
  - resources/views/game/partials/public-header.blade.php
  - resources/views/game/partials/public-footer.blade.php
  - resources/navigation/public/core.php
  - public/css/public-shell.css
  - public/css/home-production.css
  - tests/Feature/HomeTest.php
  - tests/Feature/HomePreviewTest.php
  - tests/Feature/PublicPortal/PublicPortalExtensionTest.php
  - tests/Feature/Admin/PublicModulePermissionReservationTest.php
  - scripts/acceptance/tests/responsive-critical.spec.mjs
  - scripts/acceptance/tests/accessibility-critical.spec.mjs
  - docs/contracts/PUBLIC_PORTAL_EXTENSION_CONTRACT.md
  - docs/agents/tasks/active/OTERYN-20260724-public-web-parallel-foundation.md
proven:
  - PR #142 and PR #143 are merged and both required plans exist on main.
  - No open PR other than this task owns the homepage, public layout, route registry, permission registry or public CSS paths.
  - PR #139 supplied the approved responsive homepage visual foundation and passed its exact-head gate set before merge.
  - Existing CMS and PublicGameData boundaries provide published-only news, configured channels and TTL-bounded runtime status/counts.
  - The reservation migration creates no role-permission grants.
derived:
  - Missing or expired runtime records cannot support an aggregate online count and are represented as STALE.
  - Runtime transport/malformed-data failure cannot support offline or zero claims and is represented as UNAVAILABLE.
  - Future modules can add route and navigation files without editing the central route bootstrap or shared public layout.
unknown:
  - exact-head focused/full test and browser acceptance result
  - exact-head required CI result
conflicts: []
first_failure:
  marker: none
  evidence: implementation complete; validation not yet inspected
rejected_hypotheses:
  - Add placeholder Downloads, Events, Support or Wiki links before their routes exist: rejected because the shell must expose only delivered routes.
  - Derive zero or offline from missing runtime data: rejected because PublicGameData failure semantics require explicit stale/unavailable states.
  - Grant reserved permissions to platform_admin automatically: rejected because future authority must be explicit and separately reviewed.
changed_paths:
  - routes/web.php
  - routes/modules/public-portal.php
  - app/Http/Controllers/PublicPortal/PublicHomeController.php
  - app/PublicPortal/HomePageQuery.php
  - app/PublicPortal/PublicContentState.php
  - app/PublicPortal/Navigation/PublicNavigationRegistry.php
  - app/PublicPortal/ViewModels/HomeNewsSummary.php
  - app/PublicPortal/ViewModels/HomePageViewModel.php
  - app/PublicPortal/ViewModels/HomeWorldChannel.php
  - app/PublicPortal/ViewModels/HomeWorldSummary.php
  - app/Cms/PublicNewsQuery.php
  - app/Admin/AdminPermission.php
  - database/migrations/2026_07_24_150100_reserve_public_module_permissions.php
  - resources/views/home.blade.php
  - resources/views/game/layout.blade.php
  - resources/views/game/partials/public-header.blade.php
  - resources/views/game/partials/public-footer.blade.php
  - resources/navigation/public/core.php
  - public/css/public-shell.css
  - public/css/home-production.css
  - tests/Feature/HomeTest.php
  - tests/Feature/HomePreviewTest.php
  - tests/Feature/PublicPortal/PublicPortalExtensionTest.php
  - tests/Feature/Admin/PublicModulePermissionReservationTest.php
  - scripts/acceptance/tests/responsive-critical.spec.mjs
  - scripts/acceptance/tests/accessibility-critical.spec.mjs
  - docs/contracts/PUBLIC_PORTAL_EXTENSION_CONTRACT.md
  - docs/agents/tasks/active/OTERYN-20260724-public-web-parallel-foundation.md
validation:
  - command: implementation review against mandatory architecture, security, test and visual UX contracts
    result: PASS
    evidence: bounded implementation uses existing CMS/PublicGameData reads, exact permission keys, route filtering and shared focus/responsive foundations
  - command: focused and full automated validation
    result: NOT_RUN
    evidence: awaiting exact-head CI execution and inspection
blockers:
  - none
next_action: inspect exact-head CI, fix first failures, then record final browser and gate evidence
```

## Notes

Writes remain limited to `blakinio/Oteryn-Platform`. Downloads, Events, Support, Wiki domain implementation, media upload, commerce, new Canary queries and cross-repository changes remain out of scope.
