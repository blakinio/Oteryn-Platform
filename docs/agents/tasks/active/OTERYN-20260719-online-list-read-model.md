# OTERYN-20260719 Online list read model

## Goal

Implement the bounded Phase 4 cluster-wide public online-character read model through the existing dedicated query-only Canary database boundary, using the approved `cluster_sessions` + `players` contract without adding shared Canary writes or unsupported fallback authorities.

## Acceptance criteria

- [ ] Archive the merged `OTERYN-20260719-phase3-identity-closure` task record under `docs/agents/tasks/archive/` without changing its historical contents, as part of starting this real Phase 4 task.
- [ ] Add a cluster-wide online-character query through `CanaryGameDataRepository` using `cluster_sessions` joined to `players` and approved `channels` metadata only.
- [ ] Enforce `cluster_sessions.status = 'ONLINE'`, `cluster_sessions.expires_at > read_time_epoch_ms`, and `players.deletion = 0` for every positive result.
- [ ] Expose only an explicit public character allowlist plus durable `channel_id` / approved channel metadata; never expose raw account, session, instance, fencing or lease fields.
- [ ] Preserve Canary database read failure as an explicit unavailable/error HTTP result; never convert dependency failure into a synthetic empty online list.
- [ ] Do not use `players_online`, process-local `ProtocolStatus`, SQL `channel_runtime_status` as an online-identity authority, or an unbounded stale cache.
- [ ] Extend the database-enforced Canary read-only privilege boundary to the newly implemented `cluster_sessions` read surface by updating the provisioning template, privilege-verifier allowlist and regression tests in the same change.
- [ ] Add integration coverage for a fresh ONLINE lease, expired lease, non-ONLINE status, deleted player, dependency failure, and public output allowlist/no raw session fields under the isolated query-only Canary test boundary.
- [ ] Do not add any Canary/shared-data write path or modify Canary/login-server repositories.
- [ ] Run Composer validation/install, Pint, PHPStan/Larastan level 10, full tests and Agent Governance on the exact final head before readiness.

## Ownership

```yaml
owned_paths:
  - app/PublicGameData/CanaryGameDataRepository.php
  - app/Http/Controllers/PublicGameData/PublicGameDataController.php
  - app/CanaryIntegration/CanaryDatabasePrivilegeVerifier.php
  - database/provisioning/canary-readonly.sql.template
  - routes/web.php
  - resources/views/game/online.blade.php
  - tests/Feature/PublicGameData/PublicGameDataTest.php
  - tests/Unit/CanaryIntegration/CanaryDatabasePrivilegeVerifierTest.php
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260719-online-list-read-model.md
  - docs/agents/tasks/active/OTERYN-20260719-phase3-identity-closure.md
  - docs/agents/tasks/archive/OTERYN-20260719-phase3-identity-closure.md
modules:
  - PublicGameData
  - Canary integration security boundary
  - database deployment/provisioning
  - testing
  - agent-governance
dependencies:
  - OTERYN-20260718-game-read-model
  - OTERYN-20260718-online-status-discovery
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - OTERYN-20260718-db-privilege-boundary
blockers:
  - none
cross_repository_tasks:
  - blakinio/canary and login-server repositories remain read-only evidence only; no writes are authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T16:22:34+02:00
head: 6aeaf961aafbfa8e991d1b11bd9f1e9fe578d5a5
branch: task/OTERYN-20260719-online-list-read-model
pr: none
status: implementing
context_routes:
  - agent-governance
  - public-game-data
  - canary-integration
  - database
  - testing
owned_paths:
  - app/PublicGameData/CanaryGameDataRepository.php
  - app/Http/Controllers/PublicGameData/PublicGameDataController.php
  - app/CanaryIntegration/CanaryDatabasePrivilegeVerifier.php
  - database/provisioning/canary-readonly.sql.template
  - routes/web.php
  - resources/views/game/online.blade.php
  - tests/Feature/PublicGameData/PublicGameDataTest.php
  - tests/Unit/CanaryIntegration/CanaryDatabasePrivilegeVerifierTest.php
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260719-online-list-read-model.md
  - docs/agents/tasks/active/OTERYN-20260719-phase3-identity-closure.md
  - docs/agents/tasks/archive/OTERYN-20260719-phase3-identity-closure.md
proven:
  - Main was verified at 6aeaf961aafbfa8e991d1b11bd9f1e9fe578d5a5, the squash merge of PR #17, before starting this task.
  - Live GitHub search returned no open pull requests in blakinio/Oteryn-Platform before task claim.
  - The pre-existing task/OTERYN-20260719-online-list-read-model branch had ahead_by=0 and behind_by=1 relative to main, so it contained no task-owned commits; it was fast-forwarded non-forcibly to current main.
  - PROJECT_STATE and ROADMAP mark Phase 3 complete and Phase 4 in progress, with this online-list read model as the next bounded task.
  - The approved backend identity source is cluster_sessions joined to players, with mandatory status ONLINE, unexpired expires_at and players.deletion=0 filters.
  - Canary DB read failure is dependency unavailable/error and must not be represented as an empty online list.
  - players_online is rejected as a multichannel authority; ProtocolStatus is process-local; SQL channel_runtime_status is not an online-character identity authority.
  - Current PublicGameData reads through app/PublicGameData/CanaryGameDataRepository.php using Laravel query builder and the dedicated canary connection.
  - Current Canary database privilege enforcement allowlists players, guilds, guild_membership, guild_ranks and channels; implementing cluster_sessions requires synchronized provisioning/verifier/test/contract updates.
derived:
  - The smallest complete vertical slice is one public online-list route/view backed by a sanitized repository projection and synchronized database privilege expansion for cluster_sessions.
unknown:
  - Maximum production wall-clock skew relevant to exact lease freshness SLA remains unknown and is outside this task.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Reuse players_online for simplicity: rejected by the proven last-process-writer/local-channel lifecycle.
  - Treat status ONLINE without expires_at filtering as fresh: rejected because stale orphan rows may remain physically present.
  - Gate identity on SQL channel_runtime_status: rejected because it is a best-effort diagnostic mirror and introduces an independent false-negative path.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260719-online-list-read-model.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: main SHA, open PR state, required governance/project/architecture/contracts and predecessor task records inspected before implementation
  - command: local repository validation
    result: UNAVAILABLE
    evidence: no local checkout is exposed in the current execution environment; exact-head GitHub CI and Agent Governance will be used as required validation evidence
blockers:
  - none
next_action: Archive the merged Phase 3 closure record unchanged, update ACTIVE_WORK to this task, and open the draft PR before implementing the bounded online-list read model.
```

## Notes

This task is limited to the approved read-only online-character surface. It does not authorize shared Canary writes, live runtime-status transport, Admin/RBAC, credential migration, global authentication changes or production deployment work.
