# OTERYN-20260718 Online status discovery

## Goal

Prove, from current read-only Canary source evidence, whether Oteryn Platform can rely on one authoritative cluster-wide online-character identity source, and define its freshness, stale-data and dependency-failure semantics before any online-list route is implemented.

## Acceptance criteria

- [x] Verify the current `blakinio/canary` revision used for this discovery and record exact source evidence.
- [x] Prove or reject the current writer/removal lifecycle of `players_online`.
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
updated_at: 2026-07-18T23:41:00+02:00
head: b9852f1ca17852805f7d39f7309c5f60b96439c9
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
  - No other active task ownership was declared on main when this task was claimed, so the owned_paths above did not overlap another recorded active task.
  - blakinio/canary is read-only for this task.
  - Draft PR #5 targets main from task/OTERYN-20260718-online-status-discovery.
  - Current blakinio/canary HEAD verified for this discovery is be7842412beb5d240e76ffd4cd18aacdc3a2dcca.
  - Comparing the prior contract baseline 6df7f906ed6f8fef0aa326439a5494bd1e3d523c to current Canary HEAD be7842412beb5d240e76ffd4cd18aacdc3a2dcca shows only agent/handover documentation changes; online/status source and schema evidence used by this task did not change between those revisions.
  - Canary startup GlobalEvent executes cleanupDatabase(), which TRUNCATEs the shared players_online table on every process startup.
  - Game::start schedules Game::updatePlayersOnline every 10 minutes via UPDATE_PLAYERS_ONLINE_DB.
  - Game::updatePlayersOnline reads only the current process-local Game::players map, INSERT IGNOREs those player GUIDs, then DELETEs every players_online row whose player_id is not in that local set; when the local set is empty it clears the whole table if rows exist.
  - Therefore players_online has last-process-writer local-channel semantics in multi-channel mode and is rejected as an authoritative cluster-wide online-character identity source.
  - cluster_sessions acquisition is coupled to a successful Redis session lease; the DB row is written as status ONLINE with player_id, channel_id, instance_id, session_id, fencing_token, acquired_at, last_heartbeat and expires_at, and an initial DB write failure releases the just-acquired Redis lease and rejects the login.
  - cluster_sessions heartbeat refreshes status ONLINE, last_heartbeat and expires_at when account_id and session_id match; heartbeat DB writes are best-effort after a successful Redis renew and do not disconnect a player on transient DB failure.
  - Clean logout releases the Redis lease and best-effort deletes the matching cluster_sessions row by account_id and session_id.
  - Default multi-channel tuning is a 30000 ms session lease TTL, 5000 ms heartbeat interval and 10000 ms Redis failure grace period; startup validation requires lease TTL greater than heartbeat interval.
  - The heartbeat cycle is scheduled at max(1000 ms, configured sessionHeartbeatInterval) and calls renewClusterSessions(), which invokes ClusterRuntime::renewAllAndCollectExpired().
  - During Redis loss, a tracked session is force-expired before the earlier of configured failure grace exhaustion or the last heartbeat interval before its lease expiry; ClusterRuntime then best-effort deletes the DB row and synchronously kicks affected local players.
derived:
  - The handover state on main requires bounded online-status discovery rather than online-list implementation.
  - players_online cannot be repaired into a cluster-wide read contract by Platform-side freshness filtering because each channel process periodically prunes other channels' rows by design.
  - cluster_sessions is a stronger candidate for cluster-wide identity than players_online, but Platform must not equate status ONLINE alone with freshness because routine DB heartbeat writes are best-effort and crash/outage paths can leave rows until expires_at.
unknown:
  - Whether every stale/expired cluster_sessions row is eventually removed by a separate cleanup path, or whether consumers must treat expires_at as the sole stale-row exclusion boundary.
  - Exact dependency-failure behavior required for a Platform public online read model when the Canary database query itself fails.
  - Whether the approved public identity contract should use cluster_sessions alone with status/expiry filtering or additionally gate rows by fresh channel runtime state.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Treat players_online as authoritative cluster-wide state: rejected because each process rewrites/prunes the shared table from only its own local Game::players set every 10 minutes and startup truncates it.
  - Treat cluster_sessions as a public online list without explicit status/expiry/failure semantics: rejected pending completion of this discovery.
changed_paths:
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260718-online-status-discovery.md
validation:
  - command: startup state verification
    result: PASS
    evidence: root governance, project state, repository map, context routing, active-work index, archived handover, remote PR state and main HEAD inspected before task creation
  - command: read-only Canary revision comparison and targeted source inspection
    result: PASS
    evidence: current Canary HEAD be7842412beb5d240e76ffd4cd18aacdc3a2dcca verified; compare from 6df7f906ed6f8fef0aa326439a5494bd1e3d523c found documentation-only changes; exact current source inspected for players_online and initial cluster_sessions lifecycle
blockers:
  - Local working-tree/remotes/worktrees cannot be inspected because this execution environment has no local repository checkout; work is being performed against the GitHub repository branch directly.
next_action: Prove cluster_sessions stale-row cleanup and expiry semantics, then decide whether cluster-wide online identity requires fresh ChannelRuntimeRegistry gating in addition to status ONLINE and expires_at filtering.
```

## Notes

This task is documentation/contract discovery only. Do not implement a public online-list route until the source and freshness/failure contract is proven. Do not write to `blakinio/canary`.
