# OTERYN-20260720-phase7-production-evidence-collection

## Goal

Complete the maximum staging-verifiable portion of Phase 7 in a controlled production-like environment, while keeping final production-only evidence explicitly deferred and never treating staging as proof of final production state.

## Acceptance criteria

- [x] Controlled production-like validation records exact tested SHA(s) and classifies evidence as `STAGING_PROVEN`, `PRODUCTION_PROVEN` or `UNKNOWN` in operations evidence.
- [x] Clean deployment, migrations, configuration guardrails, health/readiness, rollback to a previous known-good SHA and redeployment of the current SHA are exercised in the production-like environment.
- [x] `production:verify-configuration`, generic Canary read-only privilege verification, provisioning privilege verification and character-create privilege verification are executed against real production-like service principals.
- [x] Effective Canary SQL grants are verified; the generic connection remains read-only; the two approved shared-write principals remain operation-specific; excessive privileges fail closed.
- [x] Runtime Redis is exercised with an actual ACL-restricted principal, allowed-key access, missing/expired/malformed data and dependency-failure behavior, with no unauthorized write capability for the read boundary.
- [x] A real production-like backup is restored into a clean database, measured, integrity-checked and followed by smoke validation; the result is recorded as staging recovery evidence only.
- [x] Critical implemented flows are exercised against the exact production-like validation SHA, including Identity registration/login/logout/recovery/MFA, administrator bootstrap/RBAC/CMS, account provisioning/binding, character creation and public game-data flows.
- [x] Running-environment security validation covers the staging-verifiable CSP/security-header, cookie, HTTPS-configuration, debug-disabled, sensitive-error, rate-limit, structured-log, request-correlation and audit boundaries; final TLS/reverse-proxy topology remains explicitly `UNKNOWN`.
- [x] A real SMTP path to a safe staging/test mail provider is exercised without using Laravel `array`/`log` mail transports as delivery evidence.
- [x] Safe failure scenarios are exercised for Canary DB, runtime Redis, mail, invalid production configuration, insufficient DB privileges, malformed runtime Redis data, interrupted deployment and restore/recovery where supported by the controlled environment.
- [x] A minimal final production verification checklist contains only facts that staging cannot prove.
- [x] Phase 7 remains IN PROGRESS until final production-only exit-gate evidence is proven or eligible risks are explicitly owner-accepted.

## Ownership

```yaml
owned_paths:
  - .github/workflows/phase7-production-like-validation.yml
  - scripts/operations/**
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
  - docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
  - docs/operations/INCIDENT_RECOVERY_RUNBOOK.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/handovers/OTERYN-20260720-phase7-handover.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-production-evidence-collection.md
modules:
  - PlatformOperations
  - CanaryIntegration
  - PublicGameData
  - Identity
  - Accounts
  - Characters
  - Admin
  - CMS
dependencies:
  - PR #56 / ae659089bb288dd467f5e2f163ffb7d731e35cec
  - PR #62 / b6878c4775eda542738c78ea99fd5d2e19d2b35f
blockers:
  - final production-only evidence requires access to the actual production environment and provider controls unavailable in the current repository/tool context
cross_repository_tasks:
  - authoritative Platform game-login bridge remains separately authorized work if required for launch scope
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T21:10:06Z
head: d29a024bda0101c3947b466bc4e39766695b05b3
branch: task/OTERYN-20260720-phase7-production-evidence-collection
pr: 63
status: blocked
context_routes:
  - architecture
  - security
  - testing
  - database
  - canary-integration
  - auth-identity
  - accounts-characters
  - public-game-data
  - admin-rbac
  - web-cms
  - agent-governance
owned_paths:
  - .github/workflows/phase7-production-like-validation.yml
  - scripts/operations/**
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
  - docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
  - docs/operations/INCIDENT_RECOVERY_RUNBOOK.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/handovers/OTERYN-20260720-phase7-handover.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-production-evidence-collection.md
proven:
  - Phase 7 repository-owned hardening through PR #56 is merged on main; PR #56 merged as ae659089bb288dd467f5e2f163ffb7d731e35cec after CI #735 and Agent Governance #655 passed on its exact PR head.
  - Post-merge housekeeping PR #62 merged as b6878c4775eda542738c78ea99fd5d2e19d2b35f after CI #749 and Agent Governance #669 passed; it established this continuation task on main.
  - The task scope explicitly permits controlled staging or production-like evidence for deployment, rollback, backup/restore, configuration, DB/Redis boundaries, mail, security, critical flows and failure/recovery scenarios, while forbidding promotion of that evidence into final-production proof.
  - PR #63 adds a repeatable `Phase 7 Production-Like Validation` workflow using ephemeral MariaDB, Redis ACL and SMTP test services without production secrets or private endpoints.
  - Production-like workflow run 29779031870 / run #5 completed successfully on exact validation SHA b6dcd6ed95c55f400206864ffd6ff799e65aa2b3.
  - Required CI run 29779031976 / CI #755 and Agent Governance run 29779031673 / #675 also completed successfully on exact SHA b6dcd6ed95c55f400206864ffd6ff799e65aa2b3.
  - Non-secret evidence artifact phase7-production-like-evidence-29779031870 recorded classification STAGING_PROVEN at 2026-07-20T21:10:06Z with digest sha256:5667b86ed0a8aaeb1d0a269cf2f5ff0a1e8c237ba6a67420c5e16fa35a3248a9.
  - Controlled deployment/migration, rollback to b6878c4775eda542738c78ea99fd5d2e19d2b35f, interrupted-release isolation and redeploy of b6dcd6ed95c55f400206864ffd6ff799e65aa2b3 all passed.
  - `production:verify-configuration`, `canary:verify-db-privileges`, `canary:verify-provisioning-db-privileges` and `canary:verify-character-create-db-privileges` passed against the controlled environment.
  - Real MariaDB principals proved the generic Canary connection read-only, provisioning and character-create credentials operation-specific, prohibited cross-surface writes denied and excessive/insufficient privilege drift fail-closed.
  - Real Redis ACL validation proved the deterministic runtime read boundary, denied unauthorized writes and exercised missing, malformed and unavailable dependency semantics.
  - Real SMTP protocol delivery to a safe test service passed; an unavailable SMTP endpoint failed as expected.
  - The full exact-SHA regression suite passed with real MariaDB provisioning/character integration coverage enabled; live release smoke passed for health, CSP/security headers, Secure/HttpOnly cookies, request correlation and JSON request-completion logging.
  - Real MariaDB backup/restore into a clean database passed integrity checks: 13 source/restored tables, 11 source/restored migrations and matching validation-SHA probe.
  - Measured controlled staging restore time was 102 ms on 2026-07-20; this is staging recovery evidence only and is not a production RTO/RPO.
  - `docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md` records the exact staging evidence and a minimal final production-only verification pass.
  - Generic Canary SQL remains contractually read-only and the only approved Phase 5 shared-write credentials are the operation-specific `canary_provisioning` and `canary_character_create` principals.
  - Runtime channel availability reads deterministic `cluster:channel:{id}:runtime` Redis hashes using only `HMGET` and `PTTL`, treats non-positive TTL as missing and fails the runtime snapshot closed on malformed/dependency-failure conditions.
  - No secret or production-only credential was copied into Git, task records, PRs, evidence artifact or handoff; only CI-only placeholders and non-secret evidence were recorded.
derived:
  - The staging-verifiable portion of the current Phase 7 scope is closed by the controlled workflow plus exact-SHA repository CI and feature/integration coverage.
  - The production-like recovery result demonstrates the procedure on the controlled dataset but cannot establish production RTO/RPO because production data volume, infrastructure and backup technology remain unproven.
  - The controlled release-pointer rollback model is valid staging evidence but cannot prove the final provider's deployment/rollback implementation or operator access controls.
  - No further repository-only or controlled-staging task is required by the current Phase 7 checklist before the final production verification pass; remaining facts are environment-specific or separately authorized cross-repository work.
  - Phase 7 cannot be marked COMPLETE solely from the controlled staging evidence because the roadmap exit gate requires final production readiness evidence.
unknown:
  - final production DNS/proxy/Cloudflare/WAF/Access/TLS/HSTS state
  - final production direct-origin exposure and ingress firewall/reverse-proxy restrictions
  - final production Platform DB topology/network isolation/HA and credential rotation ownership
  - final production Canary SQL endpoint/network paths and effective grants for each enabled dedicated principal
  - final production runtime Redis endpoint/ACL/network/TLS state and dependency/freshness monitoring
  - final production session/cache/queue topology and worker supervision if asynchronous queues are introduced
  - final production backup scope/schedule/retention/encryption/access policy and dated production restore result
  - final production logging/metrics/alerting sink, retention/access policy and on-call routing
  - final production mail provider, sender-domain readiness and delivery/bounce monitoring
  - exact final production deployed SHA(s), relevant Canary/login-server versions and final critical smoke/E2E results
  - whether the authoritative Platform game-login bridge is required in the final launch scope and, if so, its separately authorized implementation state
conflicts: []
first_failure:
  marker: final production-only verification pass
  evidence: all currently staging-verifiable Phase 7 validation is recorded as STAGING_PROVEN, but the current repository/tool context has no authoritative access to final production DNS/edge/TLS/origin/firewall, provider deployment controls, production DB/Redis principals, backup schedule/restore, production observability or production mail state
rejected_hypotheses:
  - Repository hardening alone proves production readiness: rejected because the Phase 7 checklist requires environment and operational evidence.
  - Staging evidence can be labeled production evidence: rejected; final-production facts remain `UNKNOWN` until directly proven.
  - A 102 ms staging restore establishes production RTO/RPO: rejected because the controlled dataset and runner do not represent production volume or infrastructure.
  - Successful controlled MariaDB/Redis principals prove final production grants/ACLs: rejected because final environment principals and network paths must be verified directly.
  - Local or CI defaults describe production topology: rejected because the topology evidence baseline explicitly treats them as non-production evidence.
changed_paths:
  - .github/workflows/phase7-production-like-validation.yml
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-production-evidence-collection.md
validation:
  - command: Phase 7 Production-Like Validation run 29779031870 / #5 on b6dcd6ed95c55f400206864ffd6ff799e65aa2b3
    result: PASS
    evidence: deployment/migrations, three DB privilege verifiers, privilege fail-closed scenarios, Redis ACL/failure semantics, SMTP delivery/failure, production configuration guardrails, full regression suite, live security smoke, measured backup/restore and rollback/redeploy all passed.
  - command: CI run 29779031976 / #755 on b6dcd6ed95c55f400206864ffd6ff799e65aa2b3
    result: PASS
    evidence: required Composer metadata/install/advisory audit, Pint, PHPStan and full test suite passed.
  - command: Agent Governance run 29779031673 / #675 on b6dcd6ed95c55f400206864ffd6ff799e65aa2b3
    result: PASS
    evidence: governance validation passed on the exact staging-validation SHA.
  - command: production-like backup/restore artifact phase7-production-like-evidence-29779031870
    result: PASS
    evidence: STAGING_PROVEN at 2026-07-20T21:10:06Z; 13/13 tables, 11/11 migrations, SHA probe matched, measured restore 102 ms.
blockers:
  - final production-only evidence requires access to the actual production environment and provider controls
  - authoritative Platform game-login bridge requires separate authorization if it is part of launch scope
next_action: When final production access and deployment authorization are available, execute only the `PRODUCTION_PROVEN` checklist in docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md against the exact deployed SHA(s); do not repeat already closed staging validation unless production candidate code or relevant contracts change.
```

## Security handoff

- Trust boundary: controlled production-like services are `STAGING_PROVEN`; final production infrastructure remains a separate unproven boundary.
- Auth invariant: unchanged; Platform auth + confirmed MFA + exact RBAC permission remains mandatory for administrator routes.
- Canary/login-server compatibility: unchanged; no schema/session protocol mutation or external repository write is authorized by this task.
- Rollback: controlled rollback/redeploy was exercised successfully; no real production rollback was performed or inferred.
- Recovery: controlled restore completed in 102 ms with matching table/migration counts and SHA probe; do not use this as production RTO/RPO.
- Secrets: only ephemeral CI-only placeholder credentials were used; no real production credentials, private keys, connection strings or private endpoint inventories were recorded.
