# Oteryn Platform Agent Context Handoff

## Principle

Chat history is disposable. Git state, the active task record, the live PR and deterministic evidence are the durable project state.

A continuation agent must be able to resume without reading the previous conversation.

## When to checkpoint

Update the active task record when:

- a root cause or blocker is proven;
- a hypothesis is rejected by evidence;
- files are materially modified;
- validation or CI changes task state;
- branch/head/PR state changes;
- review feedback changes required work;
- context becomes large, repetitive or unreliable;
- before session replacement or context exhaustion.

## Context pressure protocol

1. Stop broad exploration.
2. Verify current branch, head, PR and working-tree state.
3. Update the active task `## Context checkpoint`.
4. Preserve only coherent work; otherwise record uncommitted paths.
5. Record exact validation evidence.
6. Leave exactly one concrete `next_action`.

## Checkpoint schema

Every substantial active task must contain:

```yaml
checkpoint_version: 1
updated_at: YYYY-MM-DDTHH:MM:SSZ
head: <commit-sha-or-UNKNOWN>
branch: <branch>
pr: <number-or-none>
status: investigating|implementing|validating|blocked|ready
context_routes:
  - <route>
owned_paths:
  - <path/glob>
proven:
  - <fact backed by source/tool/test evidence>
derived:
  - <explicit conclusion derived from proven facts>
unknown:
  - <unresolved fact>
conflicts:
  - <authoritative evidence conflict>
first_failure:
  marker: <first unmet invariant/check or none>
  evidence: <artifact/log/test/source reference>
rejected_hypotheses:
  - <hypothesis>: <disproving evidence>
changed_paths:
  - <path>
validation:
  - command: <command/workflow/job>
    result: PASS|FAIL|BLOCKED|NOT_RUN
    evidence: <short reference>
blockers:
  - <blocker or none>
next_action: <one concrete next step>
```

Omit irrelevant historical detail. Preserve what a new agent needs to continue correctly.

## Evidence states

- `PROVEN`: directly supported by source, deterministic tool output, tests, logs, artifacts or live GitHub state.
- `DERIVED`: conclusion that follows from listed proven facts.
- `UNKNOWN`: not established. Never replace with a guess.
- `CONFLICT`: authoritative evidence disagrees and requires resolution.

## Security-specific handoff requirements

For auth, admin, database, secrets or payment-related work, the checkpoint must also state:

- trust boundary affected;
- authentication/authorization invariant affected;
- whether schema or session compatibility with Canary/login-server changes;
- whether rollback is required;
- whether any secret or production-only configuration is involved.

Never copy secrets into task records, PRs, logs or handoffs.

## Handoff quality gate

A handoff is incomplete if the next agent cannot answer:

- Which branch/PR/head is current?
- What is proven versus assumed?
- What failed first, if anything?
- Which files changed?
- What validation ran and what was the result?
- What blocker remains?
- What is the single next action?

## Anti-bloat

Do not paste full logs, full diffs, whole source files, database dumps, long chat summaries or unrelated documentation into checkpoints. Store exact references instead.
