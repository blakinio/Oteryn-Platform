# Oteryn Platform Project State

This file is the compact authoritative entry point for "where are we now?". It is not a replacement for live Git/PR/task verification.

## Last architecture-state update

2026-07-20

## Current phase

- **Phase 0 — Architecture and agent bootstrap: COMPLETE**
- **Phase 1 — Laravel application bootstrap: COMPLETE**
- **Phase 2 — Canary/login authentication discovery for current implementation boundaries: COMPLETE**
- **Phase 3 — Identity foundation: COMPLETE**
- **Phase 4 — Public website and read-only game data: COMPLETE**
- **Phase 5 — Account and character management: COMPLETE**
- **Phase 6 — CMS, Admin, RBAC and Audit: NEXT / PLANNED**

## Current architecture state

Oteryn Platform is a Laravel 13 / PHP 8.5 modular monolith with Platform-owned Identity and application persistence.

Platform Identity owns supported user identity, account ownership policy and user credentials.

Supported game accounts are greenfield only:

`1 Platform Identity <-> 1 Canary accounts.id`

Existing Canary accounts are not imported or claimed.

## Implemented Identity boundary

- registration and secure Platform web login/logout;
- revocable web-session generation;
- password recovery/change with Platform session revocation;
- opt-in TOTP MFA and single-use recovery codes;
- security event recording;
- reusable `mfa.confirmed` gate for future privileged routes;
- administrator classification and RBAC remain Phase 6 work.

Platform web authentication does not imply that current native Canary/external login-server paths already enforce Platform credential policy.

## Implemented public/read-only boundary

- public Blade site shell and news;
- character search/profile and level highscores;
- guild detail/membership;
- cluster-wide online-character list;
- configured channels with fresh runtime availability projection;
- database-enforced `canary` / `oteryn_readonly` SELECT-only SQL boundary;
- separate read-only `canary_runtime` Redis boundary.

## Implemented Phase 5 account ownership

The immutable Platform-owned binding is implemented through durable `identity_canary_accounts` state.

A user-scoped Canary operation is authorized only from the authenticated Identity's ready exact `canary_account_id` binding. Pending/conflict state fails closed. Browser-supplied account identifiers, account names and email equality are not ownership evidence.

Self-service unlink/rebind/transfer is forbidden. Normal recovery restores the same Platform Identity and retains the same binding.

## Implemented Phase 5 shared writes

Phase 5 approves exactly two Oteryn Platform -> Canary mutation surfaces.

### 1. Greenfield Canary account provisioning

Connection: `canary_provisioning`.

The operation:

- creates durable Platform provisioning intent before the Canary write;
- inserts only the operation-approved account columns;
- allows Canary-owned account-create trigger side effects to execute inside the Canary transaction;
- finalizes the exact created/recovered `accounts.id` into the immutable Platform binding;
- uses forward recovery after partial failure instead of destructive compensation;
- uses a non-user random compatibility credential that is never disclosed to the user;
- has a reviewed least-privilege SQL template and fail-closed effective-grant verifier;
- is covered by real MariaDB retry/recovery, trigger and privilege tests.

Contract: `docs/contracts/PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`.

### 2. Greenfield character creation

Connection: `canary_character_create`.

The operation:

- requires an authenticated Identity with a ready immutable Canary account binding;
- accepts only character name, approved vocation and approved sex as user-controlled inputs;
- applies ADR 0005 canonical-name/reserved-name and fixed starter-state policy;
- locks the authorized account row before recovery, quota evaluation and insert;
- enforces maximum 10 active characters;
- provides natural same-account/canonical-name idempotent recovery;
- uses a reviewed column-level least-privilege SQL template and fail-closed verifier;
- is covered by real MariaDB privilege, starter-state, quota-race and global same-name-race tests.

Contract: `docs/contracts/CHARACTER_CREATION_CONTRACT.md`.

`CHARACTER CREATION: IMPLEMENTED`

## Phase 5 exit gate

Satisfied by closure revalidation:

- every implemented shared write has an explicit operation-specific contract;
- authorization, partial-failure/idempotency and concurrency invariants are tested;
- both shared writes use independent least-privilege database principals;
- the generic `canary` connection remains SELECT-only;
- no additional undocumented raw Canary write is approved or claimed.

Generic write restrictions in the broad Canary discovery contract remain the default. They are superseded only by the two operation-specific Phase 5 contracts above.

## Deferred account/character lifecycle work

Not implemented or authorized:

- existing-account claim/import;
- character deletion/soft deletion;
- character rename;
- irreversible Canary account deletion;
- exceptional unlink/rebind/transfer.

These are optional future lifecycle capabilities and each requires its own explicit operation contract, least-privilege boundary and tests before any shared write.

## Game-login boundary

Account ownership/provisioning and game-login authorization remain separate boundaries.

Platform-originated users still require a separately authorized authoritative game-login bridge before game login can use Platform credential authority.

Required future security properties:

- authorization bound to the exact Platform-owned Canary account binding;
- short-lived cryptographically protected exchange material;
- explicit audience and expiry;
- replay-resistant consumption semantics;
- deterministic failure/revocation behavior;
- no user dependency on the internal compatibility credential;
- no duplicate Canary password verification in Oteryn Platform.

Likely external work:

- `opentibiabr/login-server`: add the Platform-authorized exact-account exchange and define game-session creation semantics;
- `blakinio/canary`: change only if the selected protocol requires direct assertion verification or stronger replay/revocation/fencing semantics.

No Canary/login-server repository was modified during Phase 5.

## Current active task

`OTERYN-20260720-phase5-closure` — closure/documentation revalidation only; no new shared writes.

## Recommended next work

After Phase 5 closure housekeeping, begin Phase 6 with the smallest bounded Admin/RBAC foundation task derived from live repository state.

The authoritative game-login bridge remains a separate high-priority cross-repository integration programme and may be scheduled when external-repository modification is explicitly authorized.

## High-priority remaining unknowns

- exact authoritative game-login assertion/session protocol and rollout;
- exact deployed production authentication topology;
- game-login revocation across every supported entry point;
- current Canary tournament-coin schema/code naming conflict;
- production runtime Redis ACL/endpoint provisioning;
- production hosting/network/mail/cache/queue topology.

## Architecture summary

```text
Cloudflare / Edge
       |
       v
Oteryn Platform
       |
       +--> Platform-owned Identity + application/provisioning data
       +--> read-only Canary SQL / runtime Redis
       +--> canary_provisioning (operation-specific least privilege)
       +--> canary_character_create (operation-specific least privilege)
       +--> future authoritative game-login bridge (not implemented)
                    |
                    v
             Canary / login-server
```

Payments remain deferred.

## How to update this file

Update only when project-level phase, implemented capabilities, major unknowns or next recommended work materially changes.

Detailed progress belongs in active task records and live PRs.
