# Platform-originated Canary Account Provisioning Contract

## Status

`APPROVED IMPLEMENTATION SHAPE — SHARED WRITE NOT YET IMPLEMENTED`

This contract defines the only approved Phase 5 operation for creating a Canary account for a greenfield Oteryn Platform Identity and establishing the immutable `1 Platform Identity <-> 1 Canary accounts.id` ownership binding selected by ADR 0004.

It does not implement the write. It defines the exact invariants that the next implementation task must satisfy.

## Evidence baseline

### Oteryn Platform

- Repository: `blakinio/Oteryn-Platform`
- Base revision at task start: `3b22f13ded681abf8c01b8e4fa816fdc616c7c15`
- Platform Identity registration currently commits only Platform-owned Identity and security-audit state in one Platform database transaction.
- No durable Canary account binding or account-provisioning state exists yet.
- The existing `canary` SQL connection remains the database-enforced read-only PublicGameData boundary and must not be broadened.

### Canary

- Repository: `blakinio/canary`
- Current inspected main: `2c448205d864f6388b8be932ecbb1a9e6dcaffe0`
- The two commits after the previous ownership-contract pin modify only OAM market documentation/task history and do not modify account schema/authentication paths.
- Access mode during this task: read-only.

### External login-server

- Repository: `opentibiabr/login-server`
- Current inspected main: `2612930de4d97123a397f8f2cd0d5f784094af40`
- Access mode during this task: read-only.

## Product ownership prerequisite

ADR 0004 is authoritative:

`1 Platform Identity <-> 1 Canary accounts.id`

Supported accounts are greenfield and originate only from Oteryn Platform. Existing Canary accounts are not imported or claimed. Self-service unlink, rebind and transfer are forbidden. Normal recovery restores the same Platform Identity and therefore the same immutable Canary account binding.

## Current Canary account-create surface — PROVEN

The current `accounts` table requires:

- auto-generated `id` primary key;
- unique `name` up to 32 characters;
- required `password` up to 255 characters;
- `email`, default empty string;
- all other inspected account fields have defaults.

The product account-create operation SHALL explicitly write only:

- `name`;
- `password`;
- `email`;
- `creation`.

All other account columns SHALL use the current Canary schema defaults unless a later explicit account-profile/lifecycle contract changes them.

### Account insert side effect

Current Canary schema has `oncreate_accounts`, an `AFTER INSERT` trigger that inserts the default non-customizable VIP groups:

- `Enemies`;
- `Friends`;
- `Trading Partner`.

Those trigger writes are part of the Canary-owned account-create transaction. Oteryn Platform SHALL NOT duplicate them with direct writes to `account_vipgroups`.

If the trigger fails, the Canary account-create transaction must fail and the Platform provisioning state remains not-ready.

## Internal Canary account name

The Canary `accounts.name` value for Platform-originated accounts is an internal immutable provisioning identifier, not user ownership evidence and not the Platform login name.

Approved format:

`op` + 30 lowercase hexadecimal characters generated from 15 cryptographically secure random bytes.

Properties:

- exact length: 32 characters;
- 120 bits of randomness;
- persisted in Platform-owned provisioning state before any Canary write;
- immutable after provisioning;
- never accepted from browser/client input;
- reserved by product contract for Oteryn Platform provisioning;
- used as the deterministic recovery/idempotency key for a partially completed provisioning saga.

The database unique constraint on `accounts.name` remains the final collision guard.

## Canary `accounts.password` strategy

### Decision: non-user sink credential

The current Canary schema requires `accounts.password`, while the accepted architecture makes Oteryn Platform the user credential authority and future game login must not rely on a reusable Canary password.

Until the separately authorized Platform-authorized game-login bridge is implemented, every Platform-originated Canary account SHALL receive a non-user sink credential:

1. generate at least 32 cryptographically secure random bytes;
2. encode them as a temporary plaintext secret in process memory;
3. compute the lowercase hexadecimal SHA-1 digest required by the current external login-server/native SHA-1 fallback representation;
4. insert only that digest into `accounts.password`;
5. never return, persist, audit or log the temporary plaintext secret;
6. discard the plaintext immediately after the insert attempt.

The sink secret is not the Platform Identity password and is never disclosed to the user. Oteryn Platform does not verify it and does not read `accounts.password` back.

### Security consequence

Current native Canary and external login-server password paths can authenticate a Platform-originated account only with knowledge of the random sink plaintext. Because the plaintext is generated with at least 256 bits of entropy and is deliberately never persisted or exposed, those legacy password paths do not provide a usable alternate user authentication path.

SHA-1 is used here only as a compatibility representation for an unreachable random sink secret, not as the password hashing policy for user credentials. Platform Identity credentials remain framework-hashed and authoritative.

Any code path that accidentally logs or returns the sink plaintext is a security defect and must fail review.

## Platform-owned provisioning/binding state

The next implementation SHALL add one Platform-owned durable provisioning/binding record per Identity. The logical state must contain at least:

- `identity_id` — unique and non-null; deletion restricted until an explicit account-deletion lifecycle exists;
- `canary_account_id` — nullable while pending, unique when present;
- `provisioning_name` — unique immutable 32-character internal Canary account name generated before the external write;
- `canary_creation_epoch` — immutable Unix timestamp chosen before the external write and written to `accounts.creation`;
- readiness state derivable as `canary_account_id IS NOT NULL` or an equivalent explicit state with the same invariant;
- `ready_at` or equivalent completion timestamp;
- normal timestamps required for operations/audit support.

The record MUST be created in the Platform database before the first Canary insert attempt.

Database constraints MUST enforce:

- at most one provisioning/binding record per Platform Identity;
- at most one Platform binding for a Canary `accounts.id`;
- globally unique `provisioning_name`.

A Platform Identity with no non-null bound `canary_account_id` is **not game-account-ready** and MUST fail closed for every user-scoped Canary operation.

## Provisioning saga

Platform and Canary persistence are separate database boundaries. The implementation MUST NOT pretend that one Laravel local transaction atomically spans both databases.

Approved saga:

### Step 1 — durable Platform intent

Inside one Platform database transaction:

1. create or lock the Platform Identity provisioning/binding record;
2. if no record exists, generate and persist `provisioning_name` and `canary_creation_epoch`;
3. commit the pending provisioning intent before any Canary write.

Registration may create the Identity and pending provisioning intent in the same Platform transaction.

### Step 2 — Canary transaction

Using the dedicated provisioning connection:

1. begin a Canary database transaction;
2. attempt to insert `accounts(name, password, email, creation)` using:
   - persisted `provisioning_name`;
   - one newly generated sink-credential SHA-1 digest;
   - `email = ''`;
   - persisted `canary_creation_epoch`;
3. allow the Canary `oncreate_accounts` trigger to create default VIP groups;
4. select `id`, `name` and `creation` for the exact persisted `provisioning_name`;
5. require exact `name` and `creation` match;
6. commit only if the account row and trigger side effects succeed.

The operation SHALL NOT read `accounts.password`.

### Step 3 — durable Platform binding

Inside a new Platform database transaction:

1. lock the Identity provisioning/binding row;
2. if `canary_account_id` is null, set it to the recovered/created Canary account ID and mark ready;
3. if it already equals that same Canary account ID, treat the retry as idempotent success;
4. if it contains a different ID, fail closed as an ownership conflict;
5. emit the successful provisioning audit event.

## Retry and partial-failure semantics

### Canary unavailable before insert

- no Canary account exists;
- Platform record remains pending;
- retry reuses the same `provisioning_name` and `canary_creation_epoch`.

### Canary insert or trigger fails

- Canary transaction rolls back;
- Platform record remains pending;
- retry is allowed.

### Canary commit succeeds, Platform finalization fails

- do not delete the Canary account as automatic compensation;
- retry uses the persisted random `provisioning_name` to find the account;
- require the stored `creation` value to equal the persisted `canary_creation_epoch`;
- finalize the same binding.

This forward-recovery rule avoids destructive compensation after Canary-owned trigger side effects have committed.

### Duplicate `accounts.name`

For the persisted provisioning name, a duplicate on retry is not automatically treated as a second account-create request. The worker must select the existing row by exact name and require matching `creation` before recovering its ID.

If `creation` does not match, provisioning enters a hard conflict state and no binding is created. Operators must investigate; the implementation must not silently generate a new name after an ambiguous committed external state.

### Concurrent workers for one Identity

- the Platform unique `identity_id` constraint and row locking serialize ownership finalization;
- both workers reuse the same persisted provisioning name;
- Canary unique `accounts.name` prevents two rows for the same provisioning intent;
- finalization with the same recovered Canary ID is idempotent;
- a different ID is a hard conflict.

### Two Identities

Two different Platform Identities receive independently generated provisioning names. The unique `canary_account_id` binding constraint prevents one Canary account from being authorized by both identities.

## Dedicated least-privilege Canary connection

The existing `canary` / `oteryn_readonly` connection remains unchanged.

The next implementation SHALL introduce a separate connection, conceptually `canary_provisioning`, with a separate secret and database principal.

Required privilege surface is limited to the account-provisioning operation. Target grants:

- column-level `INSERT` on `accounts(name, password, email, creation)`;
- column-level `SELECT` on `accounts(id, name, creation)`.

No grant is approved for:

- `UPDATE` or `DELETE` on `accounts`;
- reading `accounts.password`;
- `account_sessions`;
- `players` writes;
- guild, bans, coins or other Canary tables;
- direct `account_vipgroups` writes;
- schema migration/DDL;
- GRANT OPTION or administrative privileges.

The deployment privilege verifier must fail closed if effective privileges exceed or do not satisfy the approved provisioning surface.

The current Canary trigger is expected to own its `account_vipgroups` side effects. Deployment/integration validation must prove that an account insert performed by the dedicated principal successfully executes the trigger without granting direct Platform application writes to `account_vipgroups`.

## Authorization rule

Only an authenticated Platform Identity may initiate or observe provisioning for its own Identity record.

The operation never accepts a target Canary `accounts.id` from the browser. The `accounts.id` is discovered only from the Canary row created/recovered using the server-generated persisted provisioning name.

Once ready, all future user-scoped Canary operations authorize against the Platform-owned immutable binding, never against email, account name or browser-supplied identifiers.

## Audit

Required security events, names may follow existing recorder conventions:

- provisioning requested/pending;
- provisioning completed with Identity ID and Canary account ID references;
- provisioning failed with a bounded non-secret failure code;
- provisioning hard conflict.

Never audit/log:

- Platform plaintext passwords;
- Platform password hashes;
- sink plaintext secrets;
- sink SHA-1 values;
- reusable game session material.

## Required implementation tests

The implementation task must cover at minimum:

1. one Identity creates one pending provisioning record;
2. successful Canary insert produces one exact binding;
3. retry after successful finalization is idempotent;
4. two concurrent attempts for one Identity cannot create two authorized accounts;
5. unique constraints prevent two Identities from binding one Canary account;
6. Canary connection failure leaves pending state and no ready binding;
7. trigger/insert failure leaves pending state;
8. Canary commit followed by Platform finalization failure recovers the same account by provisioning name and creation epoch;
9. mismatched recovered `creation` fails closed;
10. user/client cannot choose `accounts.id`, provisioning name or sink credential;
11. existing read-only Canary connection still cannot write;
12. provisioning credential cannot read `accounts.password` or write unrelated tables;
13. logs/audit do not contain sink plaintext/hash or Platform credentials.

Exact MySQL/MariaDB integration coverage is required for the grant boundary and trigger behavior; a mocked or SQLite-only test is insufficient for those database privilege/trigger assertions.

## Cross-repository follow-up history

No Canary/login-server repository modification is required to create the account and immutable ownership binding under this contract.

A separate future game-login integration task **is required before Platform-originated users can log into the game through the authoritative Platform credential model**.

### Required `opentibiabr/login-server` direction

A separately authorized task should add a Platform-authorized login/session exchange that:

- accepts a cryptographically authenticated, short-lived Platform assertion rather than a Canary reusable password;
- binds the assertion to one exact Canary `accounts.id` and intended login audience;
- validates expiry and replay/idempotency semantics;
- loads the account/character list by the asserted account ID;
- creates only the game-session material required by the approved login contract;
- never requires or exposes the sink credential;
- does not fall back to email-only ownership inference.

The exact session TTL, replay/single-use semantics and revocation behavior must be defined before implementation.

### Potential `blakinio/canary` follow-up

Current Canary can already load DB-backed `account_sessions` for game authentication, so this provisioning contract does not prove that a Canary code change is required for the first Platform-authorized login bridge.

If the final login contract requires single-use session consumption, stronger revocation, Platform-signed assertion verification directly in Canary, or fencing/removing alternate login endpoints, those are explicit future `blakinio/canary` changes and require a separately authorized cross-repository task. The task must update `AUTH_GAME_LOGIN_CONTRACT.md` and define rollout/backward-compatibility before modifying Canary.

## Decision

`PLATFORM-ORIGINATED CANARY ACCOUNT PROVISIONING CONTRACT: APPROVED FOR IMPLEMENTATION.`

The ownership/cardinality question is resolved and the account-create operation now has an explicit least-privilege, idempotent, forward-recoverable contract that does not make Canary passwords user credentials.

The next task may implement Platform-owned provisioning/binding persistence and the dedicated Canary account-create write path exactly within this contract.

Character creation remains blocked until that implementation is complete and tested, and remains independently subject to the character-name/starter-state blockers in `CHARACTER_CREATION_CONTRACT.md`.

`CHARACTER CREATION: BLOCKED`
