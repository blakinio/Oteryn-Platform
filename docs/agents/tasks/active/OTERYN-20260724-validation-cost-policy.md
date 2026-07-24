---
task_id: OTERYN-20260724-validation-cost-policy
coordination_id: OTS-20260724-validation-cost-policy
status: validating
branch: dudantas/validation-cost-policy
base_branch: main
created: 2026-07-24
updated: 2026-07-24
related_pr: "129"
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/agents/CONTEXT_ROUTING.md
search_first:
  - docs/agents/BUILD_TEST_MATRIX.md
optional_reads: []
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

## Acceptance criteria

- [x] Every Platform agent loads the validation matrix during startup.
- [x] Multi-step work uses focused checks during individual steps.
- [x] Heavy application/container validation is deferred to coherent milestone completion when safe.
- [x] Dependency, migration, security and deployment exceptions remain explicit.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260724-validation-cost-policy.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/agents/CONTEXT_ROUTING.md
modules:
  - agent-governance
dependencies:
  - OTS-20260724-validation-cost-policy
blockers:
  - none
cross_repository_tasks:
  - CAN-20260724-validation-cost-policy
  - OTH-20260724-validation-cost-policy
  - OTC-20260724-validation-cost-policy
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T09:05:00+02:00
head: 4c713de3dcb5e5dc78e671593b469e3f00cd5833
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
  - Exact current-head Agent Governance conclusion after the checkpoint-contract repair.
conflicts: []
first_failure:
  marker: checkpoint validation
  evidence: Agent Governance run 30073440171 failed in the Validate active task checkpoints step; application CI and the other platform workflows succeeded on the same head.
rejected_hypotheses:
  - The failure was not an application, database, auth-ticket or production-like validation failure because those workflows all succeeded on head 4c713de3dcb5e5dc78e671593b469e3f00cd5833.
changed_paths:
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/tasks/active/OTERYN-20260724-validation-cost-policy.md
validation:
  - command: exact PR patch and changed-file audit
    result: PASS
    evidence: PR 129 changes only agent governance documentation and the task record; no application, dependency, schema or deployment path changed.
  - command: application and specialized workflow review on head 4c713de3dcb5e5dc78e671593b469e3f00cd5833
    result: PASS
    evidence: CI, Game Auth Ticket Concurrency, Platform DB Outage Validation and Phase 7 Production-Like Validation all succeeded; only Agent Governance checkpoint validation failed.
blockers: []
next_action: Verify Agent Governance succeeds on the repaired checkpoint head, then merge PR 129 if review and drift gates remain clean.
```
