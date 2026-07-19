# Identity to Canary Account Binding Contract

## Status

`DIRECTION SELECTED — PLATFORM-ORIGINATED ACCOUNT CREATION / IMPLEMENTATION STILL BLOCKED`

This contract defines the authorization boundary between an authenticated Oteryn Platform Identity and Canary `accounts.id`.

The product is greenfield for account ownership. Oteryn Platform is the authoritative owner of user Identity, account lifecycle and credential policy. Existing Canary accounts are not migration inputs and are not claimable in Phase 5.

No account or character mutation is approved by this contract alone. The next implementation dependency is an explicit Platform-originated Canary account-creation operation contract plus the required least-privilege write boundary and game-login credential/session integration contract.

## Authoritative ownership decision

### PROVEN PRODUCT DECISION

The supported ownership model is:

`1 Platform Identity <-> 1 Canary accounts.id`

Rules:

- every supported product account originates as an Oteryn Platform Identity;
- each Platform Identity owns exactly one Canary account;
- each supported Canary account is created for and owned by exactly one Platform Identity;
- ownership is established only when the Platform-originated account creation operation durably records the exact created Canary `accounts.id`;
- existing Canary accounts are outside the supported migration/claim scope;
- email equality, client-supplied `account_id`, account name or any other browser-controlled attribute never establishes ownership;
- the durable binding is Platform-owned state and is not recomputed from Canary attributes at request time.

This decision is durable architecture and is recorded in `docs/architecture/adr/0004-authoritative-platform-account-ownership.md`.

## Lifecycle policy

### Unlink

Self-service unlink is forbidden. A supported Identity must not voluntarily detach from its single Canary account.

### Rebind / transfer

Self-service rebind and transfer are forbidden. A Canary account must not be moved between Platform Identities in normal operation.

Any future exceptional transfer requires a separate privileged recovery contract with implemented Admin/RBAC, confirmed MFA, independent evidence and audit. It is outside Phase 5.

### Recovery

Normal recovery is performed through Oteryn Platform Identity recovery. Recovery restores access to the same Identity and therefore the same immutable Canary account binding.

The Platform must not automatically create a replacement Canary account when a binding or Canary account is missing/corrupted. That state fails closed and requires a separately approved privileged recovery procedure.

### Disablement / deletion

Disabling a Platform Identity must disable Platform authorization to user-scoped operations without transferring its Canary account to another Identity.

Exact irreversible deletion semantics for the Canary account require a separate account-lifecycle operation contract and are not approved here.

## Required authorization answer

For every future user-scoped Canary operation the Platform must answer server-side:

> Does the currently authenticated Platform Identity have the active immutable binding to this exact Canary `accounts.id`?

The answer must be resolved from Platform-owned trusted state. A requested account ID may be used only as a lookup/target after it is matched against the authenticated Identity's stored binding; it is never ownership evidence by itself.

## Direction 1 — Account-control proof

### Decision

`NOT REQUIRED FOR THE PRIMARY GREENFIELD MODEL`

Current Canary/login-server still does not expose a purpose-built account-control proof endpoint that is simultaneously short-lived, single-use, replay-resistant, bound to one `accounts.id`, credential-compatible and free of game-session side effects.

Normal external login remains unsuitable as a claim API because it verifies SHA-1 and creates `account_sessions` game-session state. Native Canary supports broader password verification but exposes a game login protocol, not a dedicated server-to-server ownership claim capability.

Because existing Canary accounts are not migration inputs, Phase 5 does not block on implementing an existing-account claim ceremony. A future import/migration feature would require a new explicitly authorized cross-repository design.

## Direction 2 — Platform-originated Canary account creation

### Decision

`SELECTED PRODUCT DIRECTION`

Ownership originates from the authenticated Platform operation that creates the Canary account. The successful operation must establish the exact created `accounts.id` as the immutable binding for the creating Platform Identity.

The implementation is not yet approved because the following operation-level details remain unresolved:

- exact Canary account fields/defaults accepted for product account creation;
- exact credential representation or non-password game-login transition compatible with the authoritative Platform credential model;
- rollout order for replacing/fencing current native Canary and external login-server reusable-password paths;
- separate least-privilege Canary write credential/connection;
- cross-database transaction/saga/compensation semantics;
- duplicate, retry, idempotency and race behavior;
- audit and readiness-state semantics.

## Transaction and failure invariants

The future operation-level contract must ensure:

1. a Platform Identity is never considered game-account-ready without one durable exact Canary `accounts.id` binding;
2. no active binding can point to an account that was not successfully created for that Identity;
3. retries cannot create multiple Canary accounts for one Identity;
4. two Platform Identities cannot bind to the same Canary account;
5. a partial failure after Canary account creation but before Platform binding persistence must be recoverable deterministically through an approved saga/compensation or pending-provisioning mechanism;
6. compensation must never silently reassign an account to another Identity;
7. operation audit must not contain plaintext credentials, credential hashes or reusable game-session material.

Because Platform and Canary persistence are separate ownership/database boundaries, a single local database transaction cannot be assumed to cover both. The exact atomicity strategy must be proven by the account-creation operation contract.

## Security boundary

- Oteryn Platform is authoritative for user Identity, account lifecycle and credential policy.
- Canary remains semantic owner of game state and Canary-owned schema.
- The existing `canary` SQL connection remains SELECT-only and must not be broadened.
- Account creation must use a new dedicated least-privilege write credential/connection restricted to the approved operation surface.
- Platform must not read `accounts.password` or duplicate Canary password verification.
- Current native Canary/external login-server password verification is not the target authority model.
- Future game login must be redesigned so Canary/login-server consumes Platform-authorized authentication/session material or delegates to the Platform authority; changing those repositories requires separate authorization.

## Current evidence

### Oteryn Platform — PROVEN

- Platform Identity persistence contains no Canary account binding today.
- Platform Identity credentials are Platform-owned and framework-hashed.
- the current `canary` SQL connection is configured as the read-only `oteryn_readonly` boundary;
- current provisioning grants SELECT only on the approved PublicGameData tables and grants no access to `accounts` or `account_sessions`.

### Canary — PROVEN

At current inspected `blakinio/canary` main `183d7224cb5de57585294d72631f37783b93dc89`:

- `accounts.id` is the durable primary key;
- `accounts.name` is unique;
- `accounts.email` is indexed but not unique;
- `accounts.password` is required;
- an account insert triggers creation of default VIP-group rows;
- native password authentication accepts Canary custom Argon2 verification and SHA-1 fallback.

The three commits after the previous binding-discovery pin do not change the inspected account/auth/schema/login-session implementation paths.

### External login-server — PROVEN

At current upstream main `2612930de4d97123a397f8f2cd0d5f784094af40`:

- password authentication computes SHA-1 and queries `(email = ? OR name = ?) AND password = ?`;
- successful normal login creates a new `account_sessions` row and returns reusable game-login session material;
- therefore normal login is not a side-effect-free ownership proof and the external server is not compatible with a Platform-only modern password hash without further integration work.

## Rejected ownership mechanisms

- existing-account claim as the Phase 5 primary path — rejected because the product is greenfield and existing Canary accounts are out of scope;
- email-only binding — rejected because Canary email is not unique and equality is not ownership proof;
- client-supplied `accounts.id` — rejected because an identifier is not authorization;
- Platform verification of `accounts.password` — rejected because it duplicates credential authority and violates least privilege;
- normal external login as ownership proof — rejected because it creates reusable game-session state and is SHA-1-only;
- broadening the existing read-only Canary connection — rejected because the read boundary is database-enforced and must remain independent.

## Decision

`PLATFORM-ORIGINATED CANARY ACCOUNT CREATION IS THE SELECTED OWNERSHIP DIRECTION.`

The previous blocker around product cardinality/unlink/rebind/recovery is resolved by the greenfield authoritative Platform decision:

- cardinality: exactly 1:1;
- unlink: not supported;
- rebind/transfer: not supported in normal operation;
- recovery: recover the same Platform Identity and retain the same binding;
- exceptional transfer/recovery: future privileged contract only.

The remaining blocker is narrower: an account-creation operation-level contract and compatible future game-login authority path must be designed before any Canary account shared write is implemented.

Until account creation plus immutable binding are implemented and tested, user-scoped Canary mutations remain fail-closed.

`CHARACTER CREATION: BLOCKED`