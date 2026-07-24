---
task_id: OTERYN-20260724-validation-cost-policy
coordination_id: OTS-20260724-validation-cost-policy
status: validating
branch: dudantas/validation-cost-policy
base_branch: main
created: 2026-07-24
updated: 2026-07-24
related_pr: "129"
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

Make validation proportional to changed paths, risk and coherent project milestones. Agents perform focused checks during individual steps, defer application/container builds and broad suites until a phase is reviewable as a whole, and still validate early when dependencies, migrations, security or deployment prerequisites require it.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T08:52:00+02:00
branch: dudantas/validation-cost-policy
pr: 129
status: validating
context_routes:
  - agent-governance
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260724-validation-cost-policy.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/agents/CONTEXT_ROUTING.md
proven:
  - Every Platform agent now loads BUILD_TEST_MATRIX.md during core startup.
  - The matrix requires cheap focused checks during multi-step work and defers dependency installation, asset compilation, container builds and broad suites to coherent milestone completion.
  - Dependency/lockfile, build-tooling, framework-bootstrap, shared-contract, prerequisite-migration, container and required-artifact changes still trigger earlier heavy validation.
  - Security-sensitive behavior requires focused regression tests as soon as the behavior exists and cannot be postponed by milestone batching.
derived:
  - Platform agents can avoid repeated builds after every small step without weakening security, migration, dependency or deployment validation.
unknown:
  - Exact current-head required-check conclusions until PR 129 is marked ready and workflows complete.
conflicts: []
changed_paths:
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/tasks/active/OTERYN-20260724-validation-cost-policy.md
validation:
  - command: exact PR patch and changed-file audit
    result: PASS
    evidence: PR 129 changes only agent governance documentation and the task record; no application, dependency, schema or deployment path changed.
blockers: []
next_action: Mark PR 129 ready, inspect the exact-head required checks and merge only after the governance/documentation gates pass.
```
