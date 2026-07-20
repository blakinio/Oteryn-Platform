# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase5-closure` — revalidates the Phase 5 roadmap exit gate after PR #41, synchronizes project/contract state and records the remaining game-login cross-repository handover. No new shared writes are in scope.

## Proven Phase 5 implementation state

- Greenfield ownership provisioning and immutable `1 Platform Identity <-> 1 Canary accounts.id` binding are implemented through PR #33 / `d5c319448737ee5badd8ab73967535a5ec9b67d1`.
- ADR 0005 character creation product policy is merged through PR #37 / `c5b8719de51deec6cea6d9270e55416fba1d6472`.
- The character-create operation contract is merged through PR #39 / `660f1790101842772b3bd5b18926b9dc9fc394a7`.
- Greenfield character creation is implemented through PR #41 / `9839822b8e445c0e9828e73d2d7767bb237e587f`.

The generic `canary` SQL connection remains `oteryn_readonly`.

The only Phase 5 mutation connections are:

- `canary_provisioning` for the account-provisioning contract;
- `canary_character_create` for the character-create contract.

Both have separate reviewed least-privilege grant templates, fail-closed effective-grant verifiers and real MariaDB integration evidence.

## Closure conclusion under validation

The Phase 5 roadmap exit gate is satisfied by current evidence:

- every implemented shared write has an explicit operation-specific contract;
- authorization, partial-failure/idempotency and concurrency invariants are tested;
- no third undocumented Canary write boundary is configured or approved.

Character deletion/soft deletion and rename are optional future lifecycle capabilities, not delivered Phase 5 writes. They remain forbidden until separately contracted and tested.

Existing-account claim/import remains outside the greenfield product model.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge is not implemented by Phase 5 and remains separate from the shared-write exit gate.

A future separately authorized task must define and implement short-lived exact-account Platform authorization with explicit expiry, replay/session-consumption and revocation semantics. Expected primary external scope is `opentibiabr/login-server`; `blakinio/canary` changes are required only if the selected protocol needs direct assertion verification or stronger fencing/revocation support.

No Canary/login-server repository is modified by the Phase 5 closure task.

## Production enablement note

Phase completion is a repository/contract milestone, not a claim that production secrets are provisioned.

Before enabling writes in an environment:

- provision `canary_provisioning` out-of-band and run its privilege verifier;
- provision `canary_character_create` out-of-band and run `php artisan canary:verify-character-create-db-privileges`;
- fail closed if effective grants differ from the approved operation surfaces.

## Recently completed

- `OTERYN-20260720-phase5-character-create-implementation` — PR #41 / `9839822b8e445c0e9828e73d2d7767bb237e587f`; task archived by this closure branch.
- `OTERYN-20260720-phase5-character-create-operation-contract` — PR #39 / `660f1790101842772b3bd5b18926b9dc9fc394a7`.
- `OTERYN-20260720-phase5-character-product-policy` — PR #37 / `c5b8719de51deec6cea6d9270e55416fba1d6472`.
- `OTERYN-20260720-phase5-platform-account-provisioning-implementation` — PR #33 / `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
