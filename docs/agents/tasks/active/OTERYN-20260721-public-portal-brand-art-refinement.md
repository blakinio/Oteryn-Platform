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
- [ ] Preserve desktop/tablet/mobile usability and no document-level horizontal overflow through exact-head browser evidence.
- [ ] Pass required CI, governance, production-like, DB outage and browser/visual validation on the final head.

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
  - PR #102 observability correlation is inherited from current main and must remain intact
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T18:32:00+02:00
head: 1eb1388d77b04fd070f566b6586f1044f65ed60e
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
  - current synchronized baseline is main 18bd5b2c3b4496677cc58df41fd50c6387e9e6f8 including merged PR 102 observability correlation and its archive PR 104
  - PR 103 is the dedicated implementation PR for this bounded presentation-only refinement
  - the new wordmark lockup hero citadel heraldic divider and four world marks are original repository-owned SVG source created for Oteryn and copy no external game or OTS assets
  - the public header retains aria-label Oteryn Platform home while the decorative wordmark image is aria-hidden
  - the hero illustration is decorative and layered over the existing portal hero CSS fallback so core layout remains usable without the art asset
  - exact-name character search route form fields and stable help copy are unchanged
  - guest Create account authenticated Account and Explore the world CTA semantics are unchanged
  - generic letter world-card icons were replaced only at the presentation layer with decorative aria-hidden SVG marks
  - branch was explicitly resynchronized to current main after Phase 7 revealed the original branch predated PR 102 request-log correlation support
  - no backend authentication authorization Canary provisioning game-login database or production behavior was modified
derived:
  - the implementation deepens Oteryn-specific brand identity without making public navigation or functional content dependent on commissioned raster artwork
unknown:
  - final exact-head CI Phase 7 Platform DB Outage and browser visual accessibility results after current-main resynchronization
conflicts: []
first_failure:
  marker: Initial PR 103 validation was not mergeable because governance used unsupported result labels and the task branch predated merged observability correlation requirements.
  evidence: Agent Governance runs 29846679996 and 29846863655 failed checkpoint validation; after governance repair Phase 7 run 29847035960 failed at running HTTP correlation because the exact tested branch lacked scripts/phase7/assert-request-log-correlation.php from merged PR 102.
rejected_hypotheses:
  - The Phase 7 failure is a brand art runtime defect: rejected because clean deployment privileges Redis SMTP configuration and critical regression steps passed before the newly required correlation step, and the branch lacked the PR 102 correlation script.
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
  - command: live GitHub overlap and current-main synchronization preflight
    result: PASS
    evidence: PR 102 and PR 104 are merged; current main 18bd5b2c3b4496677cc58df41fd50c6387e9e6f8 was adopted before reapplying only task-owned UI and asset paths
  - command: initial Agent Governance and Phase 7 diagnostics
    result: FAIL
    evidence: governance contract labels were repaired and stale-base Phase 7 correlation failure was traced to absence of the merged PR 102 script on the old exact SHA
  - command: synchronized exact-head required validation
    result: NOT_RUN
    evidence: fresh CI governance Phase 7 DB outage and Acceptance E2E Visual UX runs must complete after this synchronized task checkpoint
blockers: []
next_action: Validate the synchronized PR #103 exact head through all required gates, inspect browser visual artifacts, and fix only evidence-backed regressions before merge.
```
