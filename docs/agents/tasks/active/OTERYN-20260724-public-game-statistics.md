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

- [x] Exact Canary source tables and semantics are recorded from a pinned current Canary head.
- [x] The public field allowlist excludes private, disciplinary and unproven fields.
- [x] Ordering and pagination are deterministic and bounded.
- [x] Empty and dependency-unavailable behavior are distinct.
- [x] The implementation uses the database-enforced read-only Canary boundary without N+1 or unbounded queries.
- [x] Focused feature/integration tests and exact-head CI pass.

## Ownership

```yaml
owned_paths:
  - app/Http/Controllers/PublicGameData/GuildIndexController.php
  - app/PublicGameData/GuildIndexQuery.php
  - routes/modules/public-game-statistics.php
  - resources/views/game/guilds/index.blade.php
  - tests/Feature/PublicGameData/GuildIndexTest.php
  - docs/contracts/PUBLIC_GUILD_INDEX_CONTRACT.md
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
updated_at: 2026-07-24T23:59:00+02:00
head: 20aad2c97d591c5f9dc9168333019c2a9dc48567
branch: feat/OTERYN-20260724-public-game-statistics
pr: 160
status: ready
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
  - docs/contracts/PUBLIC_GUILD_INDEX_CONTRACT.md
  - docs/agents/tasks/active/OTERYN-20260724-public-game-statistics.md
proven:
  - Current Canary head 93413bd53e9a40f0ff3c4f55986036b10be44e0f defines guilds, guild_membership and players in schema.sql.
  - guilds.name is unique; guild_membership.player_id is the membership primary key; players.deletion = 0 is the established active/listable character state.
  - The existing oteryn_readonly policy already grants direct SELECT on guilds, guild_membership and players and rejects broader privileges.
  - No open PR owns the guild-index adapter, controller, route, view or test paths.
  - PR 160 implements GET /guilds with a dedicated query, controller, module route, view and focused tests.
  - The projection allowlist is guild name plus a derived count of membership rows joined to active players only.
  - Pagination is fixed at 50 and ordering is guild name ascending with guild ID as a deterministic tie-breaker.
  - The aggregate is produced by one grouped active-members subquery joined into the bounded page query; the paginator performs at most one count query plus one page query.
  - Empty data returns HTTP 200 while Canary query failure returns HTTP 503.
  - All seven pull-request workflow families succeeded on executable head 20aad2c97d591c5f9dc9168333019c2a9dc48567.
derived:
  - The guild index adds no Canary table grant because every source table is already inside the database-enforced read allowlist.
unknown:
  - Product meaning of guild points, level, residence and creationdata remains unverified for this capability and those fields are not exposed.
conflicts: []
first_failure:
  marker: PHPSTAN_LEVEL_10
  evidence: early CI revisions stopped at static analysis before tests; type-safe response assertions and a grouped aggregate query produced a passing exact-head CI run 30127714879
rejected_hypotheses:
  - Bundle all three statistics surfaces: rejected because latest-death and kill-statistic semantics were not proven and are not required for a complete guild index.
  - Inspect paginator internals in the feature test: rejected because response-level ordering, count output, pagination boundary and query-log assertions cover the public contract without introducing generic-type ambiguity.
changed_paths:
  - app/Http/Controllers/PublicGameData/GuildIndexController.php
  - app/PublicGameData/GuildIndexQuery.php
  - docs/agents/tasks/active/OTERYN-20260724-public-game-statistics.md
  - docs/contracts/PUBLIC_GUILD_INDEX_CONTRACT.md
  - resources/views/game/guilds/index.blade.php
  - routes/modules/public-game-statistics.php
  - tests/Feature/PublicGameData/GuildIndexTest.php
validation:
  - command: overlap and current-schema discovery
    result: PASS
    evidence: open PR search plus blakinio/canary schema.sql and src/io/ioguild.cpp at 93413bd53e9a40f0ff3c4f55986036b10be44e0f
  - command: database privilege boundary review
    result: PASS
    evidence: existing verifier and provisioning template require direct SELECT only on guilds, guild_membership and players among the approved tables
  - command: local checkout and tests
    result: BLOCKED
    evidence: sandbox DNS resolution for github.com failed; no local result is claimed
  - command: CI run 30127714879 on 20aad2c97d591c5f9dc9168333019c2a9dc48567
    result: PASS
    evidence: composer validation/install/audit, Pint, PHPStan level 10 and full test suite succeeded
  - command: Agent Governance run 30127714795
    result: PASS
    evidence: exact executable head workflow succeeded
  - command: Phase 7 Production-Like Validation run 30127714852
    result: PASS
    evidence: exact executable head workflow succeeded
  - command: Platform DB Outage Validation run 30127714889
    result: PASS
    evidence: exact executable head workflow succeeded
  - command: Game Auth Ticket Concurrency run 30127714828
    result: PASS
    evidence: exact executable head workflow succeeded
  - command: Acceptance E2E and Visual UX run 30127714786
    result: PASS
    evidence: exact executable head workflow succeeded
  - command: Build Synology Staging Images run 30127714843
    result: PASS
    evidence: exact executable head workflow succeeded
blockers:
  - none
next_action: Verify the docs-only checkpoint head remains green, then mark PR 160 ready for review and merge only while all required checks remain successful.
```

## Notes

Privacy/moderation review: the index does not expose account IDs, owner IDs, balances, MOTD, invitations, wars, disciplinary data, deleted-character names or raw membership identifiers. Empty guild storage is a successful empty state; Canary query failure is HTTP 503 and does not render the empty-state copy.
