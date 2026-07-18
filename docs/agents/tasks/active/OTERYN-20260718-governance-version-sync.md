# OTERYN-20260718-governance-version-sync

## Goal

Version the shared checkpoint/handoff governance contract explicitly, add deterministic structural validation for active task checkpoints, and enforce it in read-only CI without coupling Oteryn Platform's repository-specific governance to Canary.

## Acceptance criteria

- [ ] A repository-local marker declares the checkpoint/handoff contract version and its shared-vs-repository-specific scope.
- [ ] `tools/agents/checkpoint.py` validates every active task checkpoint deterministically.
- [ ] Negative validator cases cover missing checkpoint, missing/duplicate `next_action`, unsupported status, and unsupported validation result.
- [ ] `.github/workflows/agent-governance.yml` validates active task records for governance-affecting changes without secrets or write permissions.
- [ ] Governance docs explain local validation, live Git/PR/CI verification boundaries, and cross-repository contract upgrade handling.
- [ ] Canary remains read-only and no full resume/context/routing stack is ported.
- [ ] Current PR head has green required checks before readiness/merge.

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
updated_at: 2026-07-18T22:00:00Z
head: 874215a0f962e8e8efd8873a2b3e58802ea141ce
branch: task/OTERYN-20260718-governance-version-sync
pr: none
status: implementing
context_routes:
  - agent-governance
  - testing
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
  - Oteryn Platform main has no claimed active task in ACTIVE_WORK.md and open PR 5 modifies PROJECT_STATE.md and ACTIVE_WORK.md but not the T4 governance-specific owned paths.
  - Canary checkpoint.py on main enforces checkpoint_version 1, supported task statuses, PASS FAIL BLOCKED NOT_RUN validation results, duplicate top-level key rejection, required fields, one non-placeholder next_action, and normalized evidence-list overlap checks.
  - Canary CONTEXT_ROUTES.json schema_version 1 versions machine-readable routing separately from the checkpoint contract.
derived:
  - The minimal shared cross-repository contract is the checkpoint and handoff structure, not complete repository governance or routing content.
unknown:
  - Required GitHub checks for the future T4 PR are not known until the PR and workflow runs exist.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Whole-governance synchronization is required: Oteryn and Canary have intentionally different repository allowlists, delivery rules, and routing scope.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260718-governance-version-sync.md
validation:
  - command: GitHub live-state inspection
    result: PASS
    evidence: main/open PR/changed-path inspection completed before branch creation
blockers:
  - none
next_action: Add the version marker, minimal validator, tests, CI workflow, and bounded governance documentation on the task branch.
```

## Notes

Follow-up only if later needed: port a bounded resume/context router after Oteryn Platform has a demonstrated continuation need; do not expand T4 to the full Canary execution-mode or routing toolchain.
