# OTERYN-20260720-phase7-production-evidence-collection

## Goal

Complete the environment-dependent Phase 7 production-readiness gates using sanitized evidence from the actual deployed application, edge/origin, database, Canary SQL, runtime Redis, mail, observability, deployment/rollback and backup/restore environment.

## Acceptance criteria

- [ ] Actual production topology is documented from sanitized, non-secret evidence rather than repository defaults or assumptions.
- [ ] Edge/TLS/origin exposure and database network restrictions are reviewed against the real environment.
- [ ] Effective Canary SQL privilege verifiers are run against the real production credential classes before claiming those boundaries.
- [ ] Runtime Redis ACL/network state is proven if the integration is enabled.
- [ ] Effective session/cache/queue/mail/logging choices are documented and validated.
- [ ] Deployment/migration/rollback procedure is proven against the actual deployment mechanism.
- [ ] Backup policy is documented and a dated operational restore test records scope, result, recovery time and data-loss measurement.
- [ ] Critical production E2E checks are tied to exact deployed SHAs where applicable.
- [ ] Phase 7 is marked COMPLETE only after the readiness exit gate is actually satisfied or remaining eligible risks are explicitly owner-accepted.

## Ownership

```yaml
owned_paths:
  - docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
  - docs/operations/INCIDENT_RECOVERY_RUNBOOK.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/handovers/OTERYN-20260720-phase7-handover.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-production-evidence-collection.md
modules:
  - PlatformOperations
dependencies:
  - PR #56 / ae659089bb288dd467f5e2f163ffb7d731e35cec
  - PR #62 / b6878c4775eda542738c78ea99fd5d2e19d2b35f
blockers:
  - sanitized evidence from the actual production environment is not available through the current repository/tool context
cross_repository_tasks:
  - authoritative Platform game-login bridge remains separately authorized work if required for launch scope
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T20:00:00Z
head: b6878c4775eda542738c78ea99fd5d2e19d2b35f
branch: task/OTERYN-20260720-phase7-production-evidence-collection
pr: none
status: blocked
context_routes:
  - architecture
  - security
  - testing
  - agent-governance
owned_paths:
  - docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
  - docs/operations/INCIDENT_RECOVERY_RUNBOOK.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/handovers/OTERYN-20260720-phase7-handover.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-production-evidence-collection.md
proven:
  - Phase 7 repository-owned hardening through PR #56 is merged on main; PR #56 merged as ae659089bb288dd467f5e2f163ffb7d731e35cec after CI #735 and Agent Governance #655 passed on its exact PR head.
  - Post-merge housekeeping PR #62 merged as b6878c4775eda542738c78ea99fd5d2e19d2b35f after CI #749 and Agent Governance #669 passed; it archived the completed readiness task and established this blocked continuation task on main.
  - Repository controls now include production configuration verification, Composer advisory scanning, browser CSP/security headers, server-generated request correlation, readiness checklist and incident/recovery runbook.
  - The repository does not prove actual production Cloudflare/TLS/origin ingress, Platform DB topology, Canary SQL endpoint provisioning, runtime Redis ACL/network state, session/cache/queue choices, mail delivery, centralized observability, deployment/rollback or backup/restore operation.
  - Trust boundary affected by this task is the real production environment and its external service/network integrations; no production configuration is changed by the current blocked task.
  - Authentication/authorization invariant remains unchanged: administrator web access requires Platform authentication, confirmed MFA and exact explicit permission; MFA is not authorization.
  - Canary/login-server schema and session compatibility are unchanged by this evidence-collection task; no external repository write is authorized by this task.
  - No rollback is currently required because this blocked task has not changed production or application behavior.
  - No secret or production-only credential may be copied into Git, task records, PRs, logs or handoffs; only sanitized non-secret evidence is acceptable.
derived:
  - Phase 7 cannot be marked COMPLETE from repository state alone.
  - The next correct dependency is environment evidence collection followed by edge/origin/database review and an operational backup-restore test, not further provider-specific implementation guesses.
unknown:
  - actual production topology and operational evidence enumerated by PRODUCTION_TOPOLOGY_EVIDENCE.md and PRODUCTION_READINESS_CHECKLIST.md
  - whether the authoritative Platform game-login bridge is required in the final launch scope and, if so, its separately authorized implementation state
conflicts: []
first_failure:
  marker: Phase 7 production-readiness exit gate
  evidence: environment-specific topology, deployment/rollback, centralized observability and dated backup-restore evidence are unavailable in current repository/tool context
rejected_hypotheses:
  - Repository hardening alone proves production readiness: rejected because the Phase 7 checklist requires real environment and restore-test evidence.
  - Local .env.example defaults describe production topology: rejected because the topology evidence baseline explicitly treats them as local-safe defaults only.
  - Provider-specific commands can be invented safely before topology evidence: rejected because deployment, backup and network providers remain UNKNOWN.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-production-readiness-runbooks.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-production-evidence-collection.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/handovers/OTERYN-20260720-phase7-handover.md
validation:
  - command: PR #56 CI #735 and Agent Governance #655
    result: PASS
    evidence: readiness/runbook task passed required checks before squash merge as ae659089bb288dd467f5e2f163ffb7d731e35cec.
  - command: PR #62 CI #749 and Agent Governance #669
    result: PASS
    evidence: post-merge housekeeping and active-task establishment passed required checks before squash merge as b6878c4775eda542738c78ea99fd5d2e19d2b35f.
  - command: python tools/agents/checkpoint.py docs/agents/tasks/active/OTERYN-20260720-phase7-production-evidence-collection.md --require-checkpoint
    result: NOT_RUN
    evidence: must be run against this final active-task checkpoint before compact handoff generation.
blockers:
  - sanitized actual-production evidence is required before substantive environment-dependent Phase 7 work can continue
next_action: Obtain sanitized evidence for the actual production application/edge/origin/database/Redis/mail/logging/backup/deployment topology, then execute the edge/origin/database exposure review and dated backup-restore operational test required by the Phase 7 exit gate.
```

## Security handoff

- Trust boundary: actual production environment and external infrastructure/service integrations.
- Auth invariant: unchanged; Platform auth + confirmed MFA + exact RBAC permission remains mandatory for administrator routes.
- Canary/login-server compatibility: unchanged by this task; no schema/session protocol mutation is authorized here.
- Rollback: none for evidence collection itself; any future environment change requires the real provider/deployment rollback procedure.
- Secrets: never commit or paste production credentials, private keys, connection strings, copied `.env` files or private IP inventories. Use sanitized evidence only.
