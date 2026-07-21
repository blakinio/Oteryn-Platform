# Game Session to Canary Contract

## Status

`TARGET CONTRACT — CANARY COMPATIBILITY ADAPTER NOT YET SELECTED`

This contract defines the required boundary between Oteryn Game Gateway authorization and Canary world-entry authentication.

It deliberately separates:

1. required target semantics;
2. proven current Canary-compatible mechanisms;
3. candidate compatibility approaches;
4. unresolved facts that block implementation claims.

No Canary or upstream login-server write is authorized by this document.

## Evidence baseline

Existing Oteryn auth discovery proves the current repository-supported landscape includes:

- native Canary password authentication;
- Canary `LoginSessionManager` short-lived single-use process-local tokens in `authType=session` mode;
- DB-backed `account_sessions` lookup/validation;
- current upstream login-server creation of `account_sessions` with a random raw session key, SHA-256 stored identifier and approximately 24-hour expiry;
- DB `account_sessions` are replayable until expiry or external deletion/revocation;
- Canary game-world authentication still checks character ownership/deletion state after session/password authentication;
- Canary account-ban/name-lock/runtime entry gates remain separate game-server checks.

The current upstream login-server source pin remains:

```text
2612930de4d97123a397f8f2cd0d5f784094af40
```

Current Canary architecture-discovery revalidation head:

```text
d760ce44c55b9aa6f01e80d2d407f6833938bdce
```

The compare delta from the original auth-contract Canary pin did not list authentication/runtime source files, but the exact selected adapter must still be revalidated against the final Canary head before implementation.

## Required target outcome

After successful Game Login Ticket redeem, Gateway must be able to authorize Canary world entry without:

- Oteryn user password;
- Canary sink password;
- Platform password hash;
- OAuth authorization code;
- OAuth access/refresh token;
- Game Login Ticket itself.

Canary receives only the game-specific credential/context required by the selected adapter.

## Logical Game Session model

Independent of storage representation, the Gateway domain treats a Game Session as logically containing:

```text
session_id
session_secret_hash
canary_account_id
world_id
created_at
expires_at
revoked_at
```

Optional future fields:

```text
channel_id
security_generation
client_protocol_profile
```

The raw session secret is returned only where the client/Canary protocol requires it and must never be logged.

## Core invariants

1. **Server generated.** Client cannot select the Game Session secret.
2. **Exact account.** Session is created only from `canary_account_id` returned by successful authoritative ticket redeem.
3. **No password fallback.** Failure to create/validate a Game Session does not fall back to user/sink password.
4. **World scoped.** Logical session is bound to one World Registry `world_id` in the target model.
5. **Bounded lifetime.** Session has explicit finite expiry.
6. **Revocable where adapter permits.** Required security events have explicit behavior; unknown behavior is not called implemented.
7. **Least privilege.** Session persistence uses a dedicated operation-specific capability, never generic Canary writes.
8. **No plaintext-at-rest by default.** Store a cryptographic hash of the presented session secret where the Canary compatibility mechanism supports it.
9. **Final Canary checks remain.** Successful session authentication does not bypass character ownership/deletion/ban/runtime admission.
10. **Retry semantics are explicit.** Ambiguous session persistence cannot create unbounded duplicate sessions silently.

## Separation from Game Login Ticket

```text
Game Login Ticket
  owner: Oteryn Identity
  purpose: authorize one Gateway login orchestration
  TTL: ~60s default
  consume: single-use atomic

Game Session
  owner: Gateway/Canary compatibility boundary
  purpose: authenticate selected world entry
  TTL: adapter/product policy
  consume/replay: adapter-specific and explicitly documented
```

A Game Login Ticket must never be stored in `account_sessions` as the Game Session credential.

## Candidate A — bounded DB `account_sessions` adapter

### Status

`CANDIDATE — NOT APPROVED UNTIL REVALIDATED AND OPERATION-SPECIFIC PRIVILEGES ARE PROVEN`

This approach attempts to reuse current Canary DB-session authentication without changing Canary code.

### Proven compatibility direction

Current evidence indicates:

- login-server generates random raw session key;
- SHA-256 of the raw key is stored in `account_sessions.id`;
- Canary hashes the presented raw session key and can resolve its account;
- Canary checks expiry;
- session is not automatically consumed on successful game authentication.

### Required adapter behavior

Gateway/session adapter would:

1. receive exact `canary_account_id` from redeemed ticket result;
2. choose authorized `world_id`;
3. generate >=256-bit random session secret;
4. compute the exact hash representation required by current Canary;
5. persist a new bounded `account_sessions` record through a dedicated least-privilege connection;
6. use a substantially shorter TTL than the current upstream login-server 24-hour default, subject to exact compatibility testing;
7. return raw session secret once to OTClient;
8. provide routing for the selected world;
9. support explicit deletion/revocation/cleanup if schema and privileges permit.

### Required proof before approval

- exact current `account_sessions` schema/constraints;
- exact current Canary lookup hash algorithm and fallback behavior;
- whether expiry precision/unit permits desired short TTL;
- whether Canary lookup is global or world-aware;
- whether multiple rows per account are permitted/expected;
- whether deletion while in use affects only future authentication or active sessions;
- exact transaction/ambiguous-commit recovery behavior;
- dedicated privilege set sufficient for INSERT and required SELECT/DELETE only;
- no trigger/side effect requiring broader privileges;
- no password/sink credential involved;
- real MariaDB integration tests against exact schema;
- exact Canary runtime E2E.

### Known limitation

Current evidence says DB `account_sessions` are replayable until expiry/deletion.

Therefore Candidate A does **not** provide the same single-use semantics as the Game Login Ticket.

Acceptance requires an explicit risk decision and bounded session lifetime/revocation policy.

If the replay window or world-scoping limitation is unacceptable, Candidate A is rejected.

## Candidate B — direct Canary one-time/bounded session integration

### Status

`FUTURE OPTION — REQUIRES SEPARATELY AUTHORIZED CANARY CHANGE IF SELECTED`

Possible direction:

- Gateway/Identity creates a shared session authorization recognized directly by Canary;
- Canary atomically consumes or validates it with explicit account/world/audience binding;
- shared store supports multiple Canary/Gateway processes;
- no direct password verification;
- deterministic revocation.

### Why current process-local LoginSessionManager is insufficient as-is for the Gateway target

Current evidence indicates its authoritative token store is process-local memory.

Without guaranteed sticky routing or shared state, a token issued outside the exact target Canary process cannot be assumed redeemable across horizontally scaled/multiworld/multichannel topology.

A future Canary task could adapt or replace this primitive, but this Platform task cannot assume such work exists.

## Candidate selection rule

Choose Candidate A only if all required safety/compatibility tests pass and the resulting replay/revocation/world semantics are accepted.

Otherwise choose Candidate B and open a separately authorized Canary task.

The selection must be recorded by updating this contract and, if architecture materially changes, ADR 0009.

## Session creation interface — Gateway domain

Gateway-facing logical interface:

```text
CreateSession(
  canary_account_id,
  world_id,
  login_attempt_id
) -> {
  session_credential,
  expires_at,
  world_route
}
```

`login_attempt_id` is a server-generated idempotency/recovery identifier, not a bearer credential.

Its exact persistence/use is adapter-specific.

## Session creation preconditions

```text
Game Login Ticket successfully redeemed
AND exact canary_account_id obtained from Identity
AND requested/selected world exists
AND world login_enabled = true
AND account is authorized for world
AND session persistence dependency is available
```

Gateway must not create a session before successful ticket redeem.

## World selection timing

Two accepted first-release patterns:

### Pattern 1 — session created before character selection

Gateway creates one session scoped to an allowed world and returns characters for that world.

Suitable for single-world MVP.

### Pattern 2 — authenticated Gateway context then session created after world/character selection

Requires an additional bounded Gateway-side selection authorization/context.

More flexible for multiworld but adds protocol/state complexity.

Initial implementation may use Pattern 1 for the single-world MVP, but the data model/API must not hard-code a permanent singleton world.

The exact chosen pattern must be reflected in OTClient/Gateway response contracts before implementation.

## Character list contract interaction

Game Session creation does not establish character ownership by itself.

Gateway character list must be loaded using exact redeemed `canary_account_id` and active/listable filtering.

At world entry, Canary must still verify that selected character:

- belongs to authenticated session account;
- is not deleted/unavailable;
- satisfies game-server admission rules.

## Canary game connection semantic input

Target semantic input:

```text
game_session_credential
selected_character_name_or_id_as_supported_by_protocol
protocol/version context
```

Canary must not require the Oteryn password in the target supported path.

The exact legacy protocol field carrying the session credential is adapter-specific and must be proven against the exact OTClient/Canary version.

## Session TTL

The final TTL is not fixed in Phase 0.

Requirements:

- finite;
- long enough for normal character selection/network connection;
- short enough to bound theft/replay risk;
- compatible with reconnect policy if reconnect is intentionally supported;
- independently configurable from the 60-second Game Login Ticket TTL.

A 24-hour default inherited blindly from upstream login-server is rejected for the target without explicit product/security justification.

## Replay semantics

### Required documentation

The selected adapter must classify Game Session as one of:

```text
single-use
bounded reusable until expiry
reusable until explicit revoke/expiry
```

It must then test exactly that behavior.

Game Login Ticket remains single-use regardless of Game Session classification.

## Revocation matrix — target decision required

| Event | Pending ticket | New Game Session issuance | Existing unconsumed/reusable Game Session | Active player connection |
|---|---|---|---|---|
| Password change | Revoke via generation | Deny until fresh auth | Target: revoke | Explicit product/Canary decision |
| Password reset | Revoke via generation | Deny until fresh auth | Target: revoke | Recommended disconnect/re-auth; exact mechanism UNKNOWN |
| MFA reset/recovery | Policy-driven revoke | Fresh auth required | Target: revoke when security-sensitive | Explicit policy |
| Identity disabled | Revoke/deny | Deny | Revoke/deny future use | Recommended deny/disconnect; exact mechanism UNKNOWN |
| Canary account banned | Ticket issuance may precheck if authoritative data available | Prefer deny | Future use must fail at Canary at minimum | Canary admission gate; immediate disconnect UNKNOWN |
| Normal logout | N/A | N/A | Product policy | Current connection ends |

The implementation phase must resolve the `UNKNOWN` cells before production-auth readiness claims.

## Ambiguous commit and idempotency

Failure case:

```text
Gateway -> session adapter INSERT
DB commits
network/driver reports ambiguous failure
Gateway retries
```

Unbounded second session creation is undesirable.

The selected adapter must provide one of:

1. server-generated unique `login_attempt_id` stored with recoverable session metadata;
2. deterministic natural/idempotency key supported by an approved Platform-owned mapping table;
3. proof that duplicate sessions are harmless and bounded, explicitly accepted by security design.

Do not guess that a failed client call means the DB transaction did not commit.

Current `account_sessions` schema may not have an idempotency column; any additional Platform-owned mapping or schema mutation requires a separate data-contract decision.

## Least-privilege persistence

If Candidate A is selected, create a dedicated connection/principal such as:

```text
canary_game_session
```

Name is illustrative until implementation.

It must receive only exact required privileges, potentially:

- INSERT on approved `account_sessions` columns;
- SELECT on approved session recovery/revocation fields;
- DELETE only where explicit revocation/cleanup requires it.

Forbidden by default:

- reading `accounts.password`;
- UPDATE/DELETE on accounts;
- player writes;
- character inventory/storage writes;
- coin/premium writes;
- ban/guild writes;
- DDL/admin privileges;
- generic Canary database access.

Effective grants must be tested against real MariaDB before approval.

## Failure behavior

| Failure | Required behavior |
|---|---|
| Session store unavailable before commit | No successful Gateway login response |
| Session commit definitely failed | No session returned |
| Session commit outcome ambiguous | Use explicit recovery/idempotency policy; never guess |
| World disabled before session create | No session for that world |
| Canary unavailable after session create | Client receives connection failure; session expiry/revocation policy handles stale credential |
| Session expired at Canary | World entry denied |
| Session revoked/deleted | Future authentication denied where adapter supports it |
| Wrong account/character | Canary denies at final ownership gate |

## Logging

Never log:

- raw Game Session credential;
- Game Login Ticket;
- password/sink credential;
- OAuth token/code.

May log bounded:

- session creation event ID/correlation ID;
- account ID only where operational policy permits;
- world ID;
- success/failure category;
- expiry duration, not secret.

## Required Candidate A tests

Before selecting DB `account_sessions` adapter:

- exact schema/hash compatibility fixture;
- raw generated secret authenticates through exact Canary version;
- wrong secret fails;
- expired session fails;
- session for account A cannot enter account B character;
- deleted character denied;
- no password involved in Gateway/Canary flow;
- shortest accepted configured TTL behaves as expected;
- concurrent/replayed use behavior measured;
- explicit deletion/revocation behavior measured;
- ambiguous commit recovery/idempotency test;
- effective least-privilege MariaDB grant test;
- forbidden privilege/read tests;
- Canary restart behavior;
- multiple Canary process/world behavior where relevant.

## Required Candidate B tests

If a direct Canary integration is selected:

- shared atomic issue/consume or validate behavior;
- concurrent replay semantics;
- account/world/audience binding;
- expiry;
- revocation generation/event behavior;
- multi-process routing;
- restart behavior;
- no password fallback;
- exact OTClient protocol compatibility;
- rollout compatibility with old/new client/server combinations.

## Rollout ordering

1. Keep legacy authentication unchanged while adapter is developed.
2. Implement/validate Gateway session adapter in controlled environment.
3. Implement OTClient candidate flow.
4. Prove cross-repository E2E.
5. Only then fence legacy password paths.

If Candidate B requires Canary changes, deploy compatible Canary support before enabling official-client Gateway flow that depends on it.

## Current unresolved decisions

`UNKNOWN` until Phase 6 discovery/implementation:

1. Candidate A vs Candidate B final selection.
2. Exact Game Session TTL.
3. Exact world-scoping enforcement in current Canary.
4. Exact reconnect semantics.
5. Active-session disconnection policy after Identity security events.
6. Session idempotency/recovery mechanism.
7. Whether a Canary code change is required.

These are deliberate blockers against premature implementation claims, not missing assumptions to be filled silently.
