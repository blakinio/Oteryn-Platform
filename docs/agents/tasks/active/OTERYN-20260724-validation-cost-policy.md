---
task_id: OTERYN-20260724-validation-cost-policy
coordination_id: OTS-20260724-validation-cost-policy
status: implementing
branch: dudantas/validation-cost-policy
base_branch: main
created: 2026-07-24
updated: 2026-07-24
related_pr: ""
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260724-validation-cost-policy.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/agents/CONTEXT_ROUTING.md
modules_touched:
  - agent-governance
depends_on: []
blocks: []
cross_repo_tasks:
  - CAN-20260724-validation-cost-policy
  - OTH-20260724-validation-cost-policy
  - OTC-20260724-validation-cost-policy
---

# Risk-based validation policy

## Goal

Make validation proportional to changed paths and risk. Skip compilation for documentation-only and clearly non-build-affecting work, use focused checks for scripts/configuration, and reserve full build/test suites for changes that can affect compiled code, dependencies, toolchains, schemas or deployment behavior.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T08:00:00+02:00
branch: dudantas/validation-cost-policy
status: implementing
context_routes:
  - agent-governance
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260724-validation-cost-policy.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/agents/CONTEXT_ROUTING.md
proven:
  - Every Platform agent reads CONTEXT_ROUTING.md during startup.
derived:
  - Adding BUILD_TEST_MATRIX.md to core startup context makes the policy apply to every task, not only explicit CI work.
unknown: []
conflicts: []
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-validation-cost-policy.md
validation: []
blockers: []
next_action: Add BUILD_TEST_MATRIX.md, route it as core startup context, then inspect the branch diff.
```
