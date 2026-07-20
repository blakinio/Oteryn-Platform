---
task_id: OTERYN-20260720-agent-required-reads
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
search_first: []
optional_reads: []
---

# Agent required-read routing rollout

## Goal

Make compact continuation prompts carry task-specific architecture, contract, security, and program reads without embedding those documents in the handover.

## Acceptance criteria

- [x] Task template exposes `required_reads`, `search_first`, and `optional_reads` metadata.
- [x] `resume.py` emits read-routing sections before evidence and `NEXT_ACTION`.
- [x] Existing tasks without frontmatter routing remain compatible through default required reads.
- [ ] Exact-head Agent Governance and CI gates pass.
- [ ] No application, Laravel, database, auth, runtime, deployment, or Canary behavior changes.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/TASK_TEMPLATE.md
  - docs/agents/tasks/active/OTERYN-20260720-agent-required-reads.md
  - tools/agents/resume.py
modules:
  - agent handoff governance
dependencies:
  - portable checkpoint resume workflow
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T19:20:00+02:00
head: b4692124f89bb814b077ed6937940fb8b2b8eab9
branch: docs/agent-required-reads-20260720
pr: none
status: implementing
context_routes:
  - agent-governance
owned_paths:
  - docs/agents/tasks/TASK_TEMPLATE.md
  - docs/agents/tasks/active/OTERYN-20260720-agent-required-reads.md
  - tools/agents/resume.py
proven:
  - Existing Oteryn Platform resume output carried checkpoint evidence but not task-specific architecture or security document reads.
  - Existing tasks without frontmatter must remain resumable.
derived:
  - Optional task-frontmatter routing preserves the existing checkpoint contract and security-specific handoff rules.
unknown:
  - Final exact-head CI and Agent Governance outcomes.
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses: []
changed_paths:
  - docs/agents/tasks/TASK_TEMPLATE.md
  - docs/agents/tasks/active/OTERYN-20260720-agent-required-reads.md
  - tools/agents/resume.py
validation:
  - command: changed-file design audit
    result: PASS
    evidence: scope limited to agent handoff tooling and task template
blockers: []
next_action: Open the draft PR, verify exact-head Agent Governance and CI, then merge only if repository gates pass.
```

## Notes

The task metadata points the next agent to authoritative repository documents; it does not copy their contents into the handover.
