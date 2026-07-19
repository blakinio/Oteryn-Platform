# OTERYN-20260719 Phase 4 public read closure

## Goal

Close Phase 4 only after revalidating every public-website/read-only-game-data deliverable and exit-gate invariant against live `main`, then leave a durable handover for the next agent without starting Phase 5 shared writes speculatively.

## Acceptance criteria

- [x] Archive the merged `OTERYN-20260719-channel-runtime-availability-read-model` task under `docs/agents/tasks/archive/` with the exact historical blob unchanged.
- [x] Verify post-PR22 `main` and confirm no other open Oteryn Platform PR overlaps this closure task.
- [x] Revalidate Phase 4 deliverables against source: public layout/navigation, homepage/server status, news display, character search/profile, highscores, guild pages, online list and bounded read/query services.
- [x] Revalidate that caching remains intentionally absent where adding it could extend online-lease or Redis-TTL freshness beyond the proven contracts.
- [x] Revalidate the Phase 4 exit gate: public game-data features require no Canary/shared-data writes; query paths avoid obvious N+1/mass-query patterns; public output is escaped/sanitized according to each implemented surface.
- [x] Classify remaining public-data unknowns as blocking or non-blocking for Phase 4 completion; do not silently resolve product policy or production deployment unknowns by assumption.
- [x] Mark Phase 4 `COMPLETE` in roadmap/project state only after the full source-level gate passes; keep later CMS authoring/Admin/RBAC, Phase 5 shared writes, deployment and payments outside this closure.
- [x] Update `ACTIVE_WORK.md` and the closure checkpoint as the durable handover, with exactly one concrete `next_action` and no speculative successor implementation.
- [x] Run repository CI and Agent Governance on the delivery-validation head, then require a fresh exact-head pass after the final ready checkpoint before merge.

## Ownership

```yaml
owned_paths:
  - app/PublicGameData/CanaryGameDataRepository.php
  - resources/views/game/online.blade.php
  - tests/Feature/PublicGameData/OnlinePaginationTest.php
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
  - testing
dependencies:
  - OTERYN-20260719-channel-runtime-availability-read-model
  - docs/contracts/CANARY_DATA_CONTRACT.md
blockers:
  - none
cross_repository_tasks:
  - blakinio/canary remains read-only evidence only; no writes are authorized
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T22:58:00+02:00
head: 9f484fa7e6b690e12c94a4e8b9920f489c010b78
branch: task/OTERYN-20260719-phase4-public-read-closure
pr: 23
status: ready
context_routes:
  - agent-governance
  - architecture
  - public-game-data
  - canary-integration
  - testing
owned_paths:
  - app/PublicGameData/CanaryGameDataRepository.php
  - resources/views/game/online.blade.php
  - tests/Feature/PublicGameData/OnlinePaginationTest.php
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/architecture/ROADMAP.md
  - docs/architecture/MODULE_CATALOG.md
  - docs/agents/tasks/active/OTERYN-20260719-phase4-public-read-closure.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-read-model.md
  - docs/agents/tasks/archive/OTERYN-20260719-channel-runtime-availability-read-model.md
proven:
  - Main was verified at 795ce5642eec7a69efe07e6f0037768cb0eed37e, the squash merge of PR #22, before starting this closure task.
  - PR #23 is the only open Oteryn Platform pull request and owns this closure scope.
  - The archived runtime read-model task blob is 798e877bdd4030adf54caa6d4431cd1228907b68, exactly matching the former active task blob.
  - Public navigation, exact-name search/profile, published-only news, highscores, guild pages, online list and server/runtime surfaces are present on the closure branch.
  - Highscores paginate 50 rows with deterministic level-desc/name-asc ordering; guild members use joined pagination; public news paginates 10 rows with deterministic published-at/id ordering; character profile uses one bounded first-row lookup.
  - Source revalidation found one Phase 4 mass-query gap: onlineCharacters() ended with an unbounded get(). PR #23 changes it to LengthAwarePaginator with a default page size of 100 and renders Previous/Next plus page numbers.
  - A dedicated route-level regression test now proves 101 fresh online characters are split across two pages, with only the first 100 visible on page 1.
  - Public game-data reads remain on the dedicated Canary query boundary; there are zero approved direct shared-data writes and the database privilege allowlist remains SELECT-only for exactly the implemented tables.
  - Online identity still requires ONLINE status, expires_at greater than read time and deletion = 0; Canary DB failure remains HTTP 503 rather than a synthetic empty list.
  - Runtime availability still uses deterministic canary_runtime Redis keys, positive TTL and whole-snapshot fail-closed behavior, with no SQL, process-local or cache fallback.
  - Blade public output is escaped by default; the existing guild MOTD and news XSS regressions remain applicable, and the online pagination view uses escaped interpolation.
  - Delivery-validation head 3bd2a7ede415f2dc386ca1f7dc39f4d14d062e15 passed CI run #380 and Agent Governance run #301.
  - ROADMAP and PROJECT_STATE now mark Phase 4 complete on the closure branch and preserve the later policy/deployment unknowns explicitly.
derived:
  - The Phase 4 exit gate is satisfied after bounding the online list: no public game-data write access is required, obvious N+1/mass-query patterns are avoided by bounded lookups/joins/pagination, and implemented public output remains escaped or plain-text rendered.
  - Privileged/group-hidden ranking policy is a later product-policy unknown, not a blocker for the currently specified Phase 4 read-only level highscore surface.
  - Production Redis ACL/endpoint provisioning is a deployment input, not a code-level Phase 4 blocker; the application boundary remains dedicated and read-only by contract.
  - Maximum production wall-clock skew affects the exact online freshness SLA but does not invalidate fail-closed expiry filtering; it remains an operations unknown.
  - Broader cache policy is non-blocking because Phase 4 deliberately leaves caching absent rather than extending lease or Redis-TTL freshness.
unknown:
  - privileged/group-hidden character filtering policy for future public ranking policy
  - production endpoint and ACL/user provisioning details for the dedicated read-only Canary runtime Redis connection
  - maximum production wall-clock skew relevant to the exact cluster_sessions freshness SLA
  - broader production cache/staleness policy outside the proven online-lease and Redis-runtime freshness contracts
conflicts: []
first_failure:
  marker: online-unbounded-get
  evidence: CanaryGameDataRepository::onlineCharacters() used terminal get() before PR #23; closure replaced it with paginate(100) and added regression coverage
rejected_hypotheses:
  - Start Phase 5 account/character writes immediately after PR #22: rejected because Phase 4 first required explicit closure against its roadmap exit gate and shared writes remain operation-contract gated.
  - Add runtime/online caching during closure: rejected because current correctness depends on bounded lease/Redis TTL freshness and the roadmap defers caching until correctness/freshness policy is defined.
  - Treat privileged/group-hidden ranking policy as a Phase 4 blocker: rejected because no current product requirement or contract defines that filtering for the implemented level highscore, while the existing public surface remains deterministic and read-only.
changed_paths:
  - app/PublicGameData/CanaryGameDataRepository.php
  - resources/views/game/online.blade.php
  - tests/Feature/PublicGameData/OnlinePaginationTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/architecture/ROADMAP.md
  - docs/agents/tasks/active/OTERYN-20260719-phase4-public-read-closure.md
  - docs/agents/tasks/active/OTERYN-20260719-channel-runtime-availability-read-model.md
  - docs/agents/tasks/archive/OTERYN-20260719-channel-runtime-availability-read-model.md
validation:
  - command: startup source-of-truth verification
    result: PASS
    evidence: post-PR22 main at 795ce5642eec7a69efe07e6f0037768cb0eed37e, PR #23 ownership and exact previous-task archive identity verified
  - command: Phase 4 source-level deliverable and exit-gate revalidation
    result: PASS_AFTER_FIX
    evidence: all implemented public surfaces revalidated; the only concrete mass-query gap found was unbounded onlineCharacters(), now paginated at 100 rows
  - command: regression coverage for /online pagination
    result: PASS
    evidence: tests/Feature/PublicGameData/OnlinePaginationTest.php added on delivery-validation head
  - command: GitHub Actions CI #380 on 3bd2a7ede415f2dc386ca1f7dc39f4d14d062e15
    result: PASS
    evidence: Composer validation/install, Pint formatting, PHPStan/Larastan and full tests all succeeded
  - command: Agent Governance #301 on 3bd2a7ede415f2dc386ca1f7dc39f4d14d062e15
    result: PASS
    evidence: checkpoint validator tests and active task checkpoint validation succeeded
blockers:
  - none
next_action: Require CI and Agent Governance to pass on the final ready-checkpoint head of PR #23, then mark the PR ready, squash-merge it with the exact expected head, and verify main plus the absence of open pull requests.
```

## Notes

This task is a bounded closure/revalidation. It must not add Phase 5 shared writes, Phase 6 Admin/RBAC/CMS authoring, Phase 7 deployment work, Phase 8 payments or cross-repository Canary changes.
