---
task_id: OTERYN-20260724-public-game-statistics
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/PUBLIC_WEBSITE_EXPANSION_PLAN.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/TEST_STRATEGY.md
search_first:
  - active PublicGameData tasks and open PRs
  - existing Canary query adapters, controllers, routes, pagination and dependency-failure tests
  - current blakinio/canary guild schema evidence
optional_reads: []
---

# OTERYN-20260724-public-game-statistics

## Goal

Deliver the smallest complete read-only public-game-statistics capability supported by verified current Canary evidence, preferring a guild index over latest deaths or kill statistics.

## Acceptance criteria

- [ ] Exact Canary source tables and semantics are recorded from a pinned current Canary head.
- [ ] The public field allowlist excludes private, disciplinary and unproven fields.
- [ ] Ordering and pagination are deterministic and bounded.
- [ ] Empty and dependency-unavailable behavior are distinct.
- [ ] The implementation uses the database-enforced read-only Canary boundary without N+1 or unbounded queries.
- [ ] Focused feature/integration tests and exact-head CI pass.

## Ownership

```yaml
owned_paths:
  - app/Http/Controllers/PublicGameData/GuildIndexController.php
  - app/PublicGameData/GuildIndexQuery.php
  - routes/modules/public-game-statistics.php
  - resources/views/game/guilds/index.blade.php
  - tests/Feature/PublicGameData/GuildIndexTest.php
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/tasks/active/OTERYN-20260724-public-game-statistics.md
modules:
  - PublicGameData
dependencies:
  - blakinio/canary read-only schema evidence
  - existing canary SELECT-only credential boundary
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T23:30:00+02:00
head: 9245fca6762918f7d2a230b8a7dfe231bd4b8131
branch: feat/OTERYN-20260724-public-game-statistics
pr: none
status: implementing
context_routes:
  - public-game-data
  - canary-integration
  - database
  - security
  - testing
  - agent-governance
owned_paths:
  - app/Http/Controllers/PublicGameData/GuildIndexController.php
  - app/PublicGameData/GuildIndexQuery.php
  - routes/modules/public-game-statistics.php
  - resources/views/game/guilds/index.blade.php
  - tests/Feature/PublicGameData/GuildIndexTest.php
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/tasks/active/OTERYN-20260724-public-game-statistics.md
proven:
  - Current Canary head 93413bd53e9a40f0ff3c4f55986036b10be44e0f defines guilds, guild_membership and players in schema.sql.
  - guilds.name is unique; guild_membership.player_id is the membership primary key; players.deletion = 0 is the established active/listable character state.
  - The existing ot eryn_readonly policy already grants direct SELECT on guilds, guild_membership and players and rejects broader privileges.
  - No open PR owns the guild-index adapter, controller, route, view or test paths.
derived:
  - A guild index exposing only guild name and a derived active-member count can be implemented without new Canary tables, writes or private fields.
unknown:
  - Product meaning of guild points, level, residence and creationdata remains unverified for this capability and those fields will not be exposed.
conflicts: []
first_failure:
  marker: LOCAL_CHECKOUT_UNAVAILABLE
  evidence: execution sandbox could not resolve github.com; validation must use GitHub CI on the exact branch head
rejected_hypotheses:
  - Bundle all three statistics surfaces: rejected because latest-death and kill-statistic semantics were not yet proven and are not required for a complete guild index.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-public-game-statistics.md
validation:
  - command: overlap and current-schema discovery
    result: PASS
    evidence: open PR search plus blakinio/canary schema.sql at 93413bd53e9a40f0ff3c4f55986036b10be44e0f
  - command: local checkout and tests
    result: BLOCKED
    evidence: sandbox DNS resolution for github.com failed
blockers:
  - none
next_action: Implement the bounded guild-index query, controller, module route, view, focused tests and contract update on this branch.
```

## Notes

Privacy/moderation review: the index must not expose account IDs, owner IDs, balances, MOTD, invitations, wars, disciplinary data, deleted-character names or raw membership identifiers. Empty guild storage is a successful empty state; Canary query failure is HTTP 503 and must not render the empty-state copy.
