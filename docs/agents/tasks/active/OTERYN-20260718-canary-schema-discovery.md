# OTERYN-20260718 Canary schema discovery

## Goal

Establish an evidence-backed data contract between Oteryn Platform and the current `blakinio/canary` repository by inspecting Canary schema/source read-only and documenting exact proven account, player, guild, world/server, online, ban and session-related structures without implementing shared-data write paths.

## Acceptance criteria

- [ ] Pin discovery evidence to the exact current `blakinio/canary` commit SHA inspected.
- [ ] Prove the account table/model, primary key, relevant columns, constraints and relationships where source evidence exists.
- [ ] Prove the player/character table/model, account ownership relation, required creation fields/defaults, uniqueness/constraints and dependent rows where source evidence exists.
- [ ] Prove guild tables/models and membership/leadership relationships relevant to public reads.
- [ ] Prove world/server identifier representation or retain it as `UNKNOWN` where no authoritative schema/source evidence exists.
- [ ] Prove authoritative online-status storage/derivation where source evidence exists.
- [ ] Prove ban/status structures and references where source evidence exists.
- [ ] Prove session-related tables/fields where source evidence exists, without expanding into final authentication-flow design.
- [ ] Document trigger/migration behavior and schema constraints relevant to Platform reads/writes.
- [ ] Classify each material conclusion as `PROVEN`, `DERIVED`, `UNKNOWN` or `CONFLICT`.
- [ ] Update `docs/contracts/CANARY_DATA_CONTRACT.md` with exact source paths/SHA evidence.
- [ ] Do not implement Canary write paths or modify `blakinio/canary`.
- [ ] Complete checkpoint, validation and handover with exactly one concrete `next_action`.

## Ownership

```yaml
owned_paths:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/tasks/active/OTERYN-20260718-canary-schema-discovery.md
  - docs/agents/tasks/archive/OTERYN-20260718-canary-schema-discovery.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
modules:
  - Integration
  - PublicGameData
  - Accounts
  - Characters
dependencies:
  - docs/architecture/DATA_OWNERSHIP.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
blockers:
  - none
cross_repository_tasks:
  - blakinio/canary read-only schema/source discovery
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T22:20:41+02:00
head: 7ea6f6d8e1ee1158d7f339d92871751cab800d6a
branch: task/OTERYN-20260718-canary-schema-discovery
pr: none
status: investigating
context_routes:
  - agent-governance
  - canary-integration
  - database
  - accounts-characters
  - public-game-data
owned_paths:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/tasks/active/OTERYN-20260718-canary-schema-discovery.md
  - docs/agents/tasks/archive/OTERYN-20260718-canary-schema-discovery.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
proven:
  - Phase 1 Laravel bootstrap is complete on main at 7ea6f6d8e1ee1158d7f339d92871751cab800d6a.
  - No active task was claimed at startup and no open Canary schema discovery PR was found.
  - Repository policy permits autonomous writes only to blakinio/Oteryn-Platform; blakinio/canary is read-only for this task.
  - Current CANARY_DATA_CONTRACT.md is explicitly DISCOVERY REQUIRED and does not prove schema details.
derived:
  - The safe next step is bounded read-only discovery against the exact current blakinio/canary source before any shared-data mutation design.
unknown:
  - Exact current blakinio/canary HEAD to pin as evidence.
  - Actual account/player/guild/world/online/ban/session schema and constraints.
  - Which Canary/shared fields, if any, Oteryn Platform may safely mutate.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses: []
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260718-canary-schema-discovery.md
validation:
  - command: startup repository/task/PR verification
    result: PASS
    evidence: main HEAD 7ea6f6d8e1ee1158d7f339d92871751cab800d6a; ACTIVE_WORK reports no active task; no open matching PR found
blockers:
  - none
next_action: Inspect the exact current blakinio/canary HEAD and locate authoritative schema/migration/model sources for accounts, players, guilds, bans, online state, worlds and sessions.
```

## Notes

All Canary evidence must remain read-only. Do not infer schema semantics from MyAAC or generic TFS conventions.