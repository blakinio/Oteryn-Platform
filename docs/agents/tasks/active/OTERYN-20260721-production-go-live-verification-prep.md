---
task_id: OTERYN-20260721-production-go-live-verification-prep
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/agents/PROJECT_STATE.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
  - docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md
  - docs/testing/PRODUCTION_SMOKE_CHECKLIST.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
search_first:
  - open production/go-live PRs and issues
  - docs/operations/** production verification evidence records
  - docs/agents/tasks/active/** overlapping production ownership
optional_reads:
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
---

# OTERYN-20260721-production-go-live-verification-prep

## Goal

Prepare a durable, fail-closed, non-secret Production Go-Live verification evidence packet and execution handoff for issue #91. The task is repository-only: make the future production verification deterministic and auditable without performing production deployment, production mutation smoke or cross-repository writes.

## Acceptance criteria

- [ ] Create a durable production-verification evidence record that starts every production-specific fact as `UNKNOWN` and is tied to an exact deployed SHA only when real evidence exists.
- [ ] Map every mandatory gate section in `PRODUCTION_READINESS_CHECKLIST.md` to a non-secret evidence field, command or operator observation.
- [ ] Map `PRODUCTION_SMOKE_CHECKLIST.md` into an execution record that cannot be marked passing without direct production evidence.
- [ ] Record explicit preconditions for mutation smoke: authorized operator, rollback path and applicable production backup/restore evidence.
- [ ] Record the selected-launch-scope decision point for the authoritative Platform game-login bridge without changing external repositories.
- [ ] Preserve `Production Readiness: STAGING_PROVEN` and `Production Go-Live Gate: PENDING PRODUCTION VERIFICATION` until direct production proof exists.
- [ ] Do not commit credentials, tokens, private endpoints, copied `.env` content, database dumps, TOTP secrets or recovery codes.
- [ ] Update durable project/active-work state so the next operator has exactly one concrete next action.

## Ownership

```yaml
owned_paths:
  - docs/operations/PRODUCTION_VERIFICATION_EVIDENCE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260721-production-go-live-verification-prep.md
  - docs/agents/tasks/archive/OTERYN-20260721-production-go-live-verification-prep.md
modules:
  - ProductionOperations
  - AcceptanceTesting
dependencies:
  - issue #91
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
  - docs/testing/PRODUCTION_SMOKE_CHECKLIST.md
  - ADR 0007 production go-live separation
blockers:
  - none for repository-only preparation
cross_repository_tasks:
  - authoritative Platform game-login bridge remains separately authorized and is required only if selected launch scope includes Platform-originated game login
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T14:50:00+02:00
head: 8ab83cb2ab3be9685e9d5ea6915a20422dfee2d3
branch: task/OTERYN-20260721-production-go-live-verification-prep
pr: pending
status: implementing
context_routes:
  - testing
  - security
  - canary-integration
  - agent-governance
owned_paths:
  - docs/operations/PRODUCTION_VERIFICATION_EVIDENCE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260721-production-go-live-verification-prep.md
  - docs/agents/tasks/archive/OTERYN-20260721-production-go-live-verification-prep.md
proven:
  - main is 8ab83cb2ab3be9685e9d5ea6915a20422dfee2d3 after stale functional-visual task archival.
  - Functional Acceptance is STAGING_PROVEN and Visual UX Acceptance is PASS for the currently delivered staging-verifiable launch scope.
  - The authoritative Production Go-Live Gate remains PENDING PRODUCTION VERIFICATION.
  - Issue #91 tracks execution of the actual Production Go-Live Gate on the exact deployed release.
  - Repository policy requires stopping autonomous work before production deployment or irreversible production actions without explicit authorization.
  - Production-specific topology, deployment, backup/restore, mail, monitoring and exact deployed release facts remain UNKNOWN until directly proven.
derived:
  - The highest-value autonomous work now is to prepare a deterministic non-secret evidence packet and leave actual production execution blocked on explicit authorization and access.
unknown:
  - exact final deployed Oteryn Platform SHA
  - relevant deployed Canary/login-server versions
  - production deployment and verification authorization
  - selected launch-scope requirement for Platform-originated authoritative game login
conflicts: []
first_failure:
  marker: none
  evidence: no repository-only preparation blocker found
rejected_hypotheses:
  - Treat current main as the deployed production SHA: rejected because no production deployment evidence exists.
  - Promote staging evidence to production proof: rejected by ADR 0007 and the authoritative go-live checklist.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-production-go-live-verification-prep.md
validation:
  - command: live-state preflight
    result: PASS
    evidence: no overlapping open production go-live PR found; issue #91 created as the external execution tracker
blockers: []
next_action: Create docs/operations/PRODUCTION_VERIFICATION_EVIDENCE.md as a fail-closed non-secret execution record mapped to the authoritative go-live and production-smoke checklists.
```

## Notes

This preparation task must not perform production deployment, production mutation smoke or cross-repository writes. Those actions remain gated by issue #91 preconditions and explicit production authorization.
