# OTERYN-YYYYMMDD-short-slug

## Goal

<bounded task goal>

## Acceptance criteria

- [ ] <criterion>

## Ownership

```yaml
owned_paths:
  - <path/glob>
modules:
  - <module>
dependencies:
  - <task/contract-or-none>
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: YYYY-MM-DDTHH:MM:SSZ
head: UNKNOWN
branch: <task-branch>
pr: none
status: investigating
context_routes:
  - <route>
owned_paths:
  - <path/glob>
proven: []
derived: []
unknown:
  - <first unresolved fact>
conflicts: []
first_failure:
  marker: none
  evidence: none
rejected_hypotheses: []
changed_paths: []
validation:
  - command: not-run
    result: NOT_RUN
    evidence: task not yet implemented
blockers:
  - none
next_action: <one concrete next step>
```

## Notes

Keep this section concise. Durable continuation state belongs in the checkpoint above. Do not paste secrets, full logs or full diffs.
