---
task_id: OTERYN-20260724-wiki-architecture-plan
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/WIKI_IMPLEMENTATION_PLAN.md
search_first:
  - active Wiki or CMS tasks and open PRs
  - current CMS, RBAC, audit and migration conventions
optional_reads:
  - docs/architecture/SYSTEM_ARCHITECTURE.md
---

# OTERYN-20260724-wiki-architecture-plan

## Goal

Record a durable architecture and phased implementation plan for a first-party Oteryn Wiki, plus a standalone implementation prompt that a fresh agent can execute without relying on chat history.

## Acceptance criteria

- [x] A durable Wiki implementation plan exists under `docs/architecture/`.
- [x] The plan defines module boundaries, scope, non-goals, data model, RBAC, audit, search, media security, UX, delivery slices and completion criteria.
- [x] A standalone implementation-agent prompt exists under `docs/agents/prompts/`.
- [x] The prompt requires lean preflight, a dedicated implementation task/branch/PR and only the first bounded implementation slice.
- [x] No application code, migrations, routes, permissions or deployment behavior are changed by this planning task.
- [x] Draft PR is opened and the changed-file boundary is reviewed.

## Ownership

```yaml
owned_paths:
  - docs/architecture/WIKI_IMPLEMENTATION_PLAN.md
  - docs/agents/prompts/OTERYN-WIKI-IMPLEMENTATION-AGENT-PROMPT.md
  - docs/agents/tasks/active/OTERYN-20260724-wiki-architecture-plan.md
  - docs/agents/ACTIVE_WORK.md
modules:
  - Architecture
  - Agent governance
  - Future Wiki

dependencies:
  - existing CMS, Identity, Admin/RBAC and Audit architecture
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T16:15:00+02:00
head: c385dec4df3eafd34329f2d3c91546807ae24c58
branch: docs/OTERYN-20260724-wiki-architecture-plan
pr: 142
status: ready
context_routes:
  - agent-governance
  - architecture
  - web-cms
  - admin-rbac
  - database
  - security
  - testing
owned_paths:
  - docs/architecture/WIKI_IMPLEMENTATION_PLAN.md
  - docs/agents/prompts/OTERYN-WIKI-IMPLEMENTATION-AGENT-PROMPT.md
  - docs/agents/tasks/active/OTERYN-20260724-wiki-architecture-plan.md
  - docs/agents/ACTIVE_WORK.md
proven:
  - Oteryn Platform is the only repository authorized for writes in this task.
  - the current CMS supports news and managed pages with escaped plain-text rendering and no media upload
  - privileged routes use authentication, confirmed MFA and exact deny-by-default permissions
  - administrator CMS mutations use bounded audit records
  - no active Wiki task or Wiki branch was found before this task began
  - PR 142 contains only four documentation and agent-governance paths
  - CI, production-like validation, database-outage validation and game-auth concurrency checks passed on checkpoint head c385dec4df3eafd34329f2d3c91546807ae24c58
  - Agent Governance run 30097726167 failed only in active checkpoint validation because status reviewing is not allowed by governance contract version 1
  - the checkpoint status is now changed to the allowed ready state
 derived:
  - Wiki should reuse shared Identity, RBAC and Audit but own a dedicated content and revision model
  - the programme must be delivered as multiple small vertical slices rather than one large PR
unknown:
  - final maintained Markdown parser and sanitizer choice
  - deployed image-processing runtime and Wiki media storage topology
  - canonical editorial source language and translation freshness policy
conflicts: []
first_failure:
  marker: Agent Governance run 30097726167 checkpoint-validation job 89495816875
  evidence: Validate active task checkpoints failed because the task used unsupported status reviewing; the governance contract permits only investigating, implementing, validating, blocked or ready
rejected_hypotheses:
  - extending generic managed pages alone is sufficient for Wiki categories, revisions, bilingual slugs, search and safe media
  - a separate external CMS is required for the first Wiki release
  - the governance failure was an application or test-suite regression
changed_paths:
  - docs/architecture/WIKI_IMPLEMENTATION_PLAN.md
  - docs/agents/prompts/OTERYN-WIKI-IMPLEMENTATION-AGENT-PROMPT.md
  - docs/agents/tasks/active/OTERYN-20260724-wiki-architecture-plan.md
  - docs/agents/ACTIVE_WORK.md
validation:
  - command: repository documentation review against AGENTS.md, MODULE_CATALOG.md and routes/web.php
    result: PASS
    evidence: plan preserves the current modular monolith, exact RBAC/MFA model and explicit upload-security requirement
  - command: compare main...docs/OTERYN-20260724-wiki-architecture-plan
    result: PASS
    evidence: four documentation/governance paths changed; no application, route, migration, configuration or deployment file changed
  - command: CI and production-like required checks on c385dec4df3eafd34329f2d3c91546807ae24c58
    result: PASS
    evidence: CI, Phase 7 Production-Like Validation, Platform DB Outage Validation and Game Auth Ticket Concurrency completed successfully
  - command: Agent Governance run 30097726167
    result: FAIL
    evidence: checkpoint validation rejected unsupported status reviewing; this commit changes it to ready and requires a fresh exact-head run
  - command: application tests
    result: NOT_RUN
    evidence: documentation-only planning change; no application code or runtime behavior changed
blockers:
  - none
next_action: Verify the fresh Agent Governance run on the updated PR 142 head, then mark the PR ready and squash-merge it if every required check passes.
```

## Notes

The implementation prompt deliberately starts with architecture and persistence foundations only. Public Wiki activation, search, media uploads and navigation integration remain later separately reviewed slices.
