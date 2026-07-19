# Character Creation Integration Contract

## Status

`BLOCKED — OPERATION DISCOVERED / SHARED WRITE NOT APPROVED`

This contract defines the current evidence-backed boundary for a future Oteryn Platform-driven character creation operation. It does not authorize a shared database write.

## Evidence baseline

### Oteryn Platform

- Repository: `blakinio/Oteryn-Platform`
- `main` observed at task start: `838c11059694b5aa4cfdfb7923fcbbacc7c3e286`
- Phase 4 status: complete
- Current Phase 5 task: `OTERYN-20260719-phase5-character-creation-contract`

### Canary

- Repository: `blakinio/canary`
- Branch observed: `main`
- Commit: `2b6ae86539640dfc52323e9d5abbde31d6610c5f`
- Access mode: read-only

The previous `CANARY_DATA_CONTRACT.md` evidence pin was `d4f8bb3aa3a6ca31b54f324797078360da28f8f8`. The current Canary revision is six commits ahead. The compared changes do not modify the inspected `schema.sql`, account repository, player load/save or vocation paths used by this operation discovery, and those paths were re-read at the current revision.

## Evidence states

- `PROVEN` — directly supported by current inspected source/schema.
- `DERIVED` — follows from proven facts but is not an explicit source declaration.
- `UNKNOWN` — not established and must not be guessed.
- `CONFLICT` — authoritative evidence disagrees.

## Operation

Future candidate operation: create one Canary character owned by the Canary account that is authoritatively bound to the currently authenticated Oteryn Platform Identity.

The operation is not approved until every blocker in this document is resolved by an explicit contract.

## Ownership

### PROVEN

- Canary is the semantic owner of the `players` schema and game-side character lifecycle.
- `players.account_id` references `accounts.id` and is the durable account-to-character ownership relationship.
- Oteryn Platform currently uses a dedicated Canary SQL connection configured as the read-only `oteryn_readonly` boundary.

### REQUIRED FUTURE CALLER BOUNDARY

A future Platform character-create implementation must use a separate least-privilege write credential/connection dedicated to the approved operation. It must not broaden or repurpose the existing `canary` read-only connection.

The exact grants remain `UNKNOWN` until the final operation fields and transaction are approved.

## Authorization

### PROVEN

- The Platform `Identity` persistence currently contains Platform-owned identity/security fields and no durable `Canary accounts.id` binding.
- Platform registration creates only the Platform Identity and security audit state; it does not create or link a Canary account.
- Canary `accounts.email` is indexed but is not unique in the proven schema.
- Canary game-world authentication itself verifies character ownership by `players.account_id` and character name.

### BLOCKER — IDENTITY TO ACCOUNT OWNERSHIP

The authenticated Platform Identity cannot currently be mapped to exactly one Canary `accounts.id` through a proven durable ownership relation.

Therefore:

- a client-supplied `account_id` must never authorize character creation;
- matching a Platform Identity to a Canary account only by email is not an approved authorization rule because Canary does not enforce email uniqueness;
- character creation must remain denied until a separate contract establishes a durable, unambiguous and lifecycle-safe Identity-to-Canary-account binding.

This blocker is independent from the broader shared password/hash migration. Resolving ownership mapping must not silently migrate or replace Canary/login-server credentials.

## Character row and required persisted state

### PROVEN

At schema level:

- `players.name` is required and database-unique;
- `players.account_id` must reference an existing `accounts.id`;
- `players.conditions` is `NOT NULL` and has no schema default;
- most other player columns have schema defaults;
- `group_id` defaults to `1`;
- `level` defaults to `1`;
- `vocation` defaults to `0`;
- `town_id` defaults to `1`;
- persisted position defaults to `(0,0,0)`.

At runtime load level:

- the account must resolve;
- the configured group must resolve;
- the vocation must resolve;
- a valid town must ultimately resolve;
- persisted `(0,0,0)` is replaced with the player's temple position;
- player load/save spans gameplay-owned structures beyond the core `players` row.

Migration 55 contains a concrete `Monk Sample` insert with explicit level, vocation, health, mana, appearance, town, capacity, sex and skill values. That is sample migration data, not a generic product creation policy.

### BLOCKER — PRODUCT STARTER STATE

Schema defaults and a sample migration do not prove the intended Oteryn product character-creation state.

The following remain `UNKNOWN`:

- allowed creation-time vocation choices;
- allowed sex and pronoun choices;
- authoritative starting town and whether the Platform may rely on temple-position fallback;
- starting level, experience, health, mana, capacity, soul and magic level;
- starting outfit/look values;
- starting skills;
- starter items, inventory, inbox, depot or reward state;
- required player storage, quest or datapack initialization state;
- whether first-login hooks supply mandatory starter side effects.

No `INSERT` field list can be approved until these rules are explicitly selected and validated against the current Oteryn datapack/product policy.

## Character name

### PROVEN

- The database enforces one unique `players.name` value under the deployed table/database collation semantics.
- Current Canary ownership lookup uses the submitted name in a parameterized/escaped equality query.

### UNKNOWN

The operation contract does not yet prove:

- canonical normalization or capitalization rules;
- allowed character set and length policy narrower than the database column;
- whitespace/apostrophe/hyphen rules;
- reserved names or impersonation restrictions;
- whether application-level equivalence must be stricter than database collation equality.

### CONCURRENCY REQUIREMENT

The database unique constraint must remain the final durable uniqueness guard. A future implementation must treat a duplicate-key race as a deterministic name-conflict result rather than relying on a pre-insert availability check alone.

## Transaction and concurrency contract

### DERIVED REQUIRED SHAPE

A future approved create operation should execute one bounded database transaction that:

1. resolves the already-authorized durable Canary account binding;
2. verifies any account-level creation preconditions that the final product contract requires;
3. inserts exactly the approved initial character state and any mandatory dependent rows that must be atomic with creation;
4. commits only when all required writes succeed.

A preflight name check may improve UX but cannot replace the database unique constraint.

### UNKNOWN / BLOCKED

Until the account-binding and starter-state blockers are resolved, the contract cannot safely finalize:

- whether an account row lock is required;
- maximum characters per account, if any;
- idempotency key semantics;
- which dependent writes must share the transaction;
- retry policy after deadlock/serialization failure;
- the exact write privilege allowlist.

## Online, session and runtime effects

### PROVEN

- Character ownership is re-read from `players` during Canary account/player loading and game-world authentication.
- `cluster_sessions` represents active character runtime leases, not character ownership.

### DERIVED

A newly committed offline character does not require creation of a `cluster_sessions` row.

### UNKNOWN

No evidence in this task proves that an already-issued external character-list response or game-login authorization is dynamically refreshed after a new character is created. The safe product expectation is therefore that the character becomes available on a subsequent fresh character-list/login flow unless a later contract proves a refresh mechanism.

No cache invalidation or runtime-session mutation is approved by this contract.

## Failure, rollback and retry

- Authorization failure or missing account binding: fail closed before any shared write.
- Validation failure: fail before any shared write.
- Duplicate name at insert: rollback and return a deterministic conflict result.
- Database/dependency failure: rollback the transaction and return an explicit unavailable/error result; never report success from a partial write.
- No compensating hard delete is approved as a generic rollback mechanism.
- Retrying after an ambiguous client/network outcome requires a defined idempotency strategy before implementation.

## Explicit blockers before implementation

1. Establish a durable, unambiguous Platform Identity to Canary `accounts.id` ownership binding and its lifecycle rules.
2. Define the Oteryn product starter-state policy and exact allowed creation inputs.
3. Define character-name canonicalization and reserved-name policy.
4. Finalize transaction/idempotency/account-limit semantics from the resolved product rules.
5. Provision a separate least-privilege Canary write connection/credential with only the exact approved operation grants.
6. Add deterministic integration/security/concurrency tests before enabling the write path.

## Decision

`NO SHARED WRITE APPROVED`.

The next bounded Phase 5 task should resolve the Identity-to-Canary-account ownership binding first, because no user-scoped account or character mutation can be safely authorized without it. Character creation implementation remains blocked after that task until the product starter-state and name-policy contract is also resolved.
