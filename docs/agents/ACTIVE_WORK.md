# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

None.

## Current project phase

**Phase 5 — Account and character management: COMPLETE**

**Phase 6 — CMS, Admin, RBAC and Audit: NEXT / PLANNED**

## Proven Phase 5 implementation state

- Greenfield ownership provisioning and immutable `1 Platform Identity <-> 1 Canary accounts.id` binding are implemented through PR #33 / `d5c319448737ee5badd8ab73967535a5ec9b67d1`.
- ADR 0005 character creation product policy is merged through PR #37 / `c5b8719de51deec6cea6d9270e55416fba1d6472`.
- Character-create operation contract is merged through PR #39 / `660f1790101842772b3bd5b18926b9dc9fc394a7`.
- Greenfield character creation is implemented through PR #41 / `9839822b8e445c0e9828e73d2d7767bb237e587f`.
- Phase 5 closure is merged through PR #42 / `3732b29b06addecbd07423ef655489a35001247c`.

The generic `canary` SQL connection remains `oteryn_readonly`.

The only approved Phase 5 Canary mutation connections are:

- `canary_provisioning` — greenfield account provisioning/recovery;
- `canary_character_create` — greenfield character creation.

Both have explicit operation contracts, reviewed least-privilege grant templates, fail-closed effective-grant verifiers and real MariaDB integration evidence.

Phase 5 exit gate is satisfied:

- every delivered shared write is explicitly contracted;
- authorization, partial-failure/idempotency and concurrency invariants are tested;
- no additional undocumented raw Canary write is approved or claimed.

## Deferred lifecycle operations

Not implemented or authorized:

- existing-account claim/import;
- character deletion/soft deletion;
- character rename;
- irreversible Canary account deletion;
- exceptional unlink/rebind/transfer.

Each requires a new explicit operation contract before any shared write.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate from completed Phase 5.

A future explicitly authorized cross-repository task must provide exact-account Platform authorization with:

- short-lived cryptographically protected exchange material;
- explicit audience and expiry;
- replay-resistant consumption/session semantics;
- deterministic revocation/failure behavior;
- no user dependency on the internal sink credential;
- no duplicate Canary password verification inside Oteryn Platform.

Expected primary external scope is `opentibiabr/login-server`. `blakinio/canary` changes are required only if the selected protocol needs direct assertion verification or stronger replay/revocation/fencing behavior.

## Production enablement note

Phase completion is a repository/contract milestone, not proof that production credentials are provisioned.

Before enabling Phase 5 writes in an environment:

- provision `canary_provisioning` out-of-band and pass its privilege verifier;
- provision `canary_character_create` out-of-band and pass `php artisan canary:verify-character-create-db-privileges`;
- fail closed if effective grants differ from approved operation surfaces.

## Recommended next work

Start Phase 6 with the smallest bounded Admin/RBAC foundation task derived from live repository state. The first Phase 6 slice should establish deny-by-default administrator identity/role authorization and compose it with the existing `auth` + `mfa.confirmed` security gates before privileged CMS/account actions are introduced.

The authoritative game-login bridge may be scheduled independently as a high-priority cross-repository programme once external-repository modification is explicitly authorized.

## Recently completed

- `OTERYN-20260720-phase5-closure` — PR #42 / `3732b29b06addecbd07423ef655489a35001247c`; task archived by post-merge housekeeping.
- `OTERYN-20260720-phase5-character-create-implementation` — PR #41 / `9839822b8e445c0e9828e73d2d7767bb237e587f`.
- `OTERYN-20260720-phase5-character-create-operation-contract` — PR #39 / `660f1790101842772b3bd5b18926b9dc9fc394a7`.
- `OTERYN-20260720-phase5-character-product-policy` — PR #37 / `c5b8719de51deec6cea6d9270e55416fba1d6472`.
- `OTERYN-20260720-phase5-platform-account-provisioning-implementation` — PR #33 / `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
