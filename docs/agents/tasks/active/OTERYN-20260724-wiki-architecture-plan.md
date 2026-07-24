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
updated_at: 2026-07-24T13:40:00Z
head: dea5583fc98a7d877599e2920ae6b8c6eecb0890
branch: docs/OTERYN-20260724-wiki-architecture-plan
pr: 142
status: reviewing
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
derived:
  - Wiki should reuse shared Identity, RBAC and Audit but own a dedicated content and revision model
  - the programme must be delivered as multiple small vertical slices rather than one large PR
unknown:
  - final maintained Markdown parser and sanitizer choice
  - deployed image-processing runtime and Wiki media storage topology
  - canonical editorial source language and translation freshness policy
conflicts: []
first_failure:
  marker: duplicate branch creation request
  evidence: the second create request returned Reference already exists after the branch had already been created successfully; no repository state was damaged
rejected_hypotheses:
  - extending generic managed pages alone is sufficient for Wiki categories, revisions, bilingual slugs, search and safe media
  - a separate external CMS is required for the first Wiki release
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
  - command: application tests
    result: NOT_RUN
    evidence: documentation-only planning change; no application code or runtime behavior changed
blockers:
  - none
next_action: Review PR 142 and merge the documentation plan when governance checks pass, then start the implementation prompt in a new dedicated task branch.
```

## Notes

The implementation prompt deliberately starts with architecture and persistence foundations only. Public Wiki activation, search, media uploads and navigation integration remain later separately reviewed slices.
