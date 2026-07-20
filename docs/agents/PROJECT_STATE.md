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
- **Phase 6 — CMS, Admin, RBAC and Audit: COMPLETE**
- **Phase 7 — Production hardening and operations: IN PROGRESS**

## Current architecture state

Oteryn Platform is a Laravel 13 / PHP 8.5 modular monolith with Platform-owned Identity and application persistence.

Platform Identity owns supported user identity, account ownership policy and user credentials.

Supported game accounts are greenfield only:

`1 Platform Identity <-> 1 Canary accounts.id`

Existing Canary accounts are not imported or claimed.

## Phase 7 production-hardening state

PR #48 merged as `676a77590e3ec93bcad0247b3065d203ac209c40` and established the production-topology evidence baseline in `docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md`.

Repository-proven runtime/deployment capabilities include:

- provider-neutral logical target architecture with an optional Cloudflare edge, origin/reverse proxy and Laravel web tier;
- Laravel `/health` health route;
- CI workflow validates Composer, locked dependencies, Pint, PHPStan and the full test suite but has no deployment step;
- Platform database configuration supports SQLite and MySQL;
- Canary integration uses separate read-only, account-provisioning and character-create SQL configuration surfaces;
- Canary runtime status uses a separate Redis configuration surface;
- sessions are environment-configurable;
- current Platform cache stores are array/file/null only;
- current queue connection is synchronous only;
- mail supports SMTP/log/array transports;
- logging supports single-file and stderr output.

Actual deployed production topology remains `UNKNOWN` unless external non-secret deployment evidence proves it. Local `.env.example` defaults are not production evidence.

PR #49 / `OTERYN-20260720-phase7-production-config-guardrails` adds provider-independent runtime checks for:

- production environment mode;
- debug disabled;
- configured application encryption key;
- HTTPS non-localhost/loopback application URL;
- Secure and HttpOnly session cookies;
- delivery-capable mail transport;
- valid non-test sender address.

Command:

`php artisan production:verify-configuration`

The verifier returns non-zero on violations and reports only setting classes/messages, not secret values.

The verifier intentionally does not require a specific database engine, Redis session/cache backend, asynchronous queue, logging provider or Cloudflare policy because actual topology does not prove those are universal requirements.

## Phase 7 deployed-state unknowns

The repository does not currently prove:

- actual Cloudflare/DNS/WAF/Access configuration;
- actual origin provider, reverse proxy or ingress firewall restrictions;
- actual Platform production database engine/endpoint/network isolation;
- actual production session/cache backend;
- actual queue/worker model;
- actual mail provider/delivery status;
- actual centralized log/metrics/alerting sink;
- actual Canary SQL production network paths/credential provisioning status;
- actual runtime Redis endpoint/ACL provisioning status;
- actual backup/restore, deployment or rollback mechanism.

## Implemented Identity boundary

- registration and secure Platform web login/logout;
- revocable web-session generation;
- password recovery/change with Platform session revocation;
- opt-in TOTP MFA and single-use recovery codes;
- security event recording;
- reusable `mfa.confirmed` gate for privileged routes;
- explicit administrator RBAC authorization as a separate boundary from authentication and MFA.

Platform web authentication does not imply that current native Canary/external login-server paths already enforce Platform credential policy.

## Implemented Phase 6 Admin/RBAC boundary

Phase 6 is complete through merged PRs #44, #45 and closure PR #46.

PR #44 merged as `170d52393e543c8033ebd896f42fb43f3fccdf42` and provides:

- durable explicit roles, permissions, role-permission mappings and Identity-role assignments;
- no administrator assignment by default;
- explicit permission keys with no wildcard or implicit unrestricted-admin bypass;
- fail-closed `admin.permission` middleware;
- privileged route composition as `auth` + `mfa.confirmed` + `admin.permission:<exact-permission>`;
- focused deny/allow authorization regression coverage.

PR #45 merged as `be25d6ec3e0512bb9615329f99f16fff294d8b1d` and provides:

- one-time console-only first `platform_admin` bootstrap requiring an existing MFA-confirmed Platform Identity and closing after the first administrator assignment exists;
- audited transactional role assignment/removal behind `admin.roles.manage`;
- supported-path protection against removing the final `platform_admin`;
- explicit content/security/platform administrator role bundles governed by ADR 0006;
- no wildcard future-permission inheritance for `platform_admin`.

Every current administrator web capability independently requires authenticated Platform context, confirmed MFA and its exact server-side permission.

## Implemented Phase 6 CMS boundary

- Platform-owned news has permission-scoped create/update administration behind `cms.news.manage`;
- public news remains published-only;
- Platform-owned managed pages provide published-only public reads;
- managed-page create/update requires `cms.pages.manage`;
- CMS authoring in Phase 6 is plain text and public output is escaped;
- CMS state mutation and administrator audit append occur in the same Platform transaction where practical;
- no rich HTML authoring, media upload, arbitrary code execution or plugin upload feature was added.

## Implemented Phase 6 administrator audit boundary

- dedicated append-oriented `admin_audit_events` storage;
- audit events for first-admin bootstrap, administrator role assignment/removal and privileged CMS create/update operations;
- minimal actor/action/target/non-secret metadata only;
- bounded administrator audit visibility at 50 rows per page behind `audit.view`, authentication and confirmed MFA;
- audit storage is not a replacement for infrastructure/application logs.

Cloudflare Access is documented as an optional production outer gate only. It never replaces Platform authentication, confirmed MFA, RBAC or audit, and Phase 6 does not claim that Access is deployed.

ADR: `docs/architecture/adr/0006-admin-rbac-and-audit-policy.md`.

Deployment option: `docs/operations/CLOUDFLARE_ACCESS_ADMIN.md`.

## Phase 6 exit gate

Satisfied by closure PR #46, merged as `f25abd8799718ac99acce050ac55018d04fff2de`, after merged-main revalidation:

- current administrator routes are deny-by-default through exact explicit permission checks;
- unknown permissions fail closed and there is no wildcard authorization path;
- privileged role, CMS and audit operations have authorization/MFA regression coverage;
- delivered administrator state-changing operations append audit records;
- exact-head validation passed for PR #44 as CI #598 / Agent Governance #519;
- exact-head validation passed for PR #45 as CI #648 / Agent Governance #569;
- exact-head validation passed for PR #46 as CI #659 / Agent Governance #580.

Phase 6 changes are Platform-only. Canary/login-server credentials, sessions, schema and game-login behavior are unchanged.

## Implemented public/read-only boundary

- public Blade site shell and news;
- public managed pages;
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

- `canary_provisioning` — greenfield account provisioning/recovery under `PLATFORM_CANARY_ACCOUNT_PROVISIONING_CONTRACT.md`;
- `canary_character_create` — greenfield character creation under `CHARACTER_CREATION_CONTRACT.md` and ADR 0005.

The generic `canary` connection remains database-enforced read-only.

## Deferred account/character lifecycle work

Not implemented or authorized:

- existing-account claim/import;
- character deletion/soft deletion;
- character rename;
- irreversible Canary account deletion;
- exceptional unlink/rebind/transfer.

Each requires its own explicit operation contract, least-privilege boundary and tests before any shared write.

## Game-login boundary

Account ownership/provisioning and game-login authorization remain separate boundaries.

Platform-originated users still require a separately authorized authoritative game-login bridge before game login can use Platform credential authority.

Required future properties:

- authorization bound to the exact Platform-owned Canary account binding;
- short-lived cryptographically protected exchange material;
- explicit audience and expiry;
- replay-resistant consumption/session semantics;
- deterministic failure/revocation behavior;
- no user dependency on the internal compatibility credential;
- no duplicate Canary password verification in Oteryn Platform.

Expected external work:

- `opentibiabr/login-server`: Platform-authorized exact-account exchange and game-session creation semantics;
- `blakinio/canary`: only if the selected protocol requires direct assertion verification or stronger replay/revocation/fencing semantics.

No Canary/login-server repository was modified during Phase 5, Phase 6 or current Phase 7 work.

## Current active task

`OTERYN-20260720-phase7-production-config-guardrails` — PR #49.

## Recommended next work

Finish PR #49 with exact-head validation. If actual deployment evidence remains unavailable, continue with repository-owned dependency/security scanning and security-header/CSP review rather than inventing edge/origin/database state.

The authoritative game-login bridge remains a separate high-priority cross-repository programme that may be scheduled only when external-repository modification is explicitly authorized.

## High-priority remaining unknowns

- exact authoritative game-login assertion/session protocol and rollout;
- exact deployed production authentication topology;
- game-login revocation across every supported entry point;
- current Canary tournament-coin schema/code naming conflict;
- actual production runtime Redis ACL/endpoint provisioning;
- actual production hosting/network/mail/cache/queue topology;
- actual production backup/restore/deployment/rollback mechanisms;
- exact production Cloudflare Access/admin-hostname routing choice, if that optional gate is adopted.

## Architecture summary

```text
Cloudflare / Edge (actual deployment UNKNOWN)
       |
       v
Origin / reverse proxy (actual deployment UNKNOWN)
       |
       v
Oteryn Platform
       |
       +--> Platform-owned Identity + application/provisioning data
       +--> explicit Admin RBAC + confirmed MFA
       |       +--> permission-scoped CMS management
       |       +--> audited role management
       |       +--> bounded administrator audit visibility
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
