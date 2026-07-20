---
task_id: OTERYN-20260720-agent-handoff-core
status: implementing
branch: docs/agent-handoff-core-20260720
base_branch: main
created: 2026-07-20
updated: 2026-07-20
related_pr: "58"
owned_paths:
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/agents/tasks/active/OTERYN-20260720-agent-handoff-core.md
  - tools/agents/resume.py
---

# Compact agent resume workflow

## Goal

Add a compact continuation prompt generator to the existing Oteryn Platform checkpoint/handoff contract without weakening its security-specific governance.

## Acceptance criteria

- existing checkpoint contract and validator remain authoritative;
- `resume.py` generates a bounded continuation prompt from the durable checkpoint;
- missing checkpoints fail closed to checkpoint reconstruction before implementation;
- security-specific handoff requirements remain unchanged;
- no application, Laravel, database, auth, runtime or deployment behavior changes;
- merge only after required exact-head checks pass.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T13:42:30Z
head: 3fb825f1383f516822850b25f4f9b5c3734bdd94
branch: docs/agent-handoff-core-20260720
pr: 58
status: validating
context_routes:
  - agent-governance
owned_paths:
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/agents/tasks/active/OTERYN-20260720-agent-handoff-core.md
  - tools/agents/resume.py
proven:
  - Oteryn Platform already has checkpoint contract version 1 and a deterministic checkpoint validator.
  - Open Phase 7 PR 56 does not overlap CONTEXT_HANDOFF.md or tools/agents/resume.py.
  - Rollout PR 58 is open against main from docs/agent-handoff-core-20260720.
derived:
  - Adding only the resume generator and documentation preserves existing repository-specific security handoff rules.
unknown:
  - Required GitHub checks on the final rollout head are not yet verified.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses: []
changed_paths:
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/agents/tasks/active/OTERYN-20260720-agent-handoff-core.md
  - tools/agents/resume.py
validation:
  - command: changed-file overlap review against open PR 56
    result: PASS
    evidence: PR 56 touches separate task/state/runbook paths
blockers: []
next_action: Verify the exact final PR head, required CI checks, review state and changed-file scope, then merge only if the repository merge gate passes.
```
