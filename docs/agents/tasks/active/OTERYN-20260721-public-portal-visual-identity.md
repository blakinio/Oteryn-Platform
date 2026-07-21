---
task_id: OTERYN-20260721-public-portal-visual-identity
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/design/VISUAL_DIRECTION.md
  - docs/design/DESIGN_SYSTEM.md
  - docs/design/RESPONSIVE_STRATEGY.md
  - docs/design/UI_ARCHITECTURE.md
search_first:
  - open PRs and active tasks overlapping resources/views/** or public/css/**
  - resources/views/game/layout.blade.php
  - resources/views/home.blade.php
  - public/css/app.css
optional_reads:
  - docs/acceptance/VISUAL_UX_ACCEPTANCE_MATRIX.md
  - tests/Feature/HomeTest.php
---

# OTERYN-20260721-public-portal-visual-identity

## Goal

Implement the public homepage visual north-star from `VISUAL_DIRECTION.md` as an original Oteryn-owned presentation layer: a stronger dark-fantasy world identity, atmospheric but placeholder-safe hero, state-aware account CTAs, prominent character search, and world-navigation cards, without changing backend/auth/data semantics.

## Acceptance criteria

- [ ] Public header/brand presentation reads as an original Oteryn MMORPG portal rather than a generic application shell.
- [ ] Homepage hero prioritizes Oteryn identity, one primary action, one secondary action and concise world context.
- [ ] Character search remains prominent, keyboard accessible and functionally unchanged.
- [ ] Online, Highscores, Servers and News discovery are presented as coherent world cards without inventing runtime/game claims.
- [ ] Styling uses existing Oteryn tokens plus original CSS/SVG ornamentation only; no copied external game/OTS assets.
- [ ] Layout remains usable without final commissioned artwork and does not create giant empty art-dependent regions.
- [ ] Desktop/tablet/mobile layouts avoid document-level horizontal overflow.
- [ ] Existing public/identity/admin component semantics and security boundaries remain unchanged.
- [ ] Required repository gates and browser/visual evidence pass on the final implementation head.

## Ownership

```yaml
owned_paths:
  - resources/views/game/layout.blade.php
  - resources/views/home.blade.php
  - public/css/portal.css
  - public/images/oteryn-sigil.svg
  - docs/agents/tasks/active/OTERYN-20260721-public-portal-visual-identity.md
  - docs/agents/tasks/archive/OTERYN-20260721-public-portal-visual-identity.md
modules:
  - PublicGamePortal
  - WebPresentation
dependencies:
  - docs/design/VISUAL_DIRECTION.md
  - PR #77 delivered-surface UI baseline
  - PR #86 Account Overview navigation
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T16:12:00+02:00
head: 6faf213b2f1c98348ec4f3558f3f34677d7d74f7
branch: task/OTERYN-20260721-public-portal-visual-identity
pr: 95
status: validating
context_routes:
  - web-cms
  - testing
  - agent-governance
owned_paths:
  - resources/views/game/layout.blade.php
  - resources/views/home.blade.php
  - public/css/portal.css
  - public/images/oteryn-sigil.svg
  - docs/agents/tasks/active/OTERYN-20260721-public-portal-visual-identity.md
  - docs/agents/tasks/archive/OTERYN-20260721-public-portal-visual-identity.md
proven:
  - main baseline is 990ebdbbdbeb7c2c1671d715b49e4e17bea7785c.
  - docs/design/VISUAL_DIRECTION.md defines a modern dark-fantasy MMORPG portal and explicitly requires placeholder-safe layouts that work without final artwork.
  - Open PR #94 currently changes only testing/governance documentation and does not overlap this task's owned product paths.
  - PR #95 implements the public visual direction entirely in presentation-layer files.
  - The implementation uses an original repository-owned SVG sigil and CSS-generated atmospheric/citadel fallback rather than copied external game/OTS assets or the generated full-page mockup PNG.
  - Existing character-search route/form semantics are preserved.
  - Guest hero primary action is Create account; authenticated hero primary action is Account Center; both expose Explore the world as the secondary action.
  - Online, Highscores, Servers and News are exposed as world-navigation cards without fabricated live counts, rates, events or version claims.
derived:
  - The current implementation materially closes the previously identified Public Game Portal atmosphere/hero gap while preserving placeholder-safe operation without commissioned artwork.
unknown:
  - final current-head browser/visual validation result
  - final current-head CI and production-like gate results
conflicts: []
first_failure:
  marker: none
  evidence: current-head workflows are still running
rejected_hypotheses:
  - Reuse the generated homepage mockup PNG as a production background: rejected because it is a full UI concept image, not a reusable production art asset.
  - Copy an existing OTS/MMORPG theme for atmosphere: rejected by VISUAL_DIRECTION.md originality constraints.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-public-portal-visual-identity.md
  - resources/views/game/layout.blade.php
  - resources/views/home.blade.php
  - public/css/portal.css
  - public/images/oteryn-sigil.svg
validation:
  - command: live GitHub preflight
    result: PASS
    evidence: PR #94 is docs-only relative to this task's product paths
  - command: PR #95 current-head required workflows
    result: NOT_RUN
    evidence: workflows on 6faf213b2f1c98348ec4f3558f3f34677d7d74f7 are still running
blockers: []
next_action: Wait for PR #95 current-head workflows to complete, inspect any first failure, then run or collect full browser/visual evidence for the final implementation head.
```

## Notes

This task changes presentation only. It must not add unverified gameplay rates/events/version claims, alter authentication/account semantics, or treat final fantasy artwork as a prerequisite for production usability.
