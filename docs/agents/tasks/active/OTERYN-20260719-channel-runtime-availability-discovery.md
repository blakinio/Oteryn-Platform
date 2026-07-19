# OTERYN-20260719 Channel runtime availability discovery

## Goal

Resolve the remaining Phase 4 transport/contract uncertainty for fresh per-channel runtime availability and player counts by verifying current Canary runtime source, transport, freshness and failure semantics against live repository evidence, then approve the smallest safe Platform integration boundary or record the exact blocker without implementing speculative runtime access.

## Acceptance criteria

- [x] Archive the merged `OTERYN-20260719-public-news-read-model` task record under `docs/agents/tasks/archive/` with the exact historical blob unchanged.
- [x] Verify current `blakinio/canary` main revision and read the repository governance/routing context required for read-only discovery.
- [x] Inspect the current Canary `ChannelRuntimeRegistry`, `ClusterRuntime`, runtime-status payload/schema, Redis key/TTL/freshness behavior and SQL `channel_runtime_status` mirror writer semantics.
- [x] Inspect whether Canary already exposes a purpose-built external runtime-status API/endpoint suitable for Platform consumption; do not infer one from process-local status protocol behavior.
- [x] Inspect current Oteryn Platform runtime/dependency/config boundaries relevant to Redis or SQL runtime-status reads.
- [x] Compare viable transport choices — direct Redis snapshot, separately contracted SQL freshness read, or purpose-built service/API — including least privilege, stale-data bounds, dependency failure semantics and operational coupling.
- [x] Update `CANARY_DATA_CONTRACT.md` with evidence pinned to the verified current Canary revision and an explicit approved next integration boundary, or an exact blocker if no safe bounded transport can be approved.
- [x] Update `PROJECT_STATE.md` / `ACTIVE_WORK.md` and roadmap state only as required by the discovery result; do not claim live runtime availability is implemented.
- [x] Do not modify Canary/login-server repositories and do not add Platform runtime integration code in this discovery task.
- [x] Run repository CI and Agent Governance on the delivery-validation head; require a fresh exact-head pass after this ready checkpoint before merge.

## Ownership

```yaml
owned_paths:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/architecture/ROADMAP.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-discovery.md
  - docs/agents/tasks/active/OTERYN-20260719-public-news-read-model.md
  - docs/agents/tasks/archive/OTERYN-20260719-public-news-read-model.md
modules:
  - PublicGameData
  - Integration
  - architecture
  - agent-governance
dependencies:
  - OTERYN-20260719-online-list-read-model
  - OTERYN-20260719-public-news-read-model
  - docs/contracts/CANARY_DATA_CONTRACT.md
blockers:
  - none
cross_repository_tasks:
  - blakinio/canary is read-only evidence source; no writes are authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T18:55:00+02:00
head: 242b6c54f1a528406ef0e4a7c6bd94c73add5c39
branch: task/OTERYN-20260719-channel-runtime-availability-discovery
pr: 21
status: ready
context_routes:
  - agent-governance
  - architecture
  - public-game-data
  - canary-integration
  - security
  - testing
owned_paths:
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/architecture/ROADMAP.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-discovery.md
  - docs/agents/tasks/active/OTERYN-20260719-public-news-read-model.md
  - docs/agents/tasks/archive/OTERYN-20260719-public-news-read-model.md
proven:
  - Main was verified at 3031f299d15a3761d6c332d6138a46629b59d009, the squash merge of PR #20, before starting this task.
  - Post-merge live PR search returned no open pull requests in blakinio/Oteryn-Platform.
  - The merged public-news task archive uses blob 9a29a9183a42a82d3285c0a29dc2c42987515dda, exactly matching the former active task blob.
  - Current blakinio/canary main is d4f8bb3aa3a6ca31b54f324797078360da28f8f8; comparison from the previous contract pin be7842412beb5d240e76ffd4cd18aacdc3a2dcca showed 31 commits but no changes to the inspected runtime, Redis client, status protocol, schema or migration paths relevant to this contract.
  - Canary ChannelRuntimeRegistry uses deterministic keys cluster:channel:{channel_id}:runtime and fail-closes its entire in-process runtime snapshot on Redis transport/read failure.
  - The runtime hash contains channel_id, instance_id, node_id, started_at, last_heartbeat, status, players_online, build_sha, map_hash and data_hash; publication atomically HSETs those fields and applies PEXPIRE.
  - ClusterRuntime configures the runtime TTL and local stale cutoff from sessionLeaseTtl; current defaults are a 30000 ms lease/runtime TTL and 5000 ms heartbeat interval.
  - Missing or expired runtime keys are distinguishable from Redis transport/protocol failure: missing data yields no status while the Redis client remains healthy, whereas transport/malformed-reply failure makes the client unhealthy.
  - ClusterRuntime queues the SQL channel_runtime_status mirror independently after attempting Redis publish/refresh, including when Redis publish/refresh fails; therefore a recently updated SQL ONLINE row can coexist with an unavailable Redis-authoritative runtime path.
  - Current ProtocolStatus reads process-local g_game player state and does not consult ChannelRuntimeRegistry, so it is not a suitable cluster-authoritative runtime transport.
  - No suitable purpose-built external aggregate runtime-status endpoint was found in the inspected current Canary source; the existing status protocol is explicitly process-local for the relevant data.
  - Current Oteryn Platform has no dedicated Canary-runtime Redis dependency/config boundary: composer has no explicit Redis client package, config/database.php defines SQL connections only, config/cache.php exposes array/file/null stores, and .env.example has no Canary runtime Redis settings.
  - CANARY_DATA_CONTRACT approves the next bounded integration as a dedicated read-only Redis adapter over deterministic configured-channel runtime keys, using enabled channels from the existing DB read as the expected key set.
  - The approved Redis adapter must use a dedicated read-only credential/connection, must not scan the keyspace or access session lease keys, and must expose only channel_id, runtime status and non-negative players_online while keeping instance/node/build/map/data metadata private.
  - Redis key existence plus positive TTL and valid runtime fields/state form the external freshness boundary; missing/expired keys while Redis is healthy mean unknown/unavailable, not synthetic OFFLINE or zero players.
  - Any Redis transport/protocol failure while reading one logical configured-channel snapshot invalidates all runtime fields for that snapshot; static channel metadata may still render independently.
  - SQL channel_runtime_status and process-local ProtocolStatus are forbidden authoritative fallbacks; no application cache is approved initially.
  - The online-character cluster_sessions identity contract remains independent and must not be gated on runtime availability.
  - Final PR #21 diff contains exactly six task-owned paths, with the merged public-news task represented as an exact-content rename with zero additions and zero deletions.
  - Delivery-validation head 242b6c54f1a528406ef0e4a7c6bd94c73add5c39 passed CI run 29695019939 (#326), including Composer validation/install, Pint, PHPStan/Larastan level 10 and the full test suite.
  - Delivery-validation head 242b6c54f1a528406ef0e4a7c6bd94c73add5c39 passed Agent Governance run 29695019933 (#247).
derived:
  - Direct read-only Redis is the smallest transport that preserves Canary's actual fail-closed runtime availability semantics without inventing a new Canary service endpoint.
  - SQL channel_runtime_status cannot safely substitute for Redis-authoritative runtime availability because its asynchronous write can succeed after the Redis publish/refresh path has failed.
  - Redis TTL is a better primary external freshness boundary than Platform-versus-Canary wall-clock comparison for this runtime adapter because Canary applies TTL atomically with the heartbeat write.
  - The approved successor bounded task is OTERYN-20260719-channel-runtime-availability-read-model; it must add the dedicated Redis dependency/config/test boundary and public server runtime projection without broadening the Canary DB table allowlist.
unknown:
  - production provisioning details for the dedicated read-only Canary runtime Redis endpoint/credential remain deployment input and must not be committed as secrets
  - exact supported Redis client/dependency selection for Laravel 13/PHP 8.5 must be resolved from current primary documentation during the implementation task
conflicts: []
first_failure:
  marker: CONTRACT_FULL_FILE_TAIL_TRUNCATION
  evidence: PR #21 contract patch inspection detected that the first full-file replacement accidentally removed the existing Remaining UNKNOWN, Safety rules and Next contract dependency tail; the missing tail was restored before delivery validation and the final patch now contains only intended contract changes.
rejected_hypotheses:
  - Implement SQL channel_runtime_status immediately: rejected because the SQL mirror is written best-effort even after Redis runtime publication failure and can therefore overstate authoritative availability.
  - Reuse process-local ProtocolStatus as cluster availability: rejected because current source reads only process-local g_game state and does not consult ChannelRuntimeRegistry.
  - Add channel_runtime_status to the Platform Canary DB privilege allowlist: rejected because the approved next transport is dedicated read-only Redis and the SQL mirror remains diagnostic only.
  - Gate the existing cluster-wide online-character identity read on runtime status: rejected because the contracts deliberately separate identity lease freshness from runtime availability.
  - Discover runtime channels by Redis KEYS/SCAN: rejected because durable configured channel IDs already come from the approved channels read and keyspace enumeration would broaden privilege/operational coupling.
changed_paths:
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-discovery.md
  - docs/agents/tasks/archive/OTERYN-20260719-public-news-read-model.md
  - docs/architecture/ROADMAP.md
  - docs/contracts/CANARY_DATA_CONTRACT.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: post-PR20 main/open-PR state and current Oteryn contract gap were verified before task claim
  - command: read-only Canary runtime transport discovery at d4f8bb3aa3a6ca31b54f324797078360da28f8f8
    result: PASS
    evidence: root governance/routing, ChannelRuntimeRegistry/Status, ClusterRuntime, HiredisRedisClient, migration 59, ProtocolStatus and multichannel architecture were inspected without repository writes
  - command: Oteryn Platform dependency/config boundary inspection
    result: PASS
    evidence: composer.json, config/database.php, config/cache.php, .env.example, existing PublicGameData and DB privilege verifier were inspected
  - command: PR #21 final task-owned diff inspection
    result: PASS
    evidence: exactly six task-owned paths; previous task archive is an exact-content rename 0/0; contract tail truncation was detected and corrected before validation
  - command: local Composer/Pint/PHPStan/tests
    result: NOT_RUN
    evidence: current execution environment cannot resolve github.com and has no usable local Oteryn-Platform checkout; exact-head GitHub Actions is the executable validation source
  - command: GitHub Actions CI run 29695019939 (#326)
    result: PASS
    evidence: exact delivery-validation head 242b6c54f1a528406ef0e4a7c6bd94c73add5c39 passed Composer validation/install, Pint, PHPStan/Larastan level 10 and the full test suite
  - command: Agent Governance run 29695019933 (#247)
    result: PASS
    evidence: exact delivery-validation head 242b6c54f1a528406ef0e4a7c6bd94c73add5c39 passed active checkpoint validation
blockers:
  - none
next_action: Revalidate CI and Agent Governance on the final ready-checkpoint head, then perform the full PR #21 merge gate and squash-merge only if main divergence, final diff and review state remain clean.
```

## Notes

This is a bounded discovery/contract task. It does not introduce runtime integration code, new shared credentials, Canary writes, SQL runtime-table grants, or falsely claim that a best-effort diagnostic mirror is equivalent to the Redis-authoritative runtime liveness path.
