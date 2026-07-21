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

- [x] Public header/brand presentation reads as an original Oteryn MMORPG portal rather than a generic application shell.
- [x] Homepage hero prioritizes Oteryn identity, one primary action, one secondary action and concise world context.
- [x] Character search remains prominent, keyboard accessible and functionally unchanged.
- [x] Online, Highscores, Servers and News discovery are presented as coherent world cards without inventing runtime/game claims.
- [x] Styling uses existing Oteryn tokens plus original CSS/SVG ornamentation only; no copied external game/OTS assets.
- [x] Layout remains usable without final commissioned artwork and does not create giant empty art-dependent regions.
- [x] Desktop/tablet/mobile layouts avoid document-level horizontal overflow.
- [x] Existing public/identity/admin component semantics and security boundaries remain unchanged.
- [x] Required repository gates and browser/visual evidence pass on the final product implementation head.

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
updated_at: 2026-07-21T16:24:00+02:00
head: 9b6df3740794e90bbdbc0a5fdf3c5124dccd0f75
branch: task/OTERYN-20260721-public-portal-visual-identity
pr: 95
status: ready
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
  - main baseline for the product implementation is 990ebdbbdbeb7c2c1671d715b49e4e17bea7785c.
  - docs/design/VISUAL_DIRECTION.md defines a modern dark-fantasy MMORPG portal and requires placeholder-safe layouts that remain usable without final artwork.
  - PR #95 implements the public visual direction entirely in presentation-layer files.
  - The implementation uses an original repository-owned SVG sigil and CSS-generated atmospheric/citadel fallback rather than copied external game/OTS assets or the generated full-page mockup PNG.
  - Existing character-search route/form semantics and the stable exact-name copy contract are preserved.
  - Guest hero primary action is Create account; authenticated hero primary action is Account Center; both expose Explore the world as the secondary action.
  - Online, Highscores, Servers and News are exposed as world-navigation cards without fabricated live counts, rates, events or version claims.
  - Product implementation SHA 9b6df3740794e90bbdbc0a5fdf3c5124dccd0f75 passed CI run 29837684523, Agent Governance run 29837684713, Phase 7 Production-Like Validation run 29837684248, Platform DB Outage Validation run 29837684969 and Acceptance E2E smoke run 29837684354.
  - Full validation-only PR #96 mirrored exact product SHA 9b6df3740794e90bbdbc0a5fdf3c5124dccd0f75 and was closed without merge after evidence collection.
  - Full Acceptance E2E and Visual UX run 29837886708 passed with 13 Playwright tests and 0 failures.
  - Full visual evidence artifact 8498278582 has digest sha256:9c3577cc1480f9f4eac5a8eccb011a356849a264eb3fe0fbeb96f99c241ef721 and contains 71 screenshots.
  - Full visual results report 0 status mismatches, 0 document-level horizontal-overflow surfaces, 0 unlabeled-control surfaces, 0 sampled low-contrast surfaces, 0 focus-not-observed surfaces and 0 raw technical-message surfaces.
  - Home desktop 1440x1000, tablet 768x1024 and mobile 390x844 screenshots were manually reviewed and are coherent; the focused Skip to content link visible in evidence is an intentional keyboard-test state.
  - Home visual inspection reports status 200, semantic H1 Oteryn Platform, hero H2 Enter the world of Oteryn, no horizontal overflow, no unlabeled controls, no low-contrast samples, observed keyboard focus and no Home console/page errors.
derived:
  - The Public Game Portal atmosphere/hero gap identified against VISUAL_DIRECTION.md is closed for the current placeholder-safe implementation without requiring commissioned artwork.
  - The generated concept PNG remains a direction reference only and is not a shipped application asset.
unknown:
  - required workflow status on the task-record-only checkpoint commit created after this product evidence
conflicts: []
first_failure:
  marker: stale presentation landmarks in deterministic tests
  evidence: initial implementation changed the exact-name help copy and homepage Oteryn Platform heading landmark; both were restored without reverting the new visual design before final product SHA 9b6df3740794e90bbdbc0a5fdf3c5124dccd0f75
rejected_hypotheses:
  - Reuse the generated homepage mockup PNG as a production background: rejected because it is a full UI concept image, not a reusable production art asset.
  - Copy an existing OTS/MMORPG theme for atmosphere: rejected by VISUAL_DIRECTION.md originality constraints.
  - Backend or public-data regression caused the first failures: rejected because CI failures were stale deterministic presentation-copy/heading expectations and all backend/security boundaries passed after restoring those landmarks.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-public-portal-visual-identity.md
  - resources/views/game/layout.blade.php
  - resources/views/home.blade.php
  - public/css/portal.css
  - public/images/oteryn-sigil.svg
validation:
  - command: CI
    result: PASS
    evidence: run 29837684523 on product SHA 9b6df3740794e90bbdbc0a5fdf3c5124dccd0f75
  - command: Agent Governance
    result: PASS
    evidence: run 29837684713 on product SHA 9b6df3740794e90bbdbc0a5fdf3c5124dccd0f75
  - command: Phase 7 Production-Like Validation
    result: PASS
    evidence: run 29837684248 on product SHA 9b6df3740794e90bbdbc0a5fdf3c5124dccd0f75
  - command: Platform DB Outage Validation
    result: PASS
    evidence: run 29837684969 on product SHA 9b6df3740794e90bbdbc0a5fdf3c5124dccd0f75
  - command: Acceptance E2E and Visual UX smoke
    result: PASS
    evidence: run 29837684354 on product SHA 9b6df3740794e90bbdbc0a5fdf3c5124dccd0f75
  - command: Full Acceptance E2E and Visual UX
    result: PASS
    evidence: run 29837886708; 13 tests, 0 failures; 71 visual screenshots; artifact 8498278582
blockers: []
next_action: Verify required checks on the task-record-only current PR #95 head, then squash-merge PR #95 if the live merge gate remains green.
```

## Notes

This task changes presentation only. It does not add unverified gameplay rates/events/version claims, alter authentication/account semantics, or treat final fantasy artwork as a prerequisite for production usability. Full evidence remains `STAGING_PROVEN`/visual acceptance evidence and does not imply `PRODUCTION_PROVEN`.
