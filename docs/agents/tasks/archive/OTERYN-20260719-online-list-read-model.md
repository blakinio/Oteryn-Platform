# OTERYN-20260719 Online list read model

## Goal

Implement the bounded Phase 4 cluster-wide public online-character read model through the existing dedicated query-only Canary database boundary, using the approved `cluster_sessions` + `players` contract without adding shared Canary writes or unsupported fallback authorities.

## Acceptance criteria

- [x] Archive the merged `OTERYN-20260719-phase3-identity-closure` task record under `docs/agents/tasks/archive/` without changing its historical contents, as part of starting this real Phase 4 task.
- [x] Add a cluster-wide online-character query through `CanaryGameDataRepository` using `cluster_sessions` joined to `players` and approved `channels` metadata only.
- [x] Enforce `cluster_sessions.status = 'ONLINE'`, `cluster_sessions.expires_at > read_time_epoch_ms`, and `players.deletion = 0` for every positive result.
- [x] Expose only an explicit public character allowlist plus durable `channel_id` / approved channel metadata; never expose raw account, session, instance, fencing or lease fields.
- [x] Preserve Canary database read failure as an explicit unavailable/error HTTP result; never convert dependency failure into a synthetic empty online list.
- [x] Do not use `players_online`, process-local `ProtocolStatus`, SQL `channel_runtime_status` as an online-identity authority, or an unbounded stale cache.
- [x] Extend the database-enforced Canary read-only privilege boundary to the newly implemented `cluster_sessions` read surface by updating the provisioning template, privilege-verifier allowlist and regression tests in the same change.
- [x] Add integration coverage for a fresh ONLINE lease, expired lease, non-ONLINE status, deleted player, dependency failure, and public output allowlist/no raw session fields under the isolated query-only Canary test boundary.
- [x] Do not add any Canary/shared-data write path or modify Canary/login-server repositories.
- [x] Run Composer validation/install, Pint, PHPStan/Larastan level 10, full tests and Agent Governance on the exact delivery-validation head; require a fresh exact-head CI/Governance pass after this ready checkpoint before merge.

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
updated_at: 2026-07-19T16:41:00+02:00
head: 1a584d862af1802d5d0cd9d1604f1f5a7a1f93d2
branch: task/OTERYN-20260719-online-list-read-model
pr: 18
status: ready
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
  - The Phase 3 closure archive blob is c570f7194121d54f12b080019fc639cdc486da06, exactly matching the former active record blob, so historical contents are unchanged.
  - PROJECT_STATE and ROADMAP mark Phase 3 complete and Phase 4 in progress.
  - The approved backend identity source is cluster_sessions joined to players, with mandatory status ONLINE, unexpired expires_at and players.deletion=0 filters.
  - Canary DB read failure is dependency unavailable/error and must not be represented as an empty online list.
  - players_online is rejected as a multichannel authority; ProtocolStatus is process-local; SQL channel_runtime_status is not an online-character identity authority.
  - GET /online is implemented through CanaryGameDataRepository using the dedicated canary connection and selects only player id, name, level, vocation, durable channel_id and channel name.
  - Online query predicates require status ONLINE, expires_at greater than Platform read-time epoch milliseconds and players.deletion=0.
  - PublicGameData integration tests cover fresh ONLINE, expired, non-ONLINE, deleted-player, dependency-failure and raw session/account/lease field non-exposure cases after enabling SQLite query_only mode.
  - Canary database privilege enforcement, provisioning and CANARY_DATA_CONTRACT now include cluster_sessions exactly because the implemented online-list adapter reads that table.
  - The contract synchronization commit 717f2cb55fb6ec62f3381265d533514a2789911d changed only the implemented table allowlist and its privilege-boundary explanation.
  - Final changed-file inspection contains only the 13 task-owned implementation, test, contract, project-state and governance paths; temporary workflow/marker experiments are absent from the PR diff.
  - Delivery-validation head 1a584d862af1802d5d0cd9d1604f1f5a7a1f93d2 passed CI run 29691283512 (#280), including Composer metadata/lockfile validation, dependency installation, Pint, PHPStan/Larastan level 10 and the full test suite.
  - Delivery-validation head 1a584d862af1802d5d0cd9d1604f1f5a7a1f93d2 passed Agent Governance run 29691283496 (#201).
derived:
  - The bounded online-list implementation fails closed for expired leases and database dependency failure without introducing an alternate online-identity authority.
unknown:
  - Maximum production wall-clock skew relevant to exact lease freshness SLA remains unknown and is outside this task.
conflicts: []
first_failure:
  marker: AGENT_GOVERNANCE_CHECKPOINT_VALIDATION
  evidence: Agent Governance run 29690919911 (#190) failed because the task checkpoint used validation result UNAVAILABLE, while GOVERNANCE_CONTRACT.json permits only PASS, FAIL, BLOCKED or NOT_RUN; the record was corrected and later Governance runs passed.
rejected_hypotheses:
  - Reuse players_online for simplicity: rejected by the proven last-process-writer/local-channel lifecycle.
  - Treat status ONLINE without expires_at filtering as fresh: rejected because stale orphan rows may remain physically present.
  - Gate identity on SQL channel_runtime_status: rejected because it is a best-effort diagnostic mirror and introduces an independent false-negative path.
  - Keep temporary online-list contract-sync workflow or CI modifications in the final diff: rejected; all temporary workflow/marker changes were restored or removed before readiness.
changed_paths:
  - app/PublicGameData/CanaryGameDataRepository.php
  - app/Http/Controllers/PublicGameData/PublicGameDataController.php
  - app/CanaryIntegration/CanaryDatabasePrivilegeVerifier.php
  - database/provisioning/canary-readonly.sql.template
  - routes/web.php
  - resources/views/game/online.blade.php
  - tests/Feature/PublicGameData/PublicGameDataTest.php
  - tests/Unit/CanaryIntegration/CanaryDatabasePrivilegeVerifierTest.php
  - docs/contracts/CANARY_DATA_CONTRACT.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-online-list-read-model.md
  - docs/agents/tasks/active/OTERYN-20260719-phase3-identity-closure.md
  - docs/agents/tasks/archive/OTERYN-20260719-phase3-identity-closure.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: main SHA, open PR state, required governance/project/architecture/contracts and predecessor task records inspected before implementation
  - command: local composer validate/install, Pint, PHPStan/Larastan and tests
    result: NOT_RUN
    evidence: current sandbox cannot resolve github.com and has no usable local repository checkout; exact-head GitHub CI is the executable validation source
  - command: GitHub Actions CI run 29690919910 (#269)
    result: PASS
    evidence: implementation head 2fe1d33e15a766688d8c91537b23bfea564c305d passed Composer validation/install, Pint, PHPStan/Larastan level 10 and full tests
  - command: Agent Governance run 29690919911 (#190)
    result: FAIL
    evidence: checkpoint validation rejected the unsupported UNAVAILABLE result value; the record now uses the allowed NOT_RUN value instead
  - command: Agent Governance run 29691061465 (#194)
    result: PASS
    evidence: corrected checkpoint validation passed after replacing UNAVAILABLE with NOT_RUN
  - command: GitHub Actions CI run 29691283512 (#280)
    result: PASS
    evidence: exact delivery-validation head 1a584d862af1802d5d0cd9d1604f1f5a7a1f93d2 passed Composer validation/install, Pint, PHPStan/Larastan level 10 and full tests
  - command: Agent Governance run 29691283496 (#201)
    result: PASS
    evidence: exact delivery-validation head 1a584d862af1802d5d0cd9d1604f1f5a7a1f93d2 passed checkpoint validation
blockers:
  - none
next_action: Revalidate CI and Agent Governance on the final ready-checkpoint head, then perform the full PR #18 merge gate and squash-merge only if main divergence, final diff and review state remain clean.
```

## Notes

This task is limited to the approved read-only online-character surface. It does not authorize shared Canary writes, live runtime-status transport, Admin/RBAC, credential migration, global authentication changes or production deployment work.
