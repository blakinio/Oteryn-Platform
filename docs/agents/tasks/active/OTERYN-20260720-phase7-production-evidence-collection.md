# OTERYN-20260720-phase7-production-evidence-collection

## Goal

Complete the maximum staging-verifiable portion of Phase 7 in a controlled production-like environment, while keeping final production-only evidence explicitly deferred and never treating staging as proof of final production state.

## Acceptance criteria

- [ ] Controlled production-like validation records exact tested SHA(s) and classifies evidence as `STAGING_PROVEN`, `PRODUCTION_PROVEN` or `UNKNOWN` in operations evidence.
- [ ] Clean deployment, migrations, configuration guardrails, health/readiness, rollback to a previous known-good SHA and redeployment of the current SHA are exercised in the production-like environment.
- [ ] `production:verify-configuration`, generic Canary read-only privilege verification, provisioning privilege verification and character-create privilege verification are executed against real production-like service principals.
- [ ] Effective Canary SQL grants are verified; the generic connection remains read-only; the two approved shared-write principals remain operation-specific; excessive privileges fail closed.
- [ ] Runtime Redis is exercised with an actual ACL-restricted principal, allowed-key access, missing/expired/malformed data and dependency-failure behavior, with no unauthorized write capability for the read boundary.
- [ ] A real production-like backup is restored into a clean database, measured, integrity-checked and followed by smoke validation; the result is recorded as staging recovery evidence only.
- [ ] Critical implemented flows are exercised against the exact production-like validation SHA, including Identity registration/login/logout/recovery/MFA, administrator bootstrap/RBAC/CMS, account provisioning/binding, character creation and public game-data flows.
- [ ] Running-environment security validation covers CSP/security headers, cookies, HTTPS/proxy handling, debug-disabled and sensitive-error behavior, relevant rate limits, secret-safe structured logging, request correlation and audit events.
- [ ] A real SMTP path to a safe staging/test mail provider is exercised without using Laravel `array`/`log` mail transports as delivery evidence.
- [ ] Safe failure scenarios are exercised for Canary DB, runtime Redis, mail, invalid production configuration, insufficient DB privileges, malformed runtime Redis data, interrupted deployment and restore/recovery where supported by the controlled environment.
- [ ] A minimal final production verification checklist contains only facts that staging cannot prove.
- [ ] Phase 7 remains IN PROGRESS until final production-only exit-gate evidence is proven or eligible risks are explicitly owner-accepted.

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
  - final production-only evidence remains unavailable through the current repository/tool context
cross_repository_tasks:
  - authoritative Platform game-login bridge remains separately authorized work if required for launch scope
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T20:30:00Z
head: UNKNOWN
branch: task/OTERYN-20260720-phase7-production-evidence-collection
pr: none
status: implementing
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
  - The task scope now explicitly permits controlled staging or production-like evidence for deployment, rollback, backup/restore, configuration, DB/Redis boundaries, mail, security, critical flows and failure/recovery scenarios, while forbidding promotion of that evidence into final-production proof.
  - Repository controls already include production configuration verification, Composer advisory scanning, browser CSP/security headers, server-generated request correlation, readiness checklist and incident/recovery runbook.
  - Generic Canary SQL is contractually read-only and the only approved Phase 5 shared-write credentials are the operation-specific `canary_provisioning` and `canary_character_create` principals.
  - Runtime channel availability reads deterministic `cluster:channel:{id}:runtime` Redis hashes using only `HMGET` and `PTTL`, treats non-positive TTL as missing and fails the runtime snapshot closed on malformed/dependency-failure conditions.
  - No secret or production-only credential may be copied into Git, task records, PRs, logs or handoffs; only non-secret controlled-environment evidence is acceptable.
derived:
  - The production-only blocker no longer prevents staging-verifiable Phase 7 work from continuing.
  - A dedicated CI-backed production-like validation harness is the smallest repository-controlled mechanism available to generate repeatable non-secret staging evidence without inventing provider-specific production claims.
  - Phase 7 still cannot be marked COMPLETE solely from the controlled staging evidence because the roadmap exit gate requires final production readiness evidence.
unknown:
  - final production DNS/edge/Cloudflare/TLS/origin/firewall state
  - final production Platform DB and Canary SQL endpoint/network/effective-grant state
  - final production runtime Redis endpoint/ACL/network/TLS state
  - final production backup schedule and operational restore result
  - final production logging/monitoring sink and on-call routing
  - final production mail provider and delivery monitoring
  - exact final production deployed SHA(s) and final critical smoke/E2E results
  - whether the authoritative Platform game-login bridge is required in the final launch scope and, if so, its separately authorized implementation state
conflicts: []
first_failure:
  marker: controlled production-like Phase 7 validation harness
  evidence: no repository workflow currently exercises the combined deployment/rollback, effective DB principals, runtime Redis ACL, SMTP delivery, backup/restore and critical-flow validation as one exact-SHA staging evidence pass
rejected_hypotheses:
  - Repository hardening alone proves production readiness: rejected because the Phase 7 checklist requires environment and operational evidence.
  - Staging evidence can be labeled production evidence: rejected by current task scope; final-production facts remain separate `UNKNOWN` until directly proven.
  - Local `.env.example` defaults describe production topology: rejected because the topology evidence baseline explicitly treats them as local-safe defaults only.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260720-phase7-production-evidence-collection.md
validation:
  - command: PR #56 CI #735 and Agent Governance #655
    result: PASS
    evidence: readiness/runbook task passed required checks before squash merge as ae659089bb288dd467f5e2f163ffb7d731e35cec.
  - command: PR #62 CI #749 and Agent Governance #669
    result: PASS
    evidence: post-merge housekeeping and active-task establishment passed required checks before squash merge as b6878c4775eda542738c78ea99fd5d2e19d2b35f.
  - command: controlled production-like Phase 7 validation
    result: NOT_RUN
    evidence: harness not yet implemented on this task branch.
blockers:
  - final production-only evidence remains deferred until the real production environment is accessible
next_action: Implement the smallest CI-backed production-like validation harness that provisions real MariaDB least-privilege principals, Redis ACL and SMTP test delivery, then runs exact-SHA configuration/privilege/security/critical-flow validation and records only non-secret staging evidence.
```

## Security handoff

- Trust boundary: controlled production-like services now enter scope for staging validation; final production infrastructure remains a separate unproven boundary.
- Auth invariant: unchanged; Platform auth + confirmed MFA + exact RBAC permission remains mandatory for administrator routes.
- Canary/login-server compatibility: unchanged; no schema/session protocol mutation or external repository write is authorized by this task.
- Rollback: the controlled environment must exercise rollback/redeploy safely; no real production rollback is performed by this task.
- Secrets: use ephemeral CI-only credentials or repository-safe placeholders; never commit or print real production credentials, private keys, connection strings or private endpoint inventories.
