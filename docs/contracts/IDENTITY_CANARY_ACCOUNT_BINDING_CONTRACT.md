# Identity to Canary Account Binding Contract

## Status

`BLOCKED — NEITHER ALLOWED DIRECTION IMPLEMENTABLE / AUTH-SIDE ACCOUNT-CONTROL PROOF IS THE MINIMAL NEXT DEPENDENCY`

This contract defines the authorization boundary between an authenticated Oteryn Platform Identity and Canary `accounts.id`. It does not authorize account or character mutations, does not authorize ownership-binding persistence, and does not authorize Canary/login-server repository changes.

## Evidence baseline

### Oteryn Platform

- Repository: `blakinio/Oteryn-Platform`
- Current `main` at dependency-gate start: `282173f3eee372bed2cdbebe47aebd8dc5053eea`
- Platform Identity remains Platform-owned and separate from Canary/login-server credentials.
- The existing `canary` SQL connection remains the database-enforced read-only PublicGameData boundary.
- No durable Identity-to-Canary-account mapping exists in the current Identity model or implemented schema.

### Canary

- Repository: `blakinio/canary`
- Current inspected `main`: `183d7224cb5de57585294d72631f37783b93dc89`
- Access mode: read-only
- Comparison from the previous Phase 5 ownership-binding evidence revision `2b6ae86539640dfc52323e9d5abbde31d6610c5f` to current `main` contains no account/authentication/schema/login-session implementation changes relevant to this contract.

### External login-server

- Repository: `opentibiabr/login-server`
- Current inspected `main`: `2612930de4d97123a397f8f2cd0d5f784094af40`
- Access mode: read-only

## Required authorization answer

Every future user-scoped Canary account or character mutation must be able to answer, from trusted server-side state:

> Is the currently authenticated Platform Identity authoritatively authorized to act on this exact Canary `accounts.id`?

The answer must not depend on:

- browser-supplied `account_id`;
- email equality;
- heuristic matching;
- implicit trust;
- reusable game sessions used as durable evidence;
- Platform-side duplication of Canary/login-server credential verification;
- broader privileges on the existing read-only Canary connection.

## Current proven facts

### Platform Identity

- `Identity` contains Platform-owned identity/security state and no Canary account key or ownership relation.
- Platform registration creates Platform-owned Identity state and security audit state only.
- Platform credentials are framework-hashed and deliberately separate from current game credentials.

### Canary account identity

- `accounts.id` is the durable primary key.
- `accounts.name` is database-unique.
- `accounts.email` is indexed but not unique.
- `accounts.password` is required and remains credential-sensitive shared game-login state.
- Account creation has Canary-owned side effects, including the proven `oncreate_accounts` VIP-group trigger documented by `CANARY_DATA_CONTRACT.md`.

### Current Platform database boundary

The existing dedicated Canary SQL credential is read-only and table-allowlisted for the implemented PublicGameData surface. It must remain unchanged for ownership binding and future writes.

A future shared write, if ever approved, requires a separate least-privilege credential/connection scoped only to the approved operation contract.

## Direction 1 — Account-control proof

### Required properties

A valid account-control proof mechanism must:

1. use an authoritative Canary/login-server credential verifier;
2. resolve exactly one Canary `accounts.id`;
3. issue a short-lived assertion;
4. be single-use;
5. be replay-resistant;
6. bind the assertion to the exact `accounts.id` and a specific Platform claim attempt/audience;
7. create no reusable game session;
8. expose no stored credential hash to Platform;
9. fail closed on ambiguity, expiry, replay or unavailable auth dependencies;
10. produce auditable proof-consumption and binding events without logging secrets.

### Current evidence

- Native Canary password verification currently accepts Canary custom Argon2 verification and SHA-1 fallback.
- Current external login-server authentication computes SHA-1 and queries `(email = ? OR name = ?) AND password = ?`.
- A successful normal external login then creates a DB-backed `account_sessions` game session and returns game-login session material.
- The current authentication contract proves a native Canary short-lived single-use game-login token mechanism, but it is issued/consumed inside the game login flow and no Platform-consumable account-claim endpoint is exposed.
- No current Canary/login-server endpoint was proven that returns a purpose-built, side-effect-free, short-lived single-use `accounts.id`-bound ownership assertion for Platform.

### Decision

`BLOCKED IN CURRENT AUTHORIZED SCOPE`

Normal external login cannot be reused as ownership proof because it creates a reusable game session and has narrower SHA-1-only credential compatibility.

Platform must not read `accounts.password`, implement Canary password verification, or emulate the native game login protocol.

The minimal technical dependency is a separately authorized auth-side capability that authenticates with the authoritative verifier and issues a dedicated claim assertion satisfying the properties above.

Because current Canary native verification has broader proven credential compatibility than the current external login-server, an implementation limited to the existing SHA-1-only login-server verifier would not be a universal proof for all currently supported credential states. The cross-repository task must therefore either:

- place the claim capability on a component that can use the authoritative compatible verifier; or
- first establish verifier parity/authority explicitly.

Any implementation in `blakinio/canary` or `opentibiabr/login-server` requires separate explicit authorization. This Oteryn Platform task does not authorize those writes.

## Direction 2 — Platform-originated Canary account creation

### Required properties

A valid Platform-originated account creation flow would require:

1. an explicit Canary account-create operation contract;
2. exact allowed fields/defaults and all creation side effects;
3. proven game credential storage format and login compatibility;
4. explicit ownership rules and cardinality;
5. explicit unlink, transfer/rebind and recovery rules;
6. a separate least-privilege Canary write credential/connection;
7. transactional or compensating semantics that prevent an active binding to a nonexistent account and prevent an unowned successful account from becoming silently usable;
8. deterministic duplicate/concurrency/idempotency handling;
9. rollback/reconciliation semantics for cross-database partial failure;
10. audit events that do not expose credentials.

### Current evidence

- Canary `accounts` requires unique `name` and required `password`; `email` is not unique.
- Account insertion has Canary-owned trigger side effects.
- No Oteryn Platform account-create shared write is approved by `CANARY_DATA_CONTRACT.md`.
- No dedicated Canary write connection exists in Platform.
- Platform Laravel password hashes are not proven compatible with Canary's custom Argon representation.
- Current native Canary accepts custom Argon2 plus SHA-1 fallback, while current external login-server verifies SHA-1 only.
- Current product documentation does not define whether one Platform Identity owns exactly one Canary account or may own multiple accounts.
- Unlink, transfer/rebind and recovery policy are not defined.

### Decision

`BLOCKED`

A Platform-originated Canary account cannot be implemented safely from current evidence without making an unapproved credential-storage decision, an unapproved ownership-cardinality decision and an unapproved shared write.

The existing read-only Canary credential must not be broadened. No account creation SQL or credential hashing implementation is approved.

## Direction decision

`BOUNDED DISCOVERY / BLOCKED`

Neither allowed direction is currently ready for safe Platform implementation.

The nearest minimal dependency is **Direction 1: Account-control proof**, implemented on the authoritative authentication side under a separately authorized cross-repository task. This is selected as the next dependency because it can establish control of an existing account without first authorizing Platform to create Canary accounts, write game credentials, define account-create side effects or introduce a Canary account write credential.

This is a dependency selection, not approval to implement the external capability in the current task.

## Durable ownership model requirements

### Ownership rules

- A binding may be created only after successful authoritative account-control proof, or atomically as part of a separately approved Platform-originated account creation operation.
- The persisted binding must reference immutable Canary `accounts.id` and Platform Identity ID.
- Mutation authorization must resolve from the durable active binding, never from request-supplied account identity or email equality.
- Two different Platform Identities must not simultaneously hold active mutation authority over the same Canary account unless a future explicit transfer model deliberately defines and audits such a state.

### Cardinality

`UNKNOWN / NOT YET APPROVED`

Current repository requirements do not prove whether the intended domain policy is:

- one Platform Identity to exactly one Canary account; or
- one Platform Identity to multiple Canary accounts.

No binding schema or unique-index strategy may be implemented until this becomes an explicit domain decision.

### Unlink

`UNKNOWN / NOT YET APPROVED`

The contract must define whether user-initiated unlink is allowed and what happens to mutation authority immediately after unlink.

### Rebind / transfer

`UNKNOWN / NOT YET APPROVED`

Any rebind or transfer must require new authoritative account-control evidence and concurrency-safe revocation of the prior active authority. Silent reassignment is forbidden.

### Recovery

`UNKNOWN / NOT YET APPROVED`

Privileged recovery requires a future explicit policy and, if administrative, Phase 6 RBAC plus confirmed MFA and auditable privileged action. Manual record mapping alone is not a production ownership proof.

## Concurrency and failure baseline

Any future binding implementation must:

- enforce conflicting-ownership prevention with database constraints at commit time;
- consume account-control proof atomically/single-use so concurrent replay cannot create multiple conflicting claims;
- make duplicate idempotent completion distinguishable from conflicting ownership;
- leave no binding after proof failure, expiry, replay or dependency failure;
- define compensating/reconciliation behavior if an external proof succeeds but Platform persistence fails.

Exact unique constraints remain blocked on the unresolved cardinality decision.

## Security boundary

- Current `canary` SQL connection remains SELECT-only and unchanged.
- Platform receives no `accounts.password` access for ownership binding.
- Platform stores no reusable game session key as binding evidence.
- No Canary/login-server repository is modified by this task.
- Any future account-control proof endpoint requires a separately authorized external-repository task and deployment/secret boundary.
- Any future account creation write requires a different, operation-scoped least-privilege credential; it must not reuse or broaden the PublicGameData read credential.

## Exact blockers

### Primary technical blocker

`SAFE_ACCOUNT_CONTROL_PROOF_CAPABILITY_MISSING`

No current external capability proves control of exactly one `accounts.id` to Platform with all required short-lived, single-use, replay-resistant and no-game-session properties.

### Domain-policy blocker

`OWNERSHIP_CARDINALITY_AND_LIFECYCLE_UNDECIDED`

Cardinality, unlink, rebind/transfer and recovery rules are not explicit enough to approve durable binding persistence.

### Platform-originated account blocker

`ACCOUNT_CREATE_CREDENTIAL_AND_OPERATION_CONTRACT_NOT_APPROVED`

Credential storage compatibility, account-create transaction/failure semantics and least-privilege write infrastructure are not approved.

## Decision

`NO OWNERSHIP BINDING IMPLEMENTATION APPROVED`.

The next minimal dependency is a separately authorized **account-control proof** task on the authoritative Canary/login-server authentication side. That task must provide a purpose-built short-lived, single-use, replay-resistant `accounts.id`-bound assertion without creating a game session and without exposing credential hashes.

After that capability exists, Oteryn Platform must still obtain an explicit ownership cardinality/unlink/rebind/recovery domain decision before implementing durable binding persistence.

Until both prerequisites are satisfied and the binding itself is implemented and tested, all Phase 5 user-scoped Canary account/character mutations remain fail-closed.

**CHARACTER CREATION: BLOCKED**
