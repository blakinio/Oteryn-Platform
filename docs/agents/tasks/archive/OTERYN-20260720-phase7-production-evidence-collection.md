# OTERYN-20260720-phase7-production-evidence-collection

## Goal

Complete the maximum staging-verifiable portion of Phase 7 in a controlled production-like environment, while keeping final production-only evidence explicitly deferred and never treating staging as proof of final production state.

## Final status

**COMPLETE — staging-verifiable scope closed by merged PR #63.**

ADR 0007 was accepted later by `OTERYN-20260721-phase7-go-live-gate-separation` and reclassified the remaining final-production verification from a Phase 7 engineering blocker into the separate fail-closed Production Go-Live Gate. This archival record preserves the original staging-validation scope; it does not relabel any evidence as production proof.

## Acceptance criteria

- [x] Controlled production-like validation records exact tested SHA(s) and classifies evidence as `STAGING_PROVEN`, `PRODUCTION_PROVEN` or `UNKNOWN`.
- [x] Clean deployment, migrations, configuration guardrails, health/readiness, rollback, interrupted-release isolation and redeploy were exercised.
- [x] `production:verify-configuration` and all three Canary DB privilege verifiers were executed against real production-like service principals.
- [x] Effective Canary SQL grants were verified; generic Canary remained read-only; operation-specific write principals remained isolated; excessive/insufficient privilege drift failed closed.
- [x] Runtime Redis used a real ACL-restricted principal with allowed-key reads, denied writes and missing/malformed/unavailable dependency behavior.
- [x] A real production-like backup was restored into a clean database, measured, integrity-checked and smoke-validated.
- [x] Critical implemented Identity/admin/RBAC/CMS/account/binding/character/public flows were covered on the exact validation SHA.
- [x] Running security validation covered CSP/security headers, cookies, debug-disabled behavior, representative sensitive-error behavior, rate limits, structured logging, request correlation and audit boundaries.
- [x] A real SMTP protocol path to a safe test service was exercised together with unavailable-mail behavior.
- [x] A minimal final production verification checklist was preserved without promoting staging evidence.

## Ownership

```yaml
owned_paths:
  - .github/workflows/phase7-production-like-validation.yml
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
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
  - PR #63 / 61f72ddda5c253f26c7d59aa7b6fce3506f120dc
blockers:
  - none for this archived staging-validation task
cross_repository_tasks:
  - authoritative Platform game-login bridge remains separately authorized work if required for launch scope
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T22:18:39Z
head: 61f72ddda5c253f26c7d59aa7b6fce3506f120dc
branch: task/OTERYN-20260720-phase7-production-evidence-collection
pr: 63
status: complete
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
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
proven:
  - PR #63 merged to main as 61f72ddda5c253f26c7d59aa7b6fce3506f120dc.
  - Final PR #63 head 7842f78ec4ac2d07d3800ffe8bde9809b055822d passed Phase 7 Production-Like Validation run 29779554130 / #9, required CI run 29779553687 / #759 and Agent Governance run 29779554188 / #679.
  - Final-head non-secret evidence artifact phase7-production-like-evidence-29779554130 recorded STAGING_PROVEN with digest sha256:e4b272dee3ac26ed789525b3191946b74a41685910f436db41ef8df49d64f96b.
  - Controlled deployment/migration, rollback, interrupted-release isolation, redeploy, DB privilege boundaries, Redis ACL/failure semantics, SMTP delivery/failure, full regression suite, live security smoke and backup/restore all passed.
  - Final-head controlled restore measured 105 ms with 13/13 tables, 11/11 migrations and matching validation-SHA probe.
  - No production secret, endpoint, private key, database dump or production credential was committed.
derived:
  - The staging-verifiable engineering scope assigned to this task is complete.
  - ADR 0007 later separated Phase 7 engineering completion from final production go-live verification; this does not change or promote this task's STAGING_PROVEN evidence.
unknown:
  - final production deployed SHA and environment-specific go-live facts remain outside this archived task and are governed by the Production Go-Live Gate
conflicts: []
first_failure:
  marker: none
  evidence: staging-verifiable scope completed successfully
rejected_hypotheses:
  - Staging evidence proves final production: rejected.
  - Staging restore timing establishes production RTO/RPO: rejected.
changed_paths:
  - .github/workflows/phase7-production-like-validation.yml
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
validation:
  - command: Phase 7 Production-Like Validation run 29779554130 / #9 on 7842f78ec4ac2d07d3800ffe8bde9809b055822d
    result: PASS
    evidence: all staging-verifiable validation steps passed on the final PR #63 head
  - command: CI run 29779553687 / #759
    result: PASS
    evidence: Composer validation/install/advisory audit, Pint, PHPStan and full tests passed
  - command: Agent Governance run 29779554188 / #679
    result: PASS
    evidence: checkpoint/governance validation passed on the final PR #63 head
blockers:
  - none
next_action: No further action for this archived task; final production verification is owned by the fail-closed Production Go-Live Gate under ADR 0007.
```
