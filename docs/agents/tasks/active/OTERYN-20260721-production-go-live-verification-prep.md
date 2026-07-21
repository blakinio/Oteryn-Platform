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

- [x] Create a durable production-verification evidence record that starts every production-specific fact as `UNKNOWN` and is tied to an exact deployed SHA only when real evidence exists.
- [x] Map every mandatory gate section in `PRODUCTION_READINESS_CHECKLIST.md` to a non-secret evidence field, command or operator observation.
- [x] Map `PRODUCTION_SMOKE_CHECKLIST.md` into an execution record that cannot be marked passing without direct production evidence.
- [x] Record explicit preconditions for mutation smoke: authorized operator, rollback path and applicable production backup/restore evidence.
- [x] Record the selected-launch-scope decision point for the authoritative Platform game-login bridge without changing external repositories.
- [x] Preserve `Production Readiness: STAGING_PROVEN` and `Production Go-Live Gate: PENDING PRODUCTION VERIFICATION` until direct production proof exists.
- [x] Do not commit credentials, tokens, private endpoints, copied `.env` content, database dumps, TOTP secrets or recovery codes.
- [x] Update durable project/active-work state so the next operator has exactly one concrete next action.

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
updated_at: 2026-07-21T14:58:00+02:00
head: e4601997730c6796fb2f81ff10cdff7b5eb862bd
branch: task/OTERYN-20260721-production-go-live-verification-prep
pr: 92
status: ready
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
  - main baseline for this task is 8ab83cb2ab3be9685e9d5ea6915a20422dfee2d3 after stale functional-visual task archival.
  - Functional Acceptance is STAGING_PROVEN and Visual UX Acceptance is PASS for the currently delivered staging-verifiable launch scope.
  - The authoritative Production Go-Live Gate remains PENDING PRODUCTION VERIFICATION.
  - Issue #91 tracks execution of the actual Production Go-Live Gate on the exact deployed release.
  - docs/operations/PRODUCTION_VERIFICATION_EVIDENCE.md now provides a fail-closed non-secret execution record mapped to release identity, effective production configuration, security, edge/origin, Platform DB, Canary SQL privileges, runtime Redis, sessions/cache/queue, mail, observability, deployment/rollback, identity/admin smoke, public/account/character smoke, game-login scope, browser security and backup/restore.
  - The evidence packet starts production-specific facts as UNKNOWN and requires direct evidence tied to the exact deployed release before PRODUCTION_PROVEN classification.
  - Mutation smoke is explicitly blocked until exact release identity, required CI, authorized operator, rollback path, applicable backup policy/restore evidence and incident escalation are proven.
  - The authoritative game-login bridge is an explicit launch-scope decision point and no external repository write was performed.
  - Repository policy requires stopping autonomous work before production deployment or irreversible production actions without explicit authorization.
derived:
  - Repository-only preparation is complete; actual production execution can now resume deterministically from issue #91 without re-discovering the evidence model.
unknown:
  - exact final deployed Oteryn Platform SHA
  - relevant deployed Canary/login-server versions
  - production deployment and verification authorization
  - production topology and direct environment evidence
  - selected launch-scope requirement for Platform-originated authoritative game login
conflicts: []
first_failure:
  marker: none
  evidence: no repository-only preparation blocker found
rejected_hypotheses:
  - Treat current main as the deployed production SHA: rejected because no production deployment evidence exists.
  - Promote staging evidence to production proof: rejected by ADR 0007 and the authoritative go-live checklist.
  - Execute production mutation smoke autonomously now: rejected because explicit production authorization, exact deployed release identity and mandatory preconditions are not proven.
changed_paths:
  - docs/operations/PRODUCTION_VERIFICATION_EVIDENCE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260721-production-go-live-verification-prep.md
validation:
  - command: live-state preflight
    result: PASS
    evidence: no overlapping open production go-live PR found; issue #91 created as the external execution tracker
  - command: evidence-model cross-check against Production Go-Live Gate and Final Production Smoke Checklist
    result: PASS
    evidence: durable packet covers all authoritative gate domains and conditional launch-scope decisions without embedding secrets or promoting staging evidence
  - command: local checkpoint validator
    result: NOT_RUN
    evidence: no synchronized local checkout is available in this connector-only session; Agent Governance on PR #92 is the authoritative repository check
blockers: []
next_action: Wait for all required PR #92 checks on the current head, then squash-merge PR #92 if the merge gate remains green.
```

## Notes

This preparation task must not perform production deployment, production mutation smoke or cross-repository writes. After merge/archive, issue #91 remains the only production execution tracker and stays blocked until its explicit production preconditions are met.
