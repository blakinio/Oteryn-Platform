# OTERYN-20260718 Online status discovery

## Goal

Prove, from current read-only Canary source evidence, whether Oteryn Platform can rely on one authoritative cluster-wide online-character identity source, and define its freshness, stale-data and dependency-failure semantics before any online-list route is implemented.

## Acceptance criteria

- [ ] Verify the current `blakinio/canary` revision used for this discovery and record exact source evidence.
- [ ] Prove or reject the current writer/removal lifecycle of `players_online`.
- [ ] Prove the lifecycle, status, heartbeat/expiry and cleanup semantics of `cluster_sessions` relevant to online-character identity.
- [ ] Verify the roles and freshness/failure semantics of process-local `ProtocolStatus`, Redis `ChannelRuntimeRegistry`, and SQL `channel_runtime_status`.
- [ ] Select one evidence-backed cluster-wide online-character identity source/aggregation contract, or retain the feature as `UNKNOWN` if no safe source exists.
- [ ] Define freshness, stale-data and dependency-failure behavior for any approved Platform read contract.
- [ ] Update `docs/contracts/CANARY_DATA_CONTRACT.md` and project state only with proven/derived evidence; preserve unresolved items as `UNKNOWN` or `CONFLICT`.
- [ ] Do not implement an online-list route or modify `blakinio/canary`.
- [ ] Run repository validation and verify current-head CI before merge/closure.

## Ownership

```yaml
owned_paths:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260718-online-status-discovery.md
  - docs/agents/tasks/archive/OTERYN-20260718-online-status-discovery.md
modules:
  - PublicGameData contract discovery
  - Canary integration contract
dependencies:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/tasks/archive/OTERYN-20260718-game-read-model.md
blockers:
  - none
cross_repository_tasks:
  - blakinio/canary read-only source discovery only; no writes authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T23:18:23+02:00
head: d3dc100744f730d908179600edd0e7d3ba11b4ae
branch: task/OTERYN-20260718-online-status-discovery
pr: 5
status: investigating
context_routes:
  - agent-governance
  - public-game-data
  - canary-integration
owned_paths:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260718-online-status-discovery.md
  - docs/agents/tasks/archive/OTERYN-20260718-online-status-discovery.md
proven:
  - main HEAD before task creation is 874215a0f962e8e8efd8873a2b3e58802ea141ce, the merged delivery of PR #4.
  - No open Oteryn Platform PR was present when this task was claimed.
  - docs/agents/ACTIVE_WORK.md on main reported no active task and recommended this exact online-status discovery as next work.
  - The archived game-read-model handover has exactly one next_action: create this discovery task and prove cluster-wide online identity/freshness/failure semantics before implementing an online-list route.
  - The existing Canary data contract marks the global public online list UNKNOWN and does not approve players_online or cluster_sessions as a sole public source.
  - No other active task ownership is declared on main, so the owned_paths above do not overlap another recorded active task.
  - blakinio/canary is read-only for this task.
  - Draft PR #5 targets main from task/OTERYN-20260718-online-status-discovery.
derived:
  - The handover state on main requires bounded online-status discovery rather than online-list implementation.
unknown:
  - Current blakinio/canary HEAD and whether online-related source changed since the contract baseline 6df7f906ed6f8fef0aa326439a5494bd1e3d523c.
  - Exact current writer/removal lifecycle of players_online.
  - Whether cluster_sessions can safely represent cluster-wide online character identity after explicit freshness/status filtering.
  - Required stale-data and dependency-failure semantics for a Platform public online read model.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Treat players_online as authoritative without proving its writer lifecycle: rejected by the existing contract.
  - Treat cluster_sessions as a public online list without proving lease/freshness/failure semantics: rejected by the existing contract.
changed_paths:
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260718-online-status-discovery.md
validation:
  - command: startup state verification
    result: PASS
    evidence: root governance, project state, repository map, context routing, active-work index, archived handover, remote PR state and main HEAD inspected before task creation
blockers:
  - Local working-tree/remotes/worktrees cannot be inspected because this execution environment has no local repository checkout; work is being performed against the GitHub repository branch directly.
next_action: Verify current blakinio/canary HEAD and inspect the exact current players_online writer/removal lifecycle read-only.
```

## Notes

This task is documentation/contract discovery only. Do not implement a public online-list route until the source and freshness/failure contract is proven. Do not write to `blakinio/canary`.
