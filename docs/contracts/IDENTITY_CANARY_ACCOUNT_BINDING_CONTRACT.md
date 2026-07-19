# Identity to Canary Account Binding Contract

## Status

`IMPLEMENTED FOR GREENFIELD ACCOUNTS — IMMUTABLE 1:1 PLATFORM-OWNED BINDING`

This contract defines the authorization boundary between an authenticated Oteryn Platform Identity and Canary `accounts.id`.

Oteryn Platform is the authoritative owner of user Identity, account lifecycle and credential policy. Existing Canary accounts are outside the supported Phase 5 ownership model and are not imported or claimed.

The greenfield ownership-binding implementation is delivered by the Platform-originated account provisioning flow governed by `docs/contracts/PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`.

## Authoritative ownership model

Canonical cardinality:

`1 Platform Identity <-> 1 Canary accounts.id`

Rules:

- every supported product account originates as an Oteryn Platform Identity;
- each Platform Identity has at most one durable Platform-owned provisioning/binding record;
- each ready binding references exactly one Canary `accounts.id`;
- one Canary `accounts.id` cannot be actively bound to two Platform Identities;
- ownership is established only from the exact Canary account created/recovered by the server-generated provisioning intent;
- email equality, account name, client-supplied `account_id` or other browser-controlled attributes never establish ownership;
- supported existing-account claim/import is not part of Phase 5.

ADR 0004 remains the durable product decision.

## Implemented trusted state

Platform-owned `identity_canary_accounts` persistence provides:

- `identity_id` as the one-row-per-Identity key;
- nullable unique `canary_account_id` while provisioning is pending;
- unique immutable server-generated `provisioning_name`;
- immutable `canary_creation_epoch` recovery marker;
- pending / ready / conflict lifecycle state;
- bounded failure metadata and timestamps.

A binding authorizes user-scoped Canary operations only when it is ready and contains a non-null exact `canary_account_id`.

Pending or conflict state fails closed.

## Required authorization answer

For every future user-scoped Canary operation the Platform must answer server-side:

> Does the currently authenticated Platform Identity have the ready immutable binding to this exact Canary `accounts.id`?

The answer is resolved from Platform-owned trusted persistence.

A requested account ID may be used only as a target after comparison with the authenticated Identity's stored ready binding. It is never ownership evidence by itself.

## Binding establishment

The only supported Phase 5 establishment path is Platform-originated Canary account provisioning.

The implementation:

1. creates the Platform Identity and durable pending provisioning intent before any Canary write;
2. uses a server-generated immutable provisioning name and creation marker;
3. creates or deterministically recovers the exact Canary account through the dedicated `canary_provisioning` connection;
4. stores the exact created/recovered `accounts.id` in Platform-owned binding state;
5. marks the binding ready only after durable Platform finalization;
6. treats retry of the same completed binding as idempotent;
7. enters fail-closed conflict state if recovered ownership evidence conflicts.

The implementation never accepts a client-selected Canary account ID for binding.

## Lifecycle policy

### Unlink

Self-service unlink is forbidden.

### Rebind / transfer

Self-service rebind and transfer are forbidden in normal operation.

Any future exceptional transfer requires a separately approved privileged recovery contract with Admin/RBAC, confirmed MFA, independent evidence and audit.

### Recovery

Normal account recovery restores access to the same Platform Identity and therefore retains the same immutable Canary binding.

Missing/corrupted binding or Canary-account state must fail closed. Automatic replacement-account creation or silent rebind is forbidden.

### Disablement / deletion

Disabling a Platform Identity must disable Platform authorization for user-scoped operations without transferring ownership.

Exact irreversible Canary account deletion semantics require a separate account-lifecycle contract.

## Security boundary

- Oteryn Platform remains authoritative for user credentials and ownership policy.
- Canary remains semantic owner of Canary-owned schema/game state.
- Existing `canary` / `oteryn_readonly` remains SELECT-only and unchanged.
- Account provisioning uses separate `canary_provisioning` credentials restricted to the approved column-level account-create/recovery surface.
- Platform does not read `accounts.password` and does not verify Canary passwords.
- Canary `accounts.password` receives only the non-user random sink-credential compatibility digest defined by the provisioning contract.
- Sink plaintext and digest are never stored in Platform binding/audit state or exposed to users.

## Failure and concurrency invariants

The implemented provisioning saga ensures:

- a Platform Identity is never treated as ready without a durable exact `canary_account_id` binding;
- Canary dependency failure leaves durable pending state for retry;
- a committed Canary account followed by Platform-finalization failure is recovered forward by immutable provisioning name + creation epoch;
- retries cannot authorize multiple accounts for one Identity;
- database uniqueness prevents one Canary account ID from being bound to two Identities;
- mismatched recovery evidence fails closed as conflict;
- no destructive automatic account deletion is used as partial-failure compensation.

## Validation evidence

PR #33 implementation validation includes:

- registration tests proving client-supplied `account_id` and `provisioning_name` cannot control ownership;
- saga tests for success, pending dependency failure, retry, idempotent completed state, hard conflict and binding uniqueness;
- privilege-policy tests rejecting table-level/excessive grants and password-read capability;
- real MariaDB 11.8 integration coverage proving the effective column-level provisioning grants, Canary-compatible account trigger side effects, denial of `accounts.password` reads, duplicate-free retry and forward recovery of an already committed Canary account;
- formatting and PHPStan level-10 validation.

Delivery-validation head `9d404bec37410ab1ef5c9954896f544b40963f54` passed CI run `29707658067` (#474) and Agent Governance run `29707658068` (#395).

## Existing-account proof

A side-effect-free authoritative existing-account control proof remains unimplemented, but it is not required for the greenfield product because existing Canary accounts are out of scope.

Adding import/claim later requires a new explicitly approved contract and, likely, separately authorized auth-side work.

## Game-login follow-up

Ownership binding and game-login availability are separate boundaries.

The immutable ownership binding is implemented, but Platform-originated users still require the separately authorized authoritative game-login integration recorded in `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md` before they can authenticate to the game without knowledge of the intentionally undisclosed sink credential.

Required future direction:

- `opentibiabr/login-server`: Platform-authorized short-lived cryptographic assertion/session exchange bound to exact `accounts.id`, with explicit expiry/replay/session semantics and no sink-password dependency;
- `blakinio/canary`: only if the final login contract requires stronger single-use/revocation/direct assertion/fencing semantics beyond current DB-backed session consumption; any such change requires a separate authorized task and rollout/backward-compatibility contract update.

No Canary/login-server repository was modified by the ownership-binding implementation task.

## Decision

`PLATFORM IDENTITY -> AUTHORIZED CANARY accounts.id OWNERSHIP BINDING: IMPLEMENTED FOR GREENFIELD ACCOUNTS.`

The original Phase 5 ownership-binding blocker is resolved for supported greenfield accounts.

Character creation remains blocked only by its independent operation-contract requirements: authoritative character naming policy, exact starter-state policy and approved least-privilege character-create write/initialization semantics.

`CHARACTER CREATION: BLOCKED`
