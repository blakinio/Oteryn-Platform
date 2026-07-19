# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260719-phase5-ownership-binding-dependency-gate` — selects the greenfield authoritative Platform account-ownership model and narrows the next dependency to a Platform-originated Canary account creation operation contract; active on PR #29.

## Recommended next task

After PR #29 is merged, create a bounded operation-contract task for **Platform-originated Canary account creation plus immutable 1:1 ownership binding**.

The product decision is now explicit:

- Oteryn Platform is authoritative for user Identity, account lifecycle and credential policy;
- existing Canary accounts are not migration/claim inputs;
- canonical cardinality is `1 Platform Identity <-> 1 Canary accounts.id`;
- unlink and normal rebind/transfer are not supported;
- normal recovery restores the same Platform Identity and retains the same binding;
- character creation and every other user-scoped Canary mutation remain blocked until account creation plus binding are implemented and tested.

The next contract must prove exact Canary account creation fields, credential/game-login representation under the authoritative Platform model, separate least-privilege write connection/grants, cross-database failure/compensation semantics, concurrency/idempotency, audit and rollout dependency on Canary/login-server authentication integration.

## Other queued work

- Character-create implementation remains blocked until durable account ownership binding, product character-name normalization/reserved-name policy and exact starter-state rules are approved; its future write path must use a separate least-privilege Canary write credential/connection rather than broadening the existing read-only connection.
- Authoritative game-login integration is now the target architecture: current native Canary/external login-server reusable-password verification must eventually be replaced/fenced or changed to consume Platform-authorized authentication/session material. Any Canary/login-server repository changes require separate explicit authorization and rollout coordination.
- Existing-account claim/import is out of scope for the greenfield product. Adding it later requires a new explicitly approved migration/claim contract.
- Admin/RBAC identity classification and permissions remain Phase 6. Exceptional privileged transfer/recovery of a binding is not available until a dedicated contract and those controls exist.

## Recently completed

- `OTERYN-20260719-phase5-identity-canary-account-binding` — bounded Phase 5 ownership-binding discovery, merged through PR #27 as `c683e6b6e37851447aaa0701237750828d6ed23c`; no binding implementation was approved because no current side-effect-free, credential-compatible existing-account claim capability is proven. The task record is archived unchanged with blob `59abf3c86fdca19aa0bd97e90711f13607132f53` by post-merge housekeeping.
- `OTERYN-20260719-phase5-character-creation-contract` — first Phase 5 bounded character-create operation discovery, merged through PR #26 as `ab78d6ac3bc674deb0868195563b61a753d95f98`; no shared write was approved because Identity→Canary account ownership binding and product starter/name policy remain unresolved. The task record was archived unchanged by exact blob identity when the successor task started.
- `OTERYN-20260719-phase4-public-read-closure` — Phase 4 public website/read-only game-data closure, including the bounded `/online` pagination fix and regression coverage, squash-merged through PR #23 as `3c52420d35f995338818b6c2c013fa518dc2c0ca`; task record archived unchanged with blob `658b31db4627da388f08054a21dcdca8def63c88` after merge.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.