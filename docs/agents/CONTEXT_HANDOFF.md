# Oteryn Platform Agent Context Handoff

## Principle

Chat history is disposable. Git state, the active task record, the live PR and deterministic evidence are the durable project state.

A continuation agent must be able to resume without reading the previous conversation.

## Governance contract version

The machine-readable marker for the checkpoint/handoff contract is `docs/agents/GOVERNANCE_CONTRACT.json`.

Current shared checkpoint contract: **version 1**.

The shared contract covers only the checkpoint/handoff structure: required checkpoint fields, supported status values, evidence-state fields (`PROVEN`, `DERIVED`, `UNKNOWN`, `CONFLICT`), supported validation results, one top-level concrete `next_action`, and the basic rule that the same normalized fact cannot occupy multiple evidence-state lists.

`blakinio/canary` is a read-only compatibility reference for this shared contract. Oteryn Platform repository allowlists, delivery policy, architecture routing, Laravel/database/security rules and other repository-specific governance remain independent and must not be mechanically synchronized with Canary.

An upgrade is required when either repository changes a shared checkpoint/handoff rule incompatibly or increments the shared checkpoint contract version. In that case, inspect Canary read-only, decide whether Oteryn adopts the new shared version or intentionally diverges, and update `GOVERNANCE_CONTRACT.json`, this document, `TASK_TEMPLATE.md`, `tools/agents/checkpoint.py` and its tests together.

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

Every substantial active task must contain a checkpoint whose `checkpoint_version` matches `shared_checkpoint_contract.version` in `docs/agents/GOVERNANCE_CONTRACT.json`:

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

Validate one checkpoint locally with:

```sh
python tools/agents/checkpoint.py docs/agents/tasks/active/<task>.md --require-checkpoint
```

Validate all active task records with:

```sh
python tools/agents/checkpoint.py --tasks docs/agents/tasks/active --require-checkpoint
```

Run validator tests with:

```sh
python tools/agents/test_checkpoint.py
```

The validator checks deterministic structure only. It does **not** verify that `head`, branch, PR state, CI status, evidence references or repository state are currently true; agents must still perform live Git/PR/CI verification.

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
