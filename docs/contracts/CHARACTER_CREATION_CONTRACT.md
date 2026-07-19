# Platform-driven Character Creation Contract

## Status

`BLOCKED — OWNERSHIP AUTHORIZATION IMPLEMENTED / PRODUCT CREATION POLICY STILL MISSING`

This contract governs any future Oteryn Platform operation that creates a Canary `players` record for an authenticated user.

The original Phase 5 authorization blocker is resolved for supported greenfield accounts: an authenticated Platform Identity may target only the exact Canary `accounts.id` from its ready immutable Platform-owned binding.

Character creation itself is still not approved because the current repositories do not define the product's authoritative character naming policy, starter-state policy or exact dependent initialization/write surface.

## Evidence baseline

### Oteryn Platform

- Ownership model: `1 Platform Identity <-> 1 Canary accounts.id`.
- Greenfield account provisioning and immutable binding are implemented on `main` through PR #33 / `d5c319448737ee5badd8ab73967535a5ec9b67d1`.
- Authorization for a future character-create operation must resolve the target account exclusively from the authenticated Identity's ready `identity_canary_accounts.canary_account_id` binding.
- Client-supplied account IDs, email equality and account-name matching are not ownership proof.

### Canary

- Repository: `blakinio/canary`.
- Current inspected main: `37b41a29c8743d4c976eb7fcb82d684594722aa4`.
- The commits after the previous character/account evidence pins do not modify the inspected player schema/load/login-hook semantics relevant to this decision; the latest functional change adds E2E vocation persistence coverage.
- Access mode for this task: read-only.

## Authorization — RESOLVED

A character-create request may proceed past authorization only when:

1. the caller is an authenticated Platform Identity;
2. the Platform-owned binding row is in ready state;
3. the exact target Canary `accounts.id` is the non-null `canary_account_id` stored in that binding;
4. the operation does not accept another account ID as authoritative client input.

The future character-create service must derive the account server-side from this binding.

## Current Canary player persistence facts

### PROVEN

Current `players` schema provides:

- auto-generated `id`;
- unique `name`;
- `account_id` foreign key to `accounts.id`;
- defaults for many gameplay fields including group, level, vocation, health, look, town, skills and stamina;
- `conditions` as `MEDIUMBLOB NOT NULL` with no default.

Database uniqueness on `players.name` is the final concurrency guard for simultaneous attempts to create the same exact stored name.

Current player loading additionally requires resolvable runtime state:

- the referenced account must exist;
- the configured group ID must resolve;
- the stored vocation ID must resolve;
- town state must resolve or Canary applies its current fallback behavior;
- a login position of `(0,0,0)` is replaced with the player's temple position.

These are engine compatibility facts, not a product starter policy.

## Name policy

### Status

`BLOCKED — PRODUCT POLICY NOT DEFINED`

Current evidence proves database uniqueness of the stored `players.name`, but does not prove an Oteryn product policy for:

- minimum/maximum user-facing character name length within the database's broad `varchar(255)` capacity;
- allowed alphabet/Unicode policy;
- spaces, capitalization and punctuation;
- canonicalization/normalization before uniqueness comparison;
- reserved names and protected staff/system words;
- visually confusable names;
- whether case variants are considered the same product name before reaching the database.

No purpose-built current Canary web character-create API was proven that can be reused as an authoritative name-policy validator.

Therefore the Platform must not invent a name validator from the SQL unique constraint alone.

### Required product decision

Before implementation, an explicit product policy/ADR must define one canonical stored-name transformation and validation contract. The database unique constraint remains the final race guard after application validation.

## Starter-state policy

### Status

`BLOCKED — PRODUCT POLICY NOT DEFINED`

Schema defaults and sample/migration characters are compatibility evidence only. They are not approved Oteryn product policy.

The product must explicitly decide at minimum:

- allowed creation-time vocation or vocation-selection flow;
- allowed sex and pronoun values;
- starting town;
- starting position or deliberate `(0,0,0)` temple fallback;
- starting level and experience;
- health/healthmax;
- mana/manamax/magic level;
- capacity and soul;
- outfit/look fields;
- initial skills;
- initial conditions blob representation;
- inventory/equipment/container items;
- depot/inbox/store-inbox state if required;
- storage/quest/tutorial state;
- whether first-login scripts are expected to perform any mandatory initialization.

### Current login-hook evidence

The inspected global login event registration hook registers gameplay events but does not establish a generic new-character starter state.

The inspected global `PlayerLogin` hook handles premium-town fallback, outfit adjustment for expired premium state and channel opening. It does not prove a generic first-login starter kit or mandatory first-login initialization contract for Platform-created characters.

Therefore the Platform cannot rely on the inspected login hooks to turn an arbitrary minimally inserted player row into the intended product starter state.

## Dependent-row/write surface

### Status

`NOT FINALIZED`

Current Canary loading supports many optional/dependent player data surfaces such as inventory, depot, inbox, storage and other gameplay systems. The current evidence does not prove that all of these require creation-time rows for a valid new character, nor does it prove which subset the Oteryn product intends to initialize immediately.

Until starter policy is selected, the exact atomic write set cannot be approved.

Consequently the final dedicated character-create database grants also cannot yet be approved. The existing read-only `canary` connection and the account-only `canary_provisioning` connection must not be broadened for character creation.

A future character-create write path must use its own reviewed least-privilege credential/connection or another explicitly approved operation-specific boundary.

## Account character limit

### Status

`BLOCKED — PRODUCT POLICY NOT DEFINED`

The inspected schema establishes ownership but does not enforce a maximum number of characters per account.

Before implementation the product must explicitly define:

- maximum active/non-deleted characters per Canary account, or an explicit unlimited policy;
- whether pending/soft-deleted characters count toward the limit;
- concurrency behavior when two creates race at the limit.

The limit check, if finite, must be enforced in the same operation transaction/locking strategy as creation so concurrent requests cannot exceed it.

## Transaction, concurrency and idempotency baseline

The future implementation must satisfy all of the following regardless of final starter policy:

1. resolve the authenticated Identity's ready Canary account binding server-side;
2. fail closed if the binding is pending, conflict or absent;
3. perform all required Canary character initialization writes in one Canary transaction;
4. use database uniqueness on `players.name` as the final exact-name race guard;
5. define deterministic duplicate-name conflict behavior;
6. define account-character-limit locking if a finite limit is selected;
7. define whether a retried create request is idempotent and, if so, use a server-controlled idempotency key rather than treating any same-name row as automatic success;
8. never reassign an existing character to another account as retry recovery;
9. roll back the complete character-create transaction when any mandatory initialization write fails.

## Online/session effects

Current game-world authentication validates that the selected character belongs to the authenticated account and is not deleted/unavailable.

No contract currently claims that an already-issued character-list response dynamically refreshes after web character creation. The safe compatibility assumption is that a new character becomes visible on a subsequent authoritative character-list/login request unless a future integration proves stronger behavior.

## Cross-repository change history

### Current decision

No Canary repository change is proven necessary merely to authorize the character-create operation: ownership binding is already implemented on the Platform side and Canary already persists players in the shared schema.

No Canary change is approved by this task.

### Potential future Canary work

If the eventual product starter policy requires mandatory game-side initialization that cannot be represented safely as a bounded transactional database write, the required Canary/datapack change must be captured in a separately authorized task before character creation is implemented.

Such a task must specify:

- the exact Canary component or datapack hook to own initialization;
- an idempotent invocation/trigger contract;
- transaction or retry behavior across Platform and Canary boundaries;
- rollout order and backward compatibility;
- integration tests proving a newly created character is complete and loadable.

The Platform must not depend on an uncontracted incidental `onLogin` side effect.

## Decision

`CHARACTER CREATE IMPLEMENTATION IS NOT YET APPROVED.`

### Resolved blocker

- `Platform Identity -> exact authorized Canary accounts.id` ownership binding: **RESOLVED / IMPLEMENTED for greenfield accounts**.

### Remaining blockers

1. explicit character-name normalization/reserved-name policy;
2. explicit starter-state policy;
3. explicit account character-limit policy;
4. exact mandatory dependent initialization write set;
5. resulting operation-specific least-privilege character-create DB grants.

The nearest minimal dependency is an explicit Oteryn product decision covering items 1-3. Only after those decisions exist can the exact initialization/write contract and implementation be finalized without guessing.

`CHARACTER CREATION: BLOCKED`
