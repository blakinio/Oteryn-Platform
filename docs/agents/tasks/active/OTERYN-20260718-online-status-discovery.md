# OTERYN-20260718 Online status discovery

## Goal

Prove, from current read-only Canary source evidence, whether Oteryn Platform can rely on one authoritative cluster-wide online-character identity source, and define its freshness, stale-data and dependency-failure semantics before any online-list route is implemented.

## Acceptance criteria

- [x] Verify the current `blakinio/canary` revision used for this discovery and record exact source evidence.
- [x] Prove or reject the current writer/removal lifecycle of `players_online`.
- [x] Prove the lifecycle, status, heartbeat/expiry and cleanup semantics of `cluster_sessions` relevant to online-character identity.
- [x] Verify the roles and freshness/failure semantics of process-local `ProtocolStatus`, Redis `ChannelRuntimeRegistry`, and SQL `channel_runtime_status`.
- [x] Select one evidence-backed cluster-wide online-character identity source/aggregation contract, or retain the feature as `UNKNOWN` if no safe source exists.
- [x] Define freshness, stale-data and dependency-failure behavior for any approved Platform read contract.
- [ ] Update `docs/contracts/CANARY_DATA_CONTRACT.md` and project state only with proven/derived evidence; preserve unresolved items as `UNKNOWN` or `CONFLICT`.
- [x] Do not implement an online-list route or modify `blakinio/canary`.
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
updated_at: 2026-07-19T00:02:00+02:00
head: 4f2f0e533128b44eb465f8edc41e95aec5048a15
branch: task/OTERYN-20260718-online-status-discovery
pr: 5
status: documenting
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
  - blakinio/canary is read-only for this task and no Canary write was performed.
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
  - cluster_sessions.expires_at is written from Canary epoch-millisecond system-clock time, so it is an explicit consumer-visible expiry boundary rather than a process-relative monotonic timestamp.
  - The cluster_sessions schema provides no database-native TTL or automatic expiry deletion; clean release/outage paths delete rows, while an ungraceful process crash can leave an ONLINE row physically present until another writer overwrites/removes it or another cleanup mechanism acts.
  - The current Canary multichannel::listOnlinePlayers() and findOnlineChannelForPlayer() helpers filter only status = ONLINE and do not filter expires_at; listOnlinePlayers() also returns an empty vector when its DB query returns no result, so Platform must not reuse those semantics as its public contract.
  - ChannelRuntimeRegistry is the Redis-backed per-channel liveness fast path; a failed publish/read clears its entire in-process snapshot, and getStatus/getAvailability reject records older than staleAfterMs.
  - ClusterRuntime configures ChannelRuntimeRegistry staleAfterMs and Redis TTL from the session lease TTL, publishes local tracked-session count plus channel runtime state, and queues channel_runtime_status as an asynchronous best-effort SQL diagnostic mirror.
  - channel_runtime_status is keyed only by channel_id and stores instance_id, node_id, started_at, last_heartbeat, status, players_online and build/hash diagnostics; its SQL mirror write failure does not invalidate a healthy Redis runtime path.
  - ProtocolStatus remains process-local: counts derive from Game::getPlayerStats(), extended player lists iterate local Game::players, and individual status checks use local Game::getPlayerByName().
  - Game::getPlayerStats() groups only the current process local Game::players by IP and applies the public count filtering rules; it does not aggregate character identities cluster-wide.
derived:
  - The handover state on main requires bounded online-status discovery rather than online-list implementation.
  - players_online cannot be repaired into a cluster-wide read contract by Platform-side freshness filtering because each channel process periodically prunes other channels' rows by design.
  - The approved cluster-wide online-character identity backend source is cluster_sessions joined to public player fields, restricted to status = ONLINE and expires_at greater than the read-time epoch milliseconds; physical row presence or status = ONLINE alone is insufficient.
  - Raw cluster_sessions security/session fields remain non-public; a future public adapter may expose only an explicit public character allowlist plus durable channels.id.
  - A positive online result is bounded by the remaining cluster-session lease lifetime after the last successful DB acquire/heartbeat write; with current defaults that lease window is 30 seconds, plus any wall-clock skew between the writer and reader.
  - Because routine DB heartbeat writes are best-effort, expiry filtering intentionally fails closed and may produce temporary false negatives while a player remains legitimately online in Redis but the DB mirror has not refreshed within the lease window.
  - A Canary DB query failure must be surfaced as dependency unavailable/error, never converted to an empty online list and never served from an unbounded stale cache.
  - Fresh ChannelRuntimeRegistry or SQL channel_runtime_status gating is not required for the online-character identity contract: Redis ChannelRuntimeRegistry does not expose identities to Platform, while SQL channel_runtime_status is explicitly a best-effort diagnostic mirror and would add an independent false-negative path without tightening the cluster_sessions expiry bound.
  - Fresh per-channel availability/count remains a separate runtime-status concern and may be integrated independently from online-character identity.
unknown:
  - Whether a separate repository path not inspected by this task eventually physically deletes every expired orphaned cluster_sessions row; consumers must not rely on such cleanup because expiry filtering is sufficient and required for correctness.
  - Maximum production wall-clock skew between Canary processes, the Platform reader and database host; any freshness SLA must include that operational skew unless a common time source is enforced.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Treat players_online as authoritative cluster-wide state: rejected because each process rewrites/prunes the shared table from only its own local Game::players set every 10 minutes and startup truncates it.
  - Treat status = ONLINE alone in cluster_sessions as a fresh online list: rejected because crash/orphan rows can remain physically present after expires_at and current Canary admin lookup helpers do not apply expiry filtering.
  - Require SQL channel_runtime_status as a hard identity gate: rejected because it is an asynchronous best-effort diagnostic mirror and does not improve the cluster_sessions lease-expiry freshness bound.
  - Convert Canary database read failure into an empty online list: rejected because dependency failure is not evidence that zero characters are online.
changed_paths:
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260718-online-status-discovery.md
validation:
  - command: startup state verification
    result: PASS
    evidence: root governance, project state, repository map, context routing, active-work index, archived handover, remote PR state and main HEAD inspected before task creation
  - command: read-only Canary revision comparison and targeted source inspection
    result: PASS
    evidence: current Canary HEAD be7842412beb5d240e76ffd4cd18aacdc3a2dcca verified; compare from 6df7f906ed6f8fef0aa326439a5494bd1e3d523c found documentation-only changes; exact current source inspected for players_online, cluster_sessions, ChannelRuntimeRegistry, channel_runtime_status and ProtocolStatus semantics
blockers:
  - Local working-tree/remotes/worktrees cannot be inspected because this execution environment has no local repository checkout; work is being performed against the GitHub repository branch directly.
next_action: Update CANARY_DATA_CONTRACT.md and PROJECT_STATE.md with the proven cluster_sessions online-read contract and the remaining bounded unknowns.
```

## Notes

This task is documentation/contract discovery only. Do not implement a public online-list route until the source and freshness/failure contract is proven. Do not write to `blakinio/canary`.
