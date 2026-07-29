---
task_id: OTERYN-20260729-character-profile-preferences
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/PROJECT_STATE.md
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/contracts/CHARACTER_PROFILE_PREFERENCES_CONTRACT.md
  - docs/testing/PRODUCT_COMPLETENESS_BENCHMARK.md
search_first:
  - PR #308 exact-final-head evidence and merge result
  - Issue #307 closure and parent Issue #277 state
optional_reads:
  - docs/operations/CHARACTER_PROFILE_PREFERENCES.md
  - docs/agents/tasks/archive/OTERYN-20260729-community-data-completeness.md
---

# OTERYN-20260729-character-profile-preferences

## Goal

Deliver Issue #307, the Platform-owned character-profile slice of parent Issue #277: owner-editable public comments, per-character field visibility and an optional main-character preference, with no Canary mutation.

## Acceptance criteria

- [x] Current account-level privacy, public-profile assembly, Account Center inventory and Canary ownership boundaries were inventoried before implementation.
- [x] Platform persistence stores one preference row per identity/player with bounded public comment, explicit field visibility and transactional main-character uniqueness.
- [x] Every owner mutation verifies a ready immutable binding and current active Canary character ownership through the read-only connection before writing Platform state.
- [x] Account-level association/status privacy remains an upper bound; per-character settings may only narrow disclosure.
- [x] Public profile comment, main badge, guild, house, skills, deaths, kills, status and related-character sections follow effective visibility without leaking Platform/Canary identifiers.
- [x] Related-character lists exclude siblings that explicitly hide account association.
- [x] Account Center exposes EN/PL owner management, validation, success, unavailable and stale-ownership states with desktop/tablet/mobile behavior.
- [x] Main-character replacement is deterministic, transactional and leaves at most one selected character per identity.
- [x] Audit events are emitted without storing comment text or private values in audit metadata.
- [x] Rename, delete, restore, transfer, whole-profile hiding and achievements remain explicitly outside this slice.
- [x] Unit/feature, real-MariaDB ownership/concurrency and zero-retry browser evidence passed on the exact final head.
- [x] Product, route, architecture, security and operations ledgers were updated after evidence existed.
- [x] PR #308 merged as `86847d0068e470274b6c3ee5523fe41cbb9663af`; Issue #307 closed and parent Issue #277 remained open.
- [x] Task was archived in this separate documentation PR.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/archive/OTERYN-20260729-character-profile-preferences.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
modules:
  - CharacterProfiles
  - Identity
  - Accounts
  - PublicGameData
  - Audit
  - Testing
dependencies:
  - PR #308
  - Issue #307
  - open parent Issue #277
blockers:
  - none
cross_repository_tasks:
  - none; Canary remained read-only and no external write was authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-29T21:00:00Z
head: 3797a094cfa522f5147d624786f49fee5027c77b
branch: feat/OTERYN-20260729-character-profile-preferences
pr: 308
status: completed
context_routes:
  - agent-governance
  - architecture
  - auth-identity
  - canary-integration
  - database
  - security
  - web-cms
  - testing
owned_paths:
  - app/CharacterProfiles/**
  - app/Http/Controllers/CharacterProfiles/**
  - app/Http/Requests/CharacterProfiles/**
  - app/Http/Controllers/Accounts/AccountOverviewController.php
  - app/PublicGameData/PublicCharacterProfileService.php
  - database/migrations/2026_07_29_165500_create_character_profile_preferences.php
  - routes/modules/character-profile-preferences.php
  - resources/views/identity/account/**
  - resources/views/game/character.blade.php
  - lang/{en,pl}/character_profiles.php
  - tests/Feature/CharacterProfiles/**
  - scripts/acceptance/**character-profile**
  - scripts/acceptance/tests/community-data-acceptance.spec.mjs
  - scripts/acceptance/coverage/surfaces/character-profile-preferences.json
  - .github/workflows/community-data-acceptance.yml
  - docs/contracts/CHARACTER_PROFILE_PREFERENCES_CONTRACT.md
  - docs/operations/CHARACTER_PROFILE_PREFERENCES.md
  - docs/architecture/{MODULE_CATALOG,DATA_OWNERSHIP,SECURITY_ARCHITECTURE}.md
  - docs/testing/{PRODUCT_COMPLETENESS_BENCHMARK.md,product-completeness-benchmark.json}
  - docs/agents/{PROJECT_STATE,ACTIVE_WORK}.md
  - docs/agents/tasks/archive/OTERYN-20260729-character-profile-preferences.md
proven:
  - Platform owns the additive character_profile_preferences table, bounded escaped owner comment, per-character visibility and optional main-character preference; no Canary row was mutated.
  - Every management write re-resolves the ready immutable binding and current active Canary ownership through the read-only connection; stored preferences are never ownership proof.
  - Account-level association and status remain disclosure upper bounds, and hidden sibling preferences are removed from related-character presentation.
  - Main-character replacement locks the Identity row and two concurrent real-MariaDB writers leave exactly one main preference.
  - Exact final head 3797a094cfa522f5147d624786f49fee5027c77b passed CI 30490007511, Governance 30490007484, Portal 30490007458, Community Data 30490007443, Phase 7 30490007483, DB Outage 30490007507, Edge 30490007432, Game Auth 30490007493, Visual UX 30490007509, Synology preflight 30490007537 and image build 30490007474.
  - Community Data passed focused authorization/privacy/unavailable tests, the real-MariaDB main-character race and zero-retry Chromium desktop/tablet/mobile EN/PL lifecycle.
  - Portal passed strict route/product ledgers at 23 implemented, 3 partial, 14 missing and 3 not applicable, plus the complete account lifecycle.
  - PR #308 squash-merged as 86847d0068e470274b6c3ee5523fe41cbb9663af and automatically closed Issue #307 as completed.
  - Parent Issue #277 remained open for rename, deletion/restore, controlled world/channel transfer and authoritative achievement selection.
derived:
  - The Platform-owned profile-preference lifecycle is independently complete without authorizing the remaining Canary mutation lifecycles.
  - Repository and staging-like evidence do not establish production deployment or PRODUCTION_PROVEN status.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: All exact-final-head workflows passed and the merge used expected-head protection.
rejected_hypotheses:
  - Store owner comments in Canary players.comment.
  - Reuse a generic Canary UPDATE principal.
  - Treat a stored preference row as ownership proof.
  - Allow character-level opt-in to override account-level privacy.
  - Close parent Issue #277 after only this Platform-owned slice.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260729-character-profile-preferences.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
validation:
  - command: Required exact-final-head workflow suite
    result: PASS
    evidence: All 11 workflow runs listed under proven completed successfully at 3797a094cfa522f5147d624786f49fee5027c77b.
  - command: PR merge with expected-head protection
    result: PASS
    evidence: PR #308 squash-merged as 86847d0068e470274b6c3ee5523fe41cbb9663af.
  - command: Issue lifecycle verification
    result: PASS
    evidence: Issue #307 is closed as completed and parent Issue #277 remains open.
blockers:
  - none
next_action: Keep Issue #277 open. Any rename, deletion, restore, world/channel transfer or achievement implementation requires a new bounded task and explicit operation-specific ownership contract; Canary writes remain unauthorized by this archive.
```

## Boundaries

This archived task wrote only Platform-owned preference state. It did not rename, delete, restore, transfer or otherwise mutate a Canary character and did not claim production deployment.
