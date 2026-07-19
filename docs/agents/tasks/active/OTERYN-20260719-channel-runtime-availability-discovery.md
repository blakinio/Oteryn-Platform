# OTERYN-20260719 Channel runtime availability discovery

## Goal

Resolve the remaining Phase 4 transport/contract uncertainty for fresh per-channel runtime availability and player counts by verifying current Canary runtime source, transport, freshness and failure semantics against live repository evidence, then approve the smallest safe Platform integration boundary or record the exact blocker without implementing speculative runtime access.

## Acceptance criteria

- [x] Archive the merged `OTERYN-20260719-public-news-read-model` task record under `docs/agents/tasks/archive/` with the exact historical blob unchanged.
- [ ] Verify current `blakinio/canary` main revision and read the repository governance/routing context required for read-only discovery.
- [ ] Inspect the current Canary `ChannelRuntimeRegistry`, `ClusterRuntime`, runtime-status payload/schema, Redis key/TTL/freshness behavior and SQL `channel_runtime_status` mirror writer semantics.
- [ ] Inspect whether Canary already exposes a purpose-built external runtime-status API/endpoint suitable for Platform consumption; do not infer one from process-local status protocol behavior.
- [ ] Inspect current Oteryn Platform runtime/dependency/config boundaries relevant to Redis or SQL runtime-status reads.
- [ ] Compare viable transport choices — direct Redis snapshot, separately contracted SQL freshness read, or purpose-built service/API — including least privilege, stale-data bounds, dependency failure semantics and operational coupling.
- [ ] Update `CANARY_DATA_CONTRACT.md` with evidence pinned to the verified current Canary revision and an explicit approved next integration boundary, or an exact blocker if no safe bounded transport can be approved.
- [ ] Update `PROJECT_STATE.md` / `ACTIVE_WORK.md` and roadmap state only as required by the discovery result; do not claim live runtime availability is implemented.
- [ ] Do not modify Canary/login-server repositories and do not add Platform runtime integration code in this discovery task.
- [ ] Run exact-head CI and Agent Governance and merge only after the full merge gate is clean.

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
  - none at task start; discovery may prove a transport blocker
cross_repository_tasks:
  - blakinio/canary is read-only evidence source; no writes are authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T18:25:00+02:00
head: 658fbe1d49405617042217f207f3fea2d9775544
branch: task/OTERYN-20260719-channel-runtime-availability-discovery
pr: none
status: investigating
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
  - Current Oteryn Platform contract already proves Canary Redis ChannelRuntimeRegistry as the fail-closed fresh per-channel runtime fast path and SQL channel_runtime_status as a best-effort asynchronous diagnostic mirror.
  - The existing contract explicitly leaves Platform transport/cache integration for per-channel fresh availability/count unresolved and separate from online-character identity.
derived:
  - The next safe step is evidence-backed transport discovery rather than selecting Redis or SQL by convenience.
unknown:
  - current live Canary main revision and whether relevant runtime-status implementation changed since the contract pinned revision
  - exact Redis key/payload/TTL and access boundary suitable for a non-Canary consumer
  - whether a purpose-built external runtime-status endpoint already exists
  - whether SQL mirror freshness semantics can support an explicitly fail-closed public availability display without overstating Redis-authoritative liveness
  - whether Oteryn Platform currently has an approved/configured Redis client boundary for Canary runtime state
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Implement SQL channel_runtime_status immediately: rejected because the existing contract marks it best-effort and the exact public freshness/failure semantics are not yet approved.
  - Reuse process-local ProtocolStatus as cluster availability: rejected because it is per-process and not an aggregate transport contract.
  - Gate the existing cluster-wide online-character identity read on runtime status: rejected because the contracts deliberately separate identity lease freshness from runtime availability.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260719-public-news-read-model.md
  - docs/agents/tasks/active/OTERYN-20260719-public-news-read-model.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-discovery.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: post-PR20 main/open-PR state and current Oteryn contract gap were verified before task claim
  - command: discovery validation
    result: NOT_RUN
    evidence: live Canary and Platform transport evidence has not yet been inspected under this task
blockers:
  - none
next_action: Open the draft PR and update ACTIVE_WORK, then verify current Canary main/governance and inspect the runtime transport implementations and Platform dependency/config boundary.
```

## Notes

This is a bounded discovery/contract task. It must not introduce speculative runtime access, new shared credentials, Canary writes, or falsely claim that a best-effort diagnostic mirror is equivalent to the Redis-authoritative runtime liveness path.
