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
- [ ] All currently active task checkpoints are structurally compliant and the current PR head is ready for merge.

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
  - OTERYN-20260718-db-privilege-boundary active checkpoint on PR 7 uses unsupported validation result UNAVAILABLE; owner coordination is required before T4 can satisfy the all-active-checkpoints Definition of Done.
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-18T21:48:00Z
head: b962f8fe0883bae6cdf748b80a628af2d0253635
branch: task/OTERYN-20260718-governance-version-sync
pr: 8
status: blocked
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
  - Oteryn Platform main was d94915064e64b7cd6c02dcd91268743224289f76 when PR 8 was opened; PR 8 is mergeable and its changed-file list contains only the eight T4 governance-specific paths.
  - The shared checkpoint contract is version 1 and is pinned to read-only Canary reference commit c1c0d10ed1e758cb72728be5fe22458cd9d9e61a without claiming repository-wide governance identity.
  - tools/agents/checkpoint.py loads the machine-readable contract marker and validates required fields, checkpoint version, supported statuses, supported validation results, one top-level non-placeholder next_action, required first_failure and validation fields, and normalized evidence-state overlap.
  - tools/agents/test_checkpoint.py covers valid input plus missing checkpoint, missing next_action, duplicate next_action, unsupported status, unsupported validation result, placeholder next_action, evidence-state overlap, and wrong checkpoint version.
  - Agent Governance workflow run 29662215721 passed checkpoint-validator tests and active-task validation on PR 8 head b962f8fe0883bae6cdf748b80a628af2d0253635.
  - Existing CI workflow run 29662215710 passed Composer validation, lockfile install, Pint formatting and application tests on PR 8 head b962f8fe0883bae6cdf748b80a628af2d0253635.
  - Active PR 6 checkpoint uses only supported checkpoint contract values in the inspected live task record.
  - Active PR 7 checkpoint still uses validation result UNAVAILABLE, which is outside contract v1; coordination comment 5013042467 requested owner-side correction without modifying the other agent task record.
derived:
  - T4 implementation and its own CI are complete, but the task must remain blocked until the active PR 7 checkpoint is corrected by its owner and revalidated.
unknown: []
conflicts: []
first_failure:
  marker: active-task-checkpoint-validation
  evidence: PR 7 docs/agents/tasks/active/OTERYN-20260718-db-privilege-boundary.md validation item local checkout validation has result UNAVAILABLE
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
    evidence: 9 tests passed in local reconstructed connector workspace
  - command: python tools/agents/checkpoint.py --tasks docs/agents/tasks/active --require-checkpoint
    result: PASS
    evidence: T4 active checkpoint validated locally against contract v1 before final handoff update
  - command: GitHub Actions Agent Governance run 29662215721
    result: PASS
    evidence: checkpoint-validation job completed successfully on PR 8 head b962f8fe0883bae6cdf748b80a628af2d0253635
  - command: GitHub Actions CI run 29662215710
    result: PASS
    evidence: test job completed successfully on PR 8 head b962f8fe0883bae6cdf748b80a628af2d0253635
  - command: active PR 6 checkpoint structure inspection
    result: PASS
    evidence: live task record uses checkpoint_version 1 and supported BLOCKED validation result for unavailable local checkout
  - command: active PR 7 checkpoint structure inspection
    result: FAIL
    evidence: live task record uses unsupported validation result UNAVAILABLE; owner coordination comment 5013042467 posted
blockers:
  - Active PR 7 checkpoint must replace UNAVAILABLE with an evidence-accurate supported validation result on its own task branch.
next_action: Re-validate PR 7's active task checkpoint after its owner replaces UNAVAILABLE with a supported validation result.
```

## Notes

Follow-up only if later needed: port a bounded resume/context router after Oteryn Platform has a demonstrated continuation need; do not expand T4 to the full Canary execution-mode or routing toolchain.
