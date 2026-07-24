---
task_id: OTERYN-20260724-public-website-expansion-plan
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/PUBLIC_WEBSITE_EXPANSION_PLAN.md
search_first:
  - active public-web, homepage, CMS, Wiki or PublicGameData tasks and open PRs
  - current public routes, homepage templates and visual acceptance records
optional_reads:
  - docs/architecture/WIKI_IMPLEMENTATION_PLAN.md
  - docs/architecture/SYSTEM_ARCHITECTURE.md
---

# OTERYN-20260724-public-website-expansion-plan

## Goal

Record a durable architecture and phased implementation plan for the complete Oteryn public website expansion, not only Wiki, plus a standalone prompt that starts the first bounded homepage implementation slice without relying on chat history.

## Acceptance criteria

- [x] A durable full public-website plan exists under `docs/architecture/`.
- [x] The plan covers homepage, public shell, downloads, server information, guides, announcements, events, community, support, legal documents, game statistics, Wiki integration, localization, media, SEO, accessibility and deferred commerce.
- [x] The plan separates current reusable Platform capabilities from new modules and later optional features.
- [x] The plan defines route ownership, persistence boundaries, permissions, security constraints, UX states, phased delivery and programme completion criteria.
- [x] A standalone implementation-agent prompt exists under `docs/agents/prompts/`.
- [x] The prompt starts only the production homepage/public-shell slice and explicitly prevents bundling the whole programme into one PR.
- [x] The planning task changes no application code, routes, migrations, permissions or deployment behavior.
- [x] Draft PR is opened and the effective changed-file boundary is reviewed.

## Ownership

```yaml
owned_paths:
  - docs/architecture/PUBLIC_WEBSITE_EXPANSION_PLAN.md
  - docs/agents/prompts/OTERYN-PUBLIC-WEBSITE-IMPLEMENTATION-AGENT-PROMPT.md
  - docs/agents/tasks/active/OTERYN-20260724-public-website-expansion-plan.md
modules:
  - Architecture
  - Agent governance
  - PublicPortal
  - CMS
  - Downloads
  - Events
  - Community and Support
  - Future Wiki integration
dependencies:
  - current public website, CMS, PublicGameData, Identity, Admin/RBAC and Audit architecture
  - dedicated Wiki plan remains a separate workstream
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T16:16:00+02:00
head: 8c854f6b1e0f4a1a874e90b311c1c9e2e1a0c239
branch: docs/OTERYN-20260724-public-website-expansion-plan
pr: 143
status: ready
context_routes:
  - agent-governance
  - architecture
  - web-cms
  - public-game-data
  - admin-rbac
  - security
  - testing
owned_paths:
  - docs/architecture/PUBLIC_WEBSITE_EXPANSION_PLAN.md
  - docs/agents/prompts/OTERYN-PUBLIC-WEBSITE-IMPLEMENTATION-AGENT-PROMPT.md
  - docs/agents/tasks/active/OTERYN-20260724-public-website-expansion-plan.md
proven:
  - Oteryn Platform is the only repository authorized for writes in this task
  - the current application already delivers public Home, News, Online, Highscores, Servers, character search/profile and guild detail surfaces
  - existing CMS, PublicGameData, Identity, Admin/RBAC and Audit provide reusable boundaries for the wider portal
  - the separate Wiki planning PR covers only the Wiki workstream and does not cover the complete website expansion
  - PR 143 is open as a documentation-only draft with exactly the three owned planning files in the effective diff
  - no application code or runtime behavior is changed by this task
  - CI, production-like validation, database-outage validation and game-auth concurrency checks passed on checkpoint head 8c854f6b1e0f4a1a874e90b311c1c9e2e1a0c239
  - Agent Governance run 30098513823 failed only in active checkpoint validation because status reviewing is not allowed by governance contract version 1
  - the checkpoint status is now changed to the allowed ready state
derived:
  - the complete public website requires a programme plan above the dedicated Wiki plan
  - the first implementation slice should activate the approved homepage and shared public shell using existing data boundaries before new persistence-heavy modules are added
  - Downloads and Events require dedicated validated models rather than arbitrary managed-page text
unknown:
  - final approved content and legal wording
  - exact client artifact hosting/signing topology
  - whether latest deaths and kill-statistics semantics are currently available through verified Canary read contracts
  - final product decision for optional polls, public banishments and boosted creature/boss
conflicts: []
first_failure:
  marker: accidental placeholder commit
  evidence: a one-line placeholder path was created by an incorrect tool call and immediately removed in the following commit; the effective PR diff contains only the three intended planning files
rejected_hypotheses:
  - Wiki alone represents the complete planned public-site expansion
  - all RubinOT-visible capabilities should be copied or delivered in one PR
  - unavailable runtime data may be shown as zero or offline
  - the governance failure was an application or test-suite regression
changed_paths:
  - docs/architecture/PUBLIC_WEBSITE_EXPANSION_PLAN.md
  - docs/agents/prompts/OTERYN-PUBLIC-WEBSITE-IMPLEMENTATION-AGENT-PROMPT.md
  - docs/agents/tasks/active/OTERYN-20260724-public-website-expansion-plan.md
validation:
  - command: architecture review against current routes, module catalog and existing Wiki plan
    result: PASS
    evidence: the plan preserves the Laravel modular monolith and reuses CMS, PublicGameData, Identity, exact RBAC/MFA and Audit boundaries
  - command: compare branch against main
    result: PASS
    evidence: effective changed-file list is limited to the three owned planning paths
  - command: CI and production-like required checks on 8c854f6b1e0f4a1a874e90b311c1c9e2e1a0c239
    result: PASS
    evidence: CI, Phase 7 Production-Like Validation, Platform DB Outage Validation and Game Auth Ticket Concurrency completed successfully
  - command: Agent Governance run 30098513823
    result: FAIL
    evidence: checkpoint validation rejected unsupported status reviewing; this commit changes it to ready and requires a fresh exact-head run
  - command: application tests
    result: NOT_RUN
    evidence: documentation-only planning change; no application code or runtime behavior changed
blockers:
  - none
next_action: Verify the fresh Agent Governance run on the updated PR 143 head, then mark the PR ready and squash-merge it if every required check passes.
```

## Notes

This task intentionally does not update `docs/agents/ACTIVE_WORK.md` because the separate Wiki planning PR currently owns that shared index. The live task record and PR are authoritative for this non-overlapping planning work.
