# OTERYN-20260719 Phase 4 public read closure

## Goal

Close Phase 4 only after revalidating every public-website/read-only-game-data deliverable and exit-gate invariant against live `main`, then leave a durable handover for the next agent without starting Phase 5 shared writes speculatively.

## Acceptance criteria

- [x] Archive the merged `OTERYN-20260719-channel-runtime-availability-read-model` task under `docs/agents/tasks/archive/` with the exact historical blob unchanged.
- [x] Verify post-PR22 `main` and confirm no other open Oteryn Platform PR overlaps this closure task.
- [ ] Revalidate Phase 4 deliverables against source: public layout/navigation, homepage/server status, news display, character search/profile, highscores, guild pages, online list and bounded read/query services.
- [ ] Revalidate that caching remains intentionally absent where adding it could extend online-lease or Redis-TTL freshness beyond the proven contracts.
- [ ] Revalidate the Phase 4 exit gate: public game-data features require no Canary/shared-data writes; query paths avoid obvious N+1/mass-query patterns; public output is escaped/sanitized according to each implemented surface.
- [ ] Classify remaining public-data unknowns as blocking or non-blocking for Phase 4 completion; do not silently resolve product policy or production deployment unknowns by assumption.
- [ ] Mark Phase 4 `COMPLETE` in roadmap/project state only if the full gate passes; keep later CMS authoring/Admin/RBAC, Phase 5 shared writes, deployment and payments outside this closure.
- [ ] Update `ACTIVE_WORK.md` and the closure checkpoint as the durable handover, with exactly one concrete `next_action` and no speculative successor implementation.
- [ ] Run repository CI and Agent Governance on the delivery-validation head, then require a fresh exact-head pass after the final ready checkpoint before merge.

## Ownership

```yaml
owned_paths:
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/architecture/ROADMAP.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/agents/tasks/active/OTERYN-20260719-phase4-public-read-closure.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-read-model.md
  - docs/agents/tasks/archive/OTERYN-20260719-channel-runtime-availability-read-model.md
modules:
  - PublicGameData
  - CMS
  - architecture
  - agent-governance
dependencies:
  - OTERYN-20260719-channel-runtime-availability-read-model
  - docs/contracts/CANARY_DATA_CONTRACT.md
blockers:
  - none at task start; closure revalidation may prove a blocking Phase 4 gap
cross_repository_tasks:
  - blakinio/canary remains read-only evidence only; no writes are authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T21:40:00+02:00
head: c715163fc7a8be6589e8ac8e43980c2dcd83cff5
branch: task/OTERYN-20260719-phase4-public-read-closure
pr: none
status: investigating
context_routes:
  - agent-governance
  - architecture
  - public-game-data
  - canary-integration
  - testing
owned_paths:
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/architecture/ROADMAP.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/agents/tasks/active/OTERYN-20260719-phase4-public-read-closure.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-read-model.md
  - docs/agents/tasks/archive/OTERYN-20260719-channel-runtime-availability-read-model.md
proven:
  - Main was verified at 795ce5642eec7a69efe07e6f0037768cb0eed37e, the squash merge of PR #22, before starting this closure task.
  - Live Oteryn Platform PR search returned no open pull requests after PR #22 merged.
  - The archived runtime read-model task blob is 798e877bdd4030adf54caa6d4431cd1228907b68, exactly matching the former active task blob.
  - Phase 4 roadmap status is still IN PROGRESS on main and must not be changed to COMPLETE until source-level revalidation finishes.
  - Current public routes include news list/detail, highscores, exact-name character search/profile, guild detail, cluster-wide online list and servers/runtime status.
derived:
  - The closure should be documentation/governance only unless source-level revalidation proves a concrete Phase 4 implementation gap.
unknown:
  - whether every Phase 4 deliverable and exit-gate invariant remains satisfied on post-PR22 main
  - whether known privileged/group-hidden ranking policy and broader production cache expectations block Phase 4 completion or remain later product/operations policy
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Start Phase 5 account/character writes immediately after PR #22: rejected because Phase 4 must first be closed against its roadmap exit gate and shared writes remain operation-contract gated.
  - Add runtime/online caching during closure: rejected unless revalidation proves it is required, because current correctness depends on bounded lease/Redis TTL freshness and the roadmap says caching only after correctness/freshness policy is defined.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260719-channel-runtime-availability-read-model.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-read-model.md
  - docs/agents/tasks/active/OTERYN-20260719-phase4-public-read-closure.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: post-PR22 main, no-open-PR state and exact previous-task archive blob were verified before closure work
  - command: Phase 4 source-level revalidation
    result: NOT_RUN
    evidence: closure task has just been claimed
blockers:
  - none
next_action: Revalidate each Phase 4 roadmap deliverable and exit-gate invariant against post-PR22 main, then classify remaining unknowns before changing phase status.
```

## Notes

This task is a bounded closure/revalidation. It must not add Phase 5 shared writes, Phase 6 Admin/RBAC/CMS authoring, Phase 7 deployment work, Phase 8 payments or cross-repository Canary changes.
