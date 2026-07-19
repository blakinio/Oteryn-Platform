# Identity to Canary Account Binding Contract

## Status

`BLOCKED — DURABLE MAPPING SHAPE UNDERSTOOD / SAFE INITIAL CLAIM CEREMONY NOT PROVEN`

This contract defines the current authorization boundary between an authenticated Oteryn Platform Identity and Canary `accounts.id`. It does not authorize account or character mutations and does not authorize a binding implementation yet.

## Evidence baseline

### Oteryn Platform

- Repository: `blakinio/Oteryn-Platform`
- Base revision at task start: `ab78d6ac3bc674deb0868195563b61a753d95f98`
- Platform Identity remains Platform-owned and separate from Canary/login-server credentials.
- The existing `canary` SQL connection is the database-enforced read-only PublicGameData boundary and does not have `SELECT` on `accounts` or `account_sessions`.

### Canary

- Repository: `blakinio/canary`
- Revision inspected: `2b6ae86539640dfc52323e9d5abbde31d6610c5f`
- Access mode: read-only

### External login-server

- Repository: `opentibiabr/login-server`
- Current inspected revision: `2612930de4d97123a397f8f2cd0d5f784094af40`
- Access mode: read-only

## Goal

A future user-scoped account or character mutation needs a server-side answer to this question:

> Which exact Canary `accounts.id`, if any, is the currently authenticated Platform Identity authorized to mutate?

The answer must come from durable trusted state established by a trustworthy account-control proof. It must never come directly from browser-supplied account IDs or from ambiguous attribute matching at mutation time.

## Current facts

### Platform Identity — PROVEN

- `identities` has Platform-owned identity/security state and no Canary account foreign key or binding record.
- Platform registration creates only the Platform Identity and Platform security audit event.
- Platform Identity password hashing and lifecycle are intentionally separate from the current Canary/login-server game credential model.

### Canary account identity — PROVEN

- `accounts.id` is the durable primary key.
- `accounts.name` is database-unique.
- `accounts.email` is indexed but not unique.
- `accounts.password` remains a shared game-login credential field with multiple current verification paths.

### Current Platform database privilege boundary — PROVEN

The dedicated read-only Canary credential is explicitly limited to:

- `players`;
- `guilds`;
- `guild_membership`;
- `guild_ranks`;
- `channels`;
- `cluster_sessions`.

It intentionally has no access to `accounts`, `accounts.password` or `account_sessions`.

## Required durable binding semantics

### DERIVED

Once ownership has been proven, the authorization result should be persisted in Platform-owned state keyed by Platform Identity and immutable Canary account ID, not recomputed from email on every request.

The durable record must not contain:

- Canary password hashes;
- plaintext game credentials;
- reusable game session keys;
- MFA secrets from either system.

### CARDINALITY — UNKNOWN

Current source does not prove product policy for whether one Platform Identity may own:

- exactly one Canary account;
- multiple Canary accounts;
- or whether one Canary account can ever transfer between Platform Identities.

Therefore no unique-index/cardinality schema is approved until product policy explicitly selects the ownership model.

At minimum, any future schema must prevent two simultaneously active bindings from authorizing different Platform Identities to mutate the same Canary account unless an explicit transfer model is designed and audited.

## Initial account-control proof options

### 1. Email equality — REJECTED

Binding by `Identity.email = accounts.email` is not safe because Canary does not enforce unique account email.

Even if the values match, equality alone does not prove that the authenticated Platform Identity controls one exact Canary account.

### 2. Client-supplied `accounts.id` — REJECTED

A browser-provided numeric account ID is an identifier, not proof of ownership.

It may be accepted only as non-authoritative input after the server independently proves control of that exact account.

### 3. Direct Platform verification of `accounts.password` — REJECTED

This would require granting the Platform access to shared credential hashes and duplicating Canary/login-server verification behavior.

Current authentication evidence already shows incompatible supported verification paths:

- current Canary native password verification accepts its custom Argon2 representation and SHA-1 fallback;
- current external login-server hashes the submitted password with SHA-1 and performs an exact database match.

A new Platform-side verifier would create another authentication authority and would broaden the current least-privilege database boundary to credential-sensitive data. This is not approved.

### 4. Reusing the normal external login-server login flow as a claim API — REJECTED

Current external login-server authentication:

- accepts one descriptor in its `Email` request field;
- queries `(email = ? OR name = ?) AND password = SHA1(submitted password)`;
- on successful authentication loads the character list;
- creates a new 24-hour `account_sessions` entry;
- returns game-login session material and character/world data.

This is a game-login flow, not a purpose-built ownership-claim proof.

Using it solely to bind a Platform Identity would have unwanted side effects by minting a reusable game session, would inherit the SHA-1-only compatibility limitation, and does not expose a dedicated externally contracted `accounts.id` claim result in the returned session structure.

Therefore normal login success is not approved as the Platform binding ceremony.

### 5. Reusing native Canary ProtocolLogin — REJECTED AS CURRENT PLATFORM BOUNDARY

Current native Canary can verify more credential forms than the external login-server, but the inspected capability is a game-client login protocol, not a purpose-built server-to-server account-ownership claim API.

No dedicated external claim endpoint was proven that returns a short-lived binding assertion for one `accounts.id` without also participating in normal login/session behavior.

The Platform must not implement or emulate the game protocol merely to claim account ownership.

### 6. New Canary account created and immediately bound by Platform — FUTURE CANDIDATE / NOT APPROVED

If a future authoritative account-creation contract allows the Platform to create a Canary account, the Platform could persist the new account ID and its own binding atomically because ownership originates in the authenticated Platform operation rather than from claiming a pre-existing account.

This path is currently blocked because:

- no Canary account-create shared write is approved;
- game credential authority/hash compatibility remains unresolved;
- exact account creation fields and rollback semantics are not approved;
- a separate least-privilege write credential does not exist.

### 7. Manual/admin binding — FUTURE RECOVERY PATH / NOT CURRENTLY AVAILABLE

A privileged manual recovery path could exist later only with:

- implemented Admin/RBAC authorization;
- mandatory confirmed MFA;
- independent account-control evidence;
- explicit audit trail;
- dual-control or equivalent policy if required by product/security policy;
- transfer/rebind safeguards.

Phase 6 Admin/RBAC does not exist yet, so this is not a current self-service or operational binding mechanism.

## Safe target claim boundary

### RECOMMENDED DESIGN DIRECTION — NOT IMPLEMENTED

The smallest clean self-service boundary for claiming an existing Canary account is a purpose-built account-control proof capability owned by the authoritative game authentication side.

A suitable future capability would:

1. accept a canonical unique account descriptor and secret through a dedicated authenticated/secured server-to-server path;
2. use the authoritative credential verifier rather than duplicating hash logic in Platform;
3. resolve exactly one `accounts.id`;
4. return a short-lived, single-use claim assertion bound to that account ID and to a specific Platform claim attempt/audience;
5. create no reusable game session and expose no stored credential hash;
6. have explicit expiry, replay protection, rate limiting and audit semantics;
7. fail closed on ambiguous account identity or unavailable authentication dependencies.

This capability does not exist in the inspected current Canary/login-server source. Implementing it would require a separately coordinated cross-repository task and deployment contract.

## Binding lifecycle requirements

Before a binding implementation can be approved, product/security policy must define:

- Identity-to-account cardinality;
- whether bindings are permanent or transferable;
- whether unlink is allowed;
- recovery when either side loses credential access;
- behavior when a Platform Identity is disabled/deleted;
- behavior when a Canary account is disabled/banned/deleted;
- concurrent duplicate claim behavior;
- idempotent replay of the same completed claim;
- audit events for claim, failed claim, unlink, transfer and recovery;
- rollback behavior if the proof succeeds but Platform persistence fails.

### CONCURRENCY BASELINE

Any future active-binding persistence must have database constraints that make conflicting ownership impossible at commit time. Application prechecks alone are insufficient.

The final unique constraints depend on the selected cardinality model, which remains `UNKNOWN`.

## Security rules

- Never authorize mutations from browser-supplied `accounts.id` alone.
- Never bind existing accounts by email equality alone.
- Never grant the Platform read access to `accounts.password` for this feature.
- Never store reusable game session keys as durable binding evidence.
- Never broaden the existing PublicGameData read-only credential to add shared-write capability.
- Keep account-control proof and durable binding creation auditable but do not log submitted secrets or credential hashes.
- A failed or unavailable proof dependency must leave no binding.

## Decision

`NO BINDING IMPLEMENTATION APPROVED`.

The durable mapping concept is valid, but current repositories do not expose a safe, side-effect-free and credential-compatible way for Oteryn Platform to prove control of an existing Canary account.

The next dependency is one of:

1. a separately coordinated authoritative account-control claim capability on the Canary/login-server authentication side; or
2. an approved future Canary account-creation flow where the authenticated Platform Identity originates the account and the binding can be established atomically at creation.

Until one of those paths is approved and implemented, Phase 5 user-scoped Canary account/character mutations must remain fail-closed.
