# Oteryn Platform Active Work

Convenience index only. The individual active task record, live PR and Git state are authoritative.

## Active tasks

- `OTERYN-20260720-phase6-admin-cms-audit` — PR #45 — `task/OTERYN-20260720-phase6-admin-cms-audit`

## Current project phase

**Phase 5 — Account and character management: COMPLETE**

**Phase 6 — CMS, Admin, RBAC and Audit: IN PROGRESS**

## Proven Phase 6 foundation

PR #44 merged as `170d52393e543c8033ebd896f42fb43f3fccdf42` and established:

- durable explicit roles, permissions, role-permission mappings and Identity-role assignments;
- no administrator assignment by default;
- no wildcard or implicit unrestricted-admin authorization path;
- reusable fail-closed `admin.permission` middleware;
- privileged route composition using `auth` + `mfa.confirmed` + an exact permission;
- focused authorization regression coverage.

The completed RBAC foundation task is archived.

## Current Phase 6 slice

PR #45 implements the remaining Phase 6 privileged vertical slice:

- one-time console-only first `platform_admin` bootstrap that requires confirmed MFA and closes after the first administrator assignment exists;
- audited transactional role assignment/removal with protection against removing the final `platform_admin`;
- permission-scoped plain-text news management;
- Platform-owned managed pages with published-only public reads and escaped output;
- permission-scoped plain-text managed-page administration;
- append-oriented administrator audit events and bounded `audit.view` query UI;
- ADR 0006 administrator RBAC/audit policy;
- optional Cloudflare Access administrator-gate deployment guidance.

No arbitrary code/plugin upload, rich HTML, media upload, Canary mutation, payment work or cross-repository change is introduced.

## Validation state

- Bootstrap/package-discovery failure caused by global exception `use` statements was identified from a dedicated CI artifact and fixed.
- Pint identified three formatting-only files; exact Pint-formatted versions were applied.
- Full CI run 639 passed Composer install, Pint, PHPStan and the complete test suite on implementation head `5688edccefe90a4eb62334369155aa263f0c797c`.
- The temporary Phase 6 diagnostic workflow has been removed.
- Exact-head CI and Agent Governance are still required after final task/documentation synchronization before PR #45 can merge.

## Deferred lifecycle operations

Not implemented or authorized:

- existing-account claim/import;
- character deletion/soft deletion;
- character rename;
- irreversible Canary account deletion;
- exceptional unlink/rebind/transfer.

Each requires a new explicit operation contract before any shared write.

## Remaining cross-repository dependency

The authoritative Platform game-login bridge remains separate from completed Phase 5 and current Phase 6.

A future explicitly authorized cross-repository task must provide exact-account Platform authorization with:

- short-lived cryptographically protected exchange material;
- explicit audience and expiry;
- replay-resistant consumption/session semantics;
- deterministic revocation/failure behavior;
- no user dependency on the internal sink credential;
- no duplicate Canary password verification inside Oteryn Platform.

Expected primary external scope is `opentibiabr/login-server`. `blakinio/canary` changes are required only if the selected protocol needs direct assertion verification or stronger replay/revocation/fencing behavior.

## Production enablement note

Phase completion is a repository/contract milestone, not proof that production infrastructure or credentials are provisioned.

Before enabling Phase 5 writes in an environment:

- provision `canary_provisioning` out-of-band and pass its privilege verifier;
- provision `canary_character_create` out-of-band and pass `php artisan canary:verify-character-create-db-privileges`;
- fail closed if effective grants differ from approved operation surfaces.

For Phase 6 administrator enablement, create an Identity, complete MFA, then use the one-time `admin:bootstrap` command. Cloudflare Access remains optional defense in depth and never replaces application auth/MFA/RBAC.

## Recommended next work

Finish PR #45 exact-head validation and merge. Then run a bounded Phase 6 closure revalidation, archive the completed task, mark Phase 6 COMPLETE only if every roadmap exit gate remains satisfied, and prepare the requested handover.

## Recently completed

- `OTERYN-20260720-phase6-admin-rbac-foundation` — PR #44 / `170d52393e543c8033ebd896f42fb43f3fccdf42`.
- `OTERYN-20260720-phase5-closure` — PR #42 / `3732b29b06addecbd07423ef655489a35001247c`.

## Coordination rule

Before starting substantial work, search `docs/agents/tasks/active/**` and open PRs for overlapping paths or intent. Do not claim paths already owned by another active task without explicit coordination.
