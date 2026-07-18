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
- [x] Update `docs/contracts/CANARY_DATA_CONTRACT.md` and project state only with proven/derived evidence; preserve unresolved items as `UNKNOWN` or `CONFLICT`.
- [x] Do not implement an online-list route or modify `blakinio/canary`.
- [x] Run repository validation and verify current-head CI before merge/closure.

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
updated_at: 2026-07-19T00:14:00+02:00
head: 655fcd1c7b048f4e6b71988127094e7fd80eaf4c
branch: task/OTERYN-20260718-online-status-discovery
pr: 5
status: completed
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
  - main HEAD before task creation was 874215a0f962e8e8efd8873a2b3e58802ea141ce, the merged delivery of PR #4.
  - No open Oteryn Platform PR was present when this task was claimed, and no other active task ownership overlapped the declared owned_paths.
  - blakinio/canary remained read-only for this task and no Canary write was performed.
  - Current blakinio/canary HEAD verified for this discovery is be7842412beb5d240e76ffd4cd18aacdc3a2dcca.
  - Comparing the prior contract baseline 6df7f906ed6f8fef0aa326439a5494bd1e3d523c to current Canary HEAD be7842412beb5d240e76ffd4cd18aacdc3a2dcca showed only agent/handover documentation changes; online/status source and schema evidence did not change between those revisions.
  - Canary startup truncates the shared players_online table, and every process schedules Game::updatePlayersOnline every 10 minutes.
  - Game::updatePlayersOnline reads only the current process-local Game::players map, inserts those GUIDs, then deletes rows not in that one local set; an empty local set can clear the whole table.
  - Therefore players_online has last-process-writer local-channel semantics in multi-channel mode and is rejected as an authoritative cluster-wide online-character identity source.
  - cluster_sessions acquisition is coupled to a successful Redis lease and a successful initial DB row write; initial DB write failure releases the acquired Redis lease and rejects login.
  - cluster_sessions heartbeat refreshes status ONLINE, last_heartbeat and expires_at when account_id/session_id match; routine DB heartbeat writes are best-effort after successful Redis renew.
  - Clean logout and Redis-outage force-expiry paths perform best-effort matching cluster_sessions row deletion.
  - Default multi-channel tuning is a 30000 ms session lease TTL, 5000 ms heartbeat interval and 10000 ms Redis failure grace period; lease TTL must exceed heartbeat interval.
  - cluster_sessions.expires_at is Unix-epoch system-clock milliseconds and is an explicit consumer-visible expiry boundary.
  - The cluster_sessions schema provides no database-native TTL; an ungraceful crash can leave a physically present ONLINE row after expires_at.
  - Current Canary multichannel::listOnlinePlayers() and findOnlineChannelForPlayer() filter only status = ONLINE and do not filter expires_at; listOnlinePlayers() also collapses a failed/no-result DB storeQuery into an empty vector.
  - ChannelRuntimeRegistry is the Redis-backed fail-closed per-channel liveness fast path with freshness filtering and snapshot clear on transport failure.
  - SQL channel_runtime_status is a channel-keyed asynchronous best-effort diagnostic mirror; its write failure does not invalidate a healthy Redis runtime path.
  - ProtocolStatus remains process-local, and Game::getPlayerStats() aggregates only the current process local player map.
  - docs/contracts/CANARY_DATA_CONTRACT.md is pinned to current Canary SHA be7842412beb5d240e76ffd4cd18aacdc3a2dcca and now records the approved bounded cluster-wide online read contract.
  - docs/agents/PROJECT_STATE.md now records online-status discovery as complete and OTERYN-20260719-online-list-read-model as the next planned task.
  - PR #5 changed only docs/agents/ACTIVE_WORK.md, docs/agents/PROJECT_STATE.md, this task record lifecycle, and docs/contracts/CANARY_DATA_CONTRACT.md; no application or Canary source was changed.
  - GitHub Actions CI run #37 on substantive documentation HEAD 655fcd1c7b048f4e6b71988127094e7fd80eaf4c completed successfully; its test job passed Composer metadata/lockfile validation, dependency install, formatting and tests.
derived:
  - The approved cluster-wide online-character identity backend source is cluster_sessions joined to public player fields, restricted to status = ONLINE, expires_at greater than read-time epoch milliseconds, and players.deletion = 0.
  - Raw cluster_sessions security/session fields remain non-public; a public adapter may expose only an explicit public player allowlist plus durable channels.id/approved channel metadata.
  - A positive online result is bounded by the remaining cluster-session lease lifetime after the last successful DB acquire/heartbeat write; with current defaults the lease window is 30 seconds, plus wall-clock skew.
  - Because DB heartbeat persistence is best-effort, expiry filtering intentionally fails closed and may create temporary false negatives while Redis still considers a player online.
  - Canary DB query failure must be surfaced as dependency unavailable/error, never converted to an empty online list and never masked by an unbounded stale cache.
  - Fresh ChannelRuntimeRegistry or SQL channel_runtime_status gating is not required for identity: Redis runtime state does not expose identities to Platform and the SQL runtime mirror is best-effort and would add an independent false-negative path without tightening the cluster_sessions expiry bound.
  - Fresh per-channel availability/count remains a separate runtime-status integration concern.
unknown:
  - Whether another cleanup path eventually physically deletes every expired orphaned cluster_sessions row; correctness does not depend on it because expiry filtering is mandatory.
  - Maximum production wall-clock skew between Canary processes, Platform and database host; an exact production freshness SLA must include or bound that skew.
conflicts:
  - Existing unrelated Canary contract conflict remains: schema.sql accounts.tournament_coins versus AccountRepositoryDB accounts.coins_tournament.
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Treat players_online as authoritative cluster-wide state: rejected by its proven local-process writer/pruner lifecycle.
  - Treat status = ONLINE alone in cluster_sessions as fresh identity: rejected because expired crash/orphan rows can remain physically present.
  - Require SQL channel_runtime_status as a hard identity gate: rejected because it is a best-effort diagnostic mirror and does not improve the lease-expiry freshness bound.
  - Convert Canary database read failure into an empty online list: rejected because dependency failure is not evidence that zero characters are online.
changed_paths:
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/archive/OTERYN-20260718-online-status-discovery.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
validation:
  - command: startup state verification
    result: PASS
    evidence: root governance, project state, repository map, context routing, active-work index, archived handover, remote PR state and main HEAD inspected before task creation
  - command: read-only Canary revision comparison and targeted source inspection
    result: PASS
    evidence: current Canary HEAD be7842412beb5d240e76ffd4cd18aacdc3a2dcca verified; compare from prior baseline found documentation-only changes; exact current source inspected for players_online, cluster_sessions, ChannelRuntimeRegistry, channel_runtime_status and ProtocolStatus semantics
  - command: full PR changed-file list and diff inspection
    result: PASS
    evidence: PR #5 changed only owned documentation/task paths and contained no unrelated or forbidden application/Canary changes
  - command: GitHub Actions CI run #37 on 655fcd1c7b048f4e6b71988127094e7fd80eaf4c
    result: PASS
    evidence: workflow completed successfully; test job passed Composer metadata/lockfile validation, dependency installation, formatting and tests
  - command: local repository validation
    result: UNAVAILABLE
    evidence: execution environment has no local repository checkout, so local working tree/remotes/worktrees and local commands could not be inspected/run; remote GitHub CI is the validation source
blockers:
  - none for merge; local checkout is unavailable but this is documented and current-head GitHub CI is required before merge
next_action: Create OTERYN-20260719-online-list-read-model from the approved cluster_sessions status/expiry/deletion and dependency-failure contract after PR #5 is merged.
```

## Notes

Discovery is complete. No public online-list route was implemented in this task, and `blakinio/canary` was not modified.
