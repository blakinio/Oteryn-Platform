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
- [x] Preserve desktop/tablet/mobile usability and no document-level horizontal overflow through exact-head browser evidence on the latest main-derived candidate.
- [x] Pass required CI, governance, production-like, DB outage and browser/visual validation on the final latest-main-derived product head.

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
updated_at: 2026-07-21T19:04:00+02:00
head: 5ecaa130977c771949fd0b26f1ffb3507b4a03f7
branch: task/OTERYN-20260721-public-portal-brand-art-refinement
pr: 103
status: ready
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
  - final product implementation head 5ecaa130977c771949fd0b26f1ffb3507b4a03f7 is derived from main cc63f1c4d4aac7f314c0b234b7bb17a864ed0972 with only 11 task-owned UI asset and checkpoint paths changed
  - wordmark lockup hero citadel heraldic divider and four world marks are original repository-owned SVG source created for Oteryn and copy no external game or OTS assets
  - header home link retains accessible name Oteryn Platform home while decorative wordmark art is hidden from assistive technology
  - hero artwork is decorative and layered over the existing CSS fallback so the portal remains usable without the SVG illustration
  - exact-name character search stable help copy account CTA semantics and backend auth Canary provisioning game-login database and production behavior remain unchanged
  - final product head passed Agent Governance run 29850227912 CI run 29850227766 Platform DB Outage Validation run 29850228066 and Phase 7 Production-Like Validation run 29850228074
  - final product head Acceptance E2E and Visual UX critical run 29850227955 passed with 7 Chromium smoke tests 12 Chromium Firefox WebKit portability tests 9 desktop tablet mobile responsive tests and 2 public dependency resilience tests all with zero failures and bounded profile retries 0
  - critical artifact 8503086709 digest sha256:459e85ea2fb7ffa1a79c647c0b330c183df0e3fc83f8b9d677f5c30f470c7970 records exact tested SHA 5ecaa130977c771949fd0b26f1ffb3507b4a03f7 and AUTOMATED_E2E_CRITICAL_PASS
  - validation-only PR 109 used validation head 28db85efcd23cf4f6f317fff8ab022ccf521f3f1 which equals the final product candidate plus only .github/workflows/brand-art-full-visual-validation.yml and was closed without merge after evidence collection
  - Brand Art Full Visual Validation run 29850556698 passed the full Chromium baseline with 15 tests 0 failures 0 skipped 0 errors and the exploratory Visual Accessibility collector
  - full visual artifact 8503333259 digest sha256:83722e9d751f1059c33ce5d4367eeccc945d25cfca2d772ba77013d32fa023a9 contains 71 screenshots
  - visual results report 0 status mismatches 0 horizontal overflow surfaces 0 unlabeled control surfaces 0 sampled low contrast surfaces 0 focus-not-observed surfaces and 0 raw technical message surfaces
  - six browser console error surfaces are only the intentional 403 404 and 503 response pages and all have zero page errors
  - home desktop 1440x1000 tablet 820x1180 and mobile 390x844 screenshots were manually reviewed and are coherent; visible focused Skip to content is an intentional keyboard-test state
  - validation-only PR 108 was closed without merge as superseded before evidence collection after main advanced its acceptance resilience harness
derived:
  - Oteryn now has a stronger repository-owned visual identity package while preserving placeholder-safe operation and the current functional security and resilience boundaries
  - the earlier single WebKit admin navigation timeout was non-reproduced same-SHA infrastructure flake because the subsequent portability rerun passed without code changes and the final latest-main critical run passed all 12 portability tests with zero retries
unknown: []
conflicts: []
first_failure:
  marker: Earlier non-product validation issues were governance-contract labels stale-base Phase 7 correlation support and one non-reproduced WebKit admin navigation timeout.
  evidence: governance labels were corrected branches were resynchronized to current main and final latest-main exact-head required plus critical browser resilience validation is fully green.
rejected_hypotheses:
  - The earlier Phase 7 failure was a brand art runtime defect: rejected because it was caused by a branch predating merged request-log correlation support.
  - The one WebKit timeout proves a deterministic UI incompatibility: rejected by same-SHA rerun and final latest-main 12-test portability PASS without retries.
  - Old-harness evidence is sufficient after acceptance harness changes: rejected; final critical and full visual evidence was recollected from the latest-main-derived candidate.
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
  - command: Agent Governance
    result: PASS
    evidence: run 29850227912 on final product head 5ecaa130977c771949fd0b26f1ffb3507b4a03f7
  - command: CI
    result: PASS
    evidence: run 29850227766 on final product head 5ecaa130977c771949fd0b26f1ffb3507b4a03f7
  - command: Platform DB Outage Validation
    result: PASS
    evidence: run 29850228066 on final product head 5ecaa130977c771949fd0b26f1ffb3507b4a03f7
  - command: Phase 7 Production-Like Validation
    result: PASS
    evidence: run 29850228074 on final product head 5ecaa130977c771949fd0b26f1ffb3507b4a03f7
  - command: Acceptance E2E critical browser responsive resilience
    result: PASS
    evidence: run 29850227955; 7 smoke 12 portability 9 responsive 2 resilience tests all zero failures and zero bounded retries
  - command: Full Chromium and exploratory Visual Accessibility validation-only evidence
    result: PASS
    evidence: run 29850556698 on product-equivalent validation head 28db85efcd23cf4f6f317fff8ab022ccf521f3f1; 15 tests zero failures; 71 screenshots; artifact 8503333259
blockers: []
next_action: Verify required checks on the task-record-only current PR #103 head, then mark PR #103 ready for review and squash-merge if the live merge gate remains green.
```
