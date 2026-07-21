---
task_id: OTERYN-20260721-public-portal-brand-art-refinement
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/design/VISUAL_DIRECTION.md
  - docs/design/DESIGN_SYSTEM.md
  - docs/design/RESPONSIVE_STRATEGY.md
search_first:
  - open PRs and active tasks overlapping resources/views/** public/css/** public/images/**
  - resources/views/game/layout.blade.php
  - resources/views/home.blade.php
  - public/css/portal.css
optional_reads:
  - docs/acceptance/VISUAL_UX_ACCEPTANCE_MATRIX.md
---

# OTERYN-20260721-public-portal-brand-art-refinement

## Goal

Refine the merged Public Game Portal with a small original repository-owned Oteryn brand/art package: a reusable wordmark lockup, an atmospheric hero citadel illustration, a heraldic divider and distinct world-navigation marks. Preserve the placeholder-safe CSS fallback, accessibility, responsive behavior and all backend/auth/data semantics.

## Acceptance criteria

- [x] Add only original repository-owned SVG assets; copy no external OTS/MMORPG/game artwork or logos.
- [x] Replace the header's text-only brand treatment with an accessible Oteryn wordmark lockup while retaining the existing home link and accessible name.
- [x] Layer an original decorative hero illustration over the existing CSS fallback without making the layout dependent on artwork.
- [x] Replace generic letter world-card icons with four distinct original marks.
- [x] Keep decorative assets hidden from assistive technology and preserve semantic headings/actions.
- [x] Preserve exact-name character search behavior/copy and existing account CTA semantics.
- [ ] Preserve desktop/tablet/mobile usability and no document-level horizontal overflow through exact-head browser evidence on the latest main-derived candidate.
- [ ] Pass required CI, governance, production-like, DB outage and browser/visual validation on the final latest-main-derived head.

## Ownership

```yaml
owned_paths:
  - resources/views/game/layout.blade.php
  - resources/views/home.blade.php
  - public/css/brand-art.css
  - public/images/oteryn-wordmark.svg
  - public/images/oteryn-hero-citadel.svg
  - public/images/oteryn-heraldic-divider.svg
  - public/images/oteryn-mark-online.svg
  - public/images/oteryn-mark-highscores.svg
  - public/images/oteryn-mark-servers.svg
  - public/images/oteryn-mark-news.svg
  - docs/agents/tasks/active/OTERYN-20260721-public-portal-brand-art-refinement.md
  - docs/agents/tasks/archive/OTERYN-20260721-public-portal-brand-art-refinement.md
modules:
  - PublicGamePortal
  - WebPresentation
dependencies:
  - PR #95 public portal visual identity baseline
  - latest merged acceptance/resilience harness on main
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T18:47:00+02:00
head: f15d44af82c1b0e901d7b7e6729235acb7dedbe9
branch: task/OTERYN-20260721-public-portal-brand-art-refinement
pr: 103
status: validating
context_routes:
  - web-cms
  - testing
  - agent-governance
owned_paths:
  - resources/views/game/layout.blade.php
  - resources/views/home.blade.php
  - public/css/brand-art.css
  - public/images/oteryn-wordmark.svg
  - public/images/oteryn-hero-citadel.svg
  - public/images/oteryn-heraldic-divider.svg
  - public/images/oteryn-mark-online.svg
  - public/images/oteryn-mark-highscores.svg
  - public/images/oteryn-mark-servers.svg
  - public/images/oteryn-mark-news.svg
  - docs/agents/tasks/active/OTERYN-20260721-public-portal-brand-art-refinement.md
  - docs/agents/tasks/archive/OTERYN-20260721-public-portal-brand-art-refinement.md
proven:
  - current implementation branch was resynchronized to main cc63f1c4d4aac7f314c0b234b7bb17a864ed0972 before reapplying only task-owned UI and asset paths
  - main advancement from 18bd5b2c3b4496677cc58df41fd50c6387e9e6f8 to cc63f1c4d4aac7f314c0b234b7bb17a864ed0972 added acceptance public-dependency recovery/resilience coverage and changed the acceptance workflow but did not overlap task-owned product paths
  - validation-only PR 108 based on the previous harness was closed without merge before collecting final full evidence because it was superseded by the latest-main synchronization
  - the wordmark lockup hero citadel heraldic divider and four world marks are original repository-owned SVG source created for Oteryn and copy no external game or OTS assets
  - the public header retains aria-label Oteryn Platform home while the decorative wordmark image is aria-hidden
  - the hero illustration is decorative and layered over the existing portal hero CSS fallback so core layout remains usable without the art asset
  - exact-name character search route form fields and stable help copy remain unchanged
  - guest Create account authenticated Account and Explore the world CTA semantics remain unchanged
  - no backend authentication authorization Canary provisioning game-login database or production behavior was modified
derived:
  - the latest-main-derived implementation deepens Oteryn-specific brand identity while preserving current resilience/acceptance harness changes from main
unknown:
  - final exact-head CI governance Phase 7 DB outage critical browser and full visual accessibility results after latest-main synchronization
conflicts: []
first_failure:
  marker: Earlier validation failures were governance-contract labels, stale-base Phase 7 correlation support, and one non-reproduced WebKit navigation timeout on the prior main-derived head.
  evidence: governance labels were corrected; stale-base branch was resynchronized; same-SHA critical rerun later passed Chromium Firefox WebKit and responsive profiles before main advanced again with new resilience harness changes.
rejected_hypotheses:
  - The earlier Phase 7 failure was a brand art runtime defect: rejected because it was caused by a branch predating merged request-log correlation support.
  - The one WebKit timeout proves a deterministic UI incompatibility: rejected because the same exact SHA rerun passed the portability profile without code changes.
  - Old-harness full visual evidence is sufficient after main changes acceptance/resilience coverage: rejected; final evidence must be rerun from the latest-main-derived candidate.
  - Replace the placeholder-safe hero with a raster-only full-page mockup: rejected because VISUAL_DIRECTION.md requires production usability without final artwork.
  - Copy an existing OTS or MMORPG logo/art package: rejected by project originality constraints.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-public-portal-brand-art-refinement.md
  - resources/views/game/layout.blade.php
  - resources/views/home.blade.php
  - public/css/brand-art.css
  - public/images/oteryn-wordmark.svg
  - public/images/oteryn-hero-citadel.svg
  - public/images/oteryn-heraldic-divider.svg
  - public/images/oteryn-mark-online.svg
  - public/images/oteryn-mark-highscores.svg
  - public/images/oteryn-mark-servers.svg
  - public/images/oteryn-mark-news.svg
validation:
  - command: latest-main overlap and synchronization preflight
    result: PASS
    evidence: task branch reset to cc63f1c4d4aac7f314c0b234b7bb17a864ed0972 and only 11 task-owned UI asset checkpoint paths reapplied
  - command: prior critical browser evidence
    result: PASS
    evidence: prior exact SHA 9df8f0a1ecc0d8010170991e4f1a042e6a4accf0 rerun passed 7 Chromium smoke 12 portability Chromium Firefox WebKit and 9 responsive tests with zero failures; superseded for final merge evidence by newer main harness
  - command: latest-main-derived exact-head required validation
    result: NOT_RUN
    evidence: fresh checks will start after this restored checkpoint commit
blockers: []
next_action: Run all required checks and the latest critical browser/resilience profile on the latest-main-derived PR #103 head, then collect a full exploratory Visual Accessibility artifact from a validation-only branch before merge.
```
