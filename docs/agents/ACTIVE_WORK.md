# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase5-character-create-operation-contract` — defines the exact implementation-ready Canary transaction, retry/idempotency semantics and dedicated `canary_character_create` least-privilege boundary for ADR 0005 character creation. No character shared write is implemented in this contract task.

## Proven implementation result

Phase 5 greenfield ownership provisioning and immutable binding are implemented on `main` through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

ADR 0005 character creation product policy is merged through PR #37 as `c5b8719de51deec6cea6d9270e55416fba1d6472`.

The selected product policy defines canonical names, base vocation/sex choices, starter profile v1, no dependent starter writes, maximum 10 active characters per account and same-account serialization before the quota count.

The existing `canary` read-only and `canary_provisioning` account-create connections remain unchanged.

`CHARACTER CREATION: BLOCKED` until the active operation contract is approved and the resulting implementation is tested.

## Active contract target

The task is proving:

1. one exact `players` insert column allowlist for starter profile v1;
2. an exact `accounts.id` row lock followed by active-character count inside one Canary transaction;
3. canonical `(bound account_id, canonical name)` retry recovery with vocation/sex consistency checks;
4. deterministic name-conflict, limit-conflict, idempotency-conflict and dependency-failure results;
5. bounded deadlock/serialization retry only;
6. a third dedicated `canary_character_create` credential with only the exact SELECT/INSERT columns required by the operation;
7. real MariaDB integration coverage required before the implementation can merge.

No `UPDATE` or `DELETE` on `players`, no account credential reads and no inventory/storage/session/guild writes are intended.

## Cross-repository state

Current Canary evidence pin for this task is `800142e65c2975e57647bf34128ab468532218f0`. The only commit after the ADR 0005 evidence pin changed OAM documentation, not player schema/load behavior.

No Canary repository change is currently proven necessary. If implementation integration tests disprove the selected starter/loadability assumptions, the exact Canary/datapack change must be recorded for a separately authorized task rather than applied implicitly.

The future authoritative game-login bridge remains separate work recorded in `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`.

## Other queued work

- Existing-account claim/import is out of scope for the greenfield product.
- Admin/RBAC identity classification and permissions remain Phase 6.

## Recently completed

- `OTERYN-20260720-phase5-character-product-policy` — selected ADR 0005 and merged through PR #37 as `c5b8719de51deec6cea6d9270e55416fba1d6472`.
- `OTERYN-20260720-phase5-character-creation-policy-revalidation` — revalidated the character-create gate after ownership binding, merged through PR #35 as `35ca943d6051ff27f215b813d51a5ae557cbac40`.
- `OTERYN-20260720-phase5-platform-account-provisioning-implementation` — implemented immutable greenfield account provisioning/binding, merged through PR #33 as `d5c319448737ee5badd8ab73967535a5ec9b67d1`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
