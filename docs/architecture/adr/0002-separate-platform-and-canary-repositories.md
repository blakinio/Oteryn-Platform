# ADR 0002 — Separate Oteryn Platform and Canary repositories

- Status: Accepted
- Date: 2026-07-18

## Context

Oteryn Platform and Canary have different responsibilities, languages, release cycles and operational concerns.

Combining web/application code with the game-server repository would complicate upstream Canary synchronization, CI, deployment and ownership.

## Decision

Keep:

- `blakinio/Oteryn-Platform` for web/application platform code;
- `blakinio/canary` for the game server.

Cross-repository behavior is governed through explicit documents under `docs/contracts/**` and coordinated tasks when changes are required on both sides.

Oteryn Platform agents treat Canary as read-only unless the user explicitly authorizes a separate Canary write task.

## Consequences

### Positive

- independent deployment and rollback;
- cleaner Canary upstream synchronization;
- smaller change scopes and clearer CI;
- explicit ownership and compatibility boundaries;
- web changes do not require rebuilding/restarting the game server by default.

### Negative

- shared schema/protocol changes require coordination;
- end-to-end behavior needs cross-repository testing;
- version compatibility must be documented.

## Rules

- do not silently change both repositories in one untracked workflow;
- document shared database assumptions;
- breaking integration changes require compatibility ordering and rollback thinking;
- atomic-required changes remain blocked until both sides are ready.
