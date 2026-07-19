# OTERYN-20260718-governance-version-sync

## Goal

Version the shared checkpoint/handoff governance contract explicitly, add deterministic structural validation for active task checkpoints, and enforce it in read-only CI without coupling Oteryn Platform's repository-specific governance to Canary.

## Acceptance criteria

- [x] A repository-local marker declares the checkpoint/handoff contract version and its shared-vs-repository-specific scope.
- [x] `tools/agents/checkpoint.py` validates every selected active task checkpoint deterministically.
- [x] Negative validator cases cover missing checkpoint, missing/duplicate `next_action`, unsupported status, and unsupported validation result.
- [x] `.github/workflows/agent-governance.yml` validates active task records for governance-affecting changes without secrets or write permissions.
- [x] Governance docs explain local validation, live Git/PR/CI verification boundaries, and cross-repository contract upgrade handling.
- [x] Canary remains read-only and no full resume/context/routing stack is ported.
- [x] All currently active task checkpoints inspected for T4 are structurally compliant and PR #8 is synchronized with current `main` before final-head validation.

## Ownership

```yaml
owned_paths:
  - docs/agents/GOVERNANCE_CONTRACT.json
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/tasks/TASK_TEMPLATE.md
  - docs/agents/tasks/active/OTERYN-20260718-governance-version-sync.md
  - tools/agents/checkpoint.py
  - tools/agents/test_checkpoint.py
  - .github/workflows/agent-governance.yml
modules:
  - agent-governance
dependencies:
  - blakinio/canary checkpoint/handoff contract v1 (read-only reference)
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T07:15:00Z
head: 90fbd8bc524900c3bbb9e836dceebb0c0f917841
branch: task/OTERYN-20260718-governance-version-sync
pr: 8
status: ready
context_routes:
  - agent-governance
  - testing
  - ci-repair
owned_paths:
  - docs/agents/GOVERNANCE_CONTRACT.json
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/tasks/TASK_TEMPLATE.md
  - docs/agents/tasks/active/OTERYN-20260718-governance-version-sync.md
  - tools/agents/checkpoint.py
  - tools/agents/test_checkpoint.py
  - .github/workflows/agent-governance.yml
proven:
  - PR 8 contains only the eight T4 governance-specific paths and does not modify PROJECT_STATE.md or ACTIVE_WORK.md.
  - The shared checkpoint contract is version 1 and is pinned to read-only Canary reference commit c1c0d10ed1e758cb72728be5fe22458cd9d9e61a without claiming repository-wide governance identity.
  - tools/agents/checkpoint.py loads the machine-readable contract marker and validates required fields, checkpoint version, supported statuses, supported validation results, one top-level non-placeholder next_action, required first_failure and validation fields, and normalized evidence-state overlap.
  - tools/agents/test_checkpoint.py covers valid input plus missing checkpoint, missing next_action, duplicate next_action, unsupported status, unsupported validation result, placeholder next_action, evidence-state overlap, and wrong checkpoint version.
  - Agent Governance and CI both passed on PR 8 implementation head b962f8fe0883bae6cdf748b80a628af2d0253635 before the final handover and synchronization commits.
  - PR 7 was corrected by its owner and merged to main as cf14cb611b8b9168884dd01b1372e4a24b5323c0, so its former non-compliant active checkpoint is no longer an active-task blocker.
  - Open PR 6 checkpoint_version is 1, status is ready, validation results use only PASS and BLOCKED, and it has exactly one concrete top-level next_action.
  - Temporary synchronization PR 9 merged current main cf14cb611b8b9168884dd01b1372e4a24b5323c0 into the T4 task branch without rewriting published history, producing synchronized task head 90fbd8bc524900c3bbb9e836dceebb0c0f917841.
derived:
  - The T4 coordination blocker is resolved and the implementation is ready for final-head CI validation and squash merge.
unknown: []
conflicts: []
first_failure:
  marker: active-task-checkpoint-validation
  evidence: Historical T4 blocker was PR 7 validation result UNAVAILABLE; the owner corrected/completed that task and PR 7 is now merged.
rejected_hypotheses:
  - Whole-governance synchronization is required: Oteryn and Canary have intentionally different repository allowlists, delivery rules, routing scope, and repository-specific policies.
  - Canary CONTEXT_ROUTES.json schema_version is the checkpoint contract version: routing schema versioning is separate from the shared checkpoint_version contract.
changed_paths:
  - .github/workflows/agent-governance.yml
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/GOVERNANCE_CONTRACT.json
  - docs/agents/tasks/TASK_TEMPLATE.md
  - docs/agents/tasks/active/OTERYN-20260718-governance-version-sync.md
  - tools/agents/checkpoint.py
  - tools/agents/test_checkpoint.py
validation:
  - command: python tools/agents/test_checkpoint.py
    result: PASS
    evidence: 9 tests passed in local reconstructed connector workspace and in Agent Governance GitHub Actions
  - command: python tools/agents/checkpoint.py --tasks docs/agents/tasks/active --require-checkpoint
    result: PASS
    evidence: T4 active checkpoint validated against contract v1 before this final handover update
  - command: GitHub Actions Agent Governance run 29662266003
    result: PASS
    evidence: checkpoint-validation completed successfully on PR 8 head 5b28d7b8b87b573c63be82de12e28133c3761080
  - command: GitHub Actions CI run 29662265970
    result: PASS
    evidence: CI completed successfully on PR 8 head 5b28d7b8b87b573c63be82de12e28133c3761080
  - command: active PR 6 checkpoint structure inspection
    result: PASS
    evidence: live task record uses checkpoint_version 1, supported ready status, supported PASS and BLOCKED validation results, and one concrete next_action
  - command: PR 7 coordination revalidation
    result: PASS
    evidence: PR 7 is merged and its former active checkpoint no longer blocks active-task validation
  - command: synchronize PR 8 task branch with current main
    result: PASS
    evidence: temporary PR 9 merged main cf14cb611b8b9168884dd01b1372e4a24b5323c0 into the task branch as 90fbd8bc524900c3bbb9e836dceebb0c0f917841
blockers:
  - none
next_action: After this checkpoint commit's Agent Governance and CI runs pass, mark PR 8 ready and squash-merge it.
```

## Notes

Follow-up only if later needed: port a bounded resume/context router after Oteryn Platform has a demonstrated continuation need; do not expand T4 to the full Canary execution-mode or routing toolchain.
