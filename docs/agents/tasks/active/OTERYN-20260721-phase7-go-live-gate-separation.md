---
task_id: OTERYN-20260721-phase7-go-live-gate-separation
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/ROADMAP.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
search_first:
  - docs/agents/tasks/active/** for overlapping Phase 7/go-live ownership
  - open PRs for Phase 7/go-live status changes
optional_reads:
  - docs/agents/PROJECT_STATE.md
  - docs/agents/ACTIVE_WORK.md
---

# OTERYN-20260721-phase7-go-live-gate-separation

## Goal

Resolve the durable architecture/governance boundary between completed Phase 7 engineering hardening and the still-unproven final production go-live verification gate, without relabeling staging evidence as production evidence or weakening any fail-closed production requirement.

## Acceptance criteria

- [x] Revalidate the current Phase 7 roadmap exit gate against merged production-like staging evidence and exact-head CI evidence.
- [x] Record a durable ADR deciding whether Phase 7 engineering completion is separate from final production go-live verification.
- [x] Mark Phase 7 engineering/hardening COMPLETE while preserving `STAGING_PROVEN` evidence classification and keeping all final production facts non-passing until directly proven.
- [x] Establish the existing production readiness checklist as the fail-closed Production Go-Live Gate independent from Phase 7 engineering completion.
- [x] Keep exact deployed SHA, DNS/edge/TLS/origin/firewall, production DB/Redis, session/cache/queue, mail, observability, provider deployment controls, backup/restore and final smoke/E2E requirements pending until real production evidence exists.
- [x] Do not declare production RTO/RPO from staging restore measurements and do not infer production DB/Redis privileges from staging.
- [x] Do not modify runtime application behavior, Canary/login-server, payments or production infrastructure.
- [ ] Required GitHub checks pass on the final PR head and the completed task is archived consistently before merge.

## Ownership

```yaml
owned_paths:
  - docs/architecture/adr/0007-phase7-engineering-and-production-go-live-gate.md
  - docs/architecture/ROADMAP.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-production-evidence-collection.md
  - docs/agents/tasks/active/OTERYN-20260721-phase7-go-live-gate-separation.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-production-evidence-collection.md
  - docs/agents/tasks/archive/OTERYN-20260721-phase7-go-live-gate-separation.md
modules:
  - PlatformOperations
  - agent-governance
  - architecture
dependencies:
  - PR #63 / 61f72ddda5c253f26c7d59aa7b6fce3506f120dc
  - PR #64 / 221a13f6d7fba28ba765d67594a5cce4bf9523c4
blockers:
  - none
cross_repository_tasks:
  - authoritative Platform game-login bridge remains separately authorized work if required for launch scope
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T22:19:46Z
head: 1018ff189dde2f8fc74e548b76f6f900ca064db2
branch: task/OTERYN-20260721-phase7-go-live-gate-separation
pr: 65
status: validating
context_routes:
  - architecture
  - agent-governance
  - security
owned_paths:
  - docs/architecture/adr/0007-phase7-engineering-and-production-go-live-gate.md
  - docs/architecture/ROADMAP.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260721-phase7-go-live-gate-separation.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-production-evidence-collection.md
proven:
  - main HEAD at task start was 221a13f6d7fba28ba765d67594a5cce4bf9523c4 after merged PR #64.
  - The pre-change ROADMAP made `production readiness checklist complete` an integral Phase 7 exit gate, and the pre-change checklist explicitly required actual deployed-production evidence before Phase 7 completion.
  - PR #63 merged the production-like validation harness; final PR head 7842f78ec4ac2d07d3800ffe8bde9809b055822d passed Production-Like Validation #9, CI #759 and Agent Governance #679.
  - Latest final-head staging evidence is STAGING_PROVEN only: controlled restore 105 ms, 13/13 tables, 11/11 migrations and matching validation-SHA probe; no production RTO/RPO is inferred.
  - No open pull request overlapped this governance/architecture scope at task start.
  - ADR 0007 now separates Phase 7 engineering completion from the fail-closed Production Go-Live Gate while retaining the existing production readiness checklist as the single authoritative gate.
  - ROADMAP and PROJECT_STATE now classify Phase 7 COMPLETE, Production Readiness STAGING_PROVEN, Production Go-Live Gate PENDING PRODUCTION VERIFICATION and Production Verification REQUIRED BEFORE GO-LIVE.
  - Production-specific exact SHA, DNS/edge/TLS/origin/firewall, DB/Redis, session/cache/queue, mail, observability, provider deployment, backup/restore and final smoke/E2E facts remain UNKNOWN until directly proven.
  - The completed staging-evidence task OTERYN-20260720-phase7-production-evidence-collection has been moved from active to archive without changing its STAGING_PROVEN evidence.
derived:
  - Phase 7 engineering/hardening scope is complete because its repository-owned mechanisms are delivered and the staging-verifiable boundaries passed exact-SHA controlled validation.
  - Final production verification is an operational release gate rather than unfinished Phase 7 engineering work.
  - Owner risk acceptance is a governance decision, not an evidence state, and cannot fabricate PRODUCTION_PROVEN facts.
unknown:
  - final production go-live facts remain intentionally UNKNOWN pending direct production verification
conflicts: []
first_failure:
  marker: none
  evidence: the roadmap/checklist semantic conflict was resolved by accepted ADR 0007 without weakening production verification
rejected_hypotheses:
  - Staging evidence is production evidence: rejected.
  - Phase 7 can be marked COMPLETE by status-only edit without an ADR/roadmap decision: rejected; ADR 0007 provides the durable decision.
  - Keeping Phase 7 IN PROGRESS solely until a production deployment occurs is the best model: rejected because it conflates engineering completion with the external go-live gate.
  - Owner risk acceptance can convert UNKNOWN to PRODUCTION_PROVEN: rejected.
changed_paths:
  - docs/architecture/adr/0007-phase7-engineering-and-production-go-live-gate.md
  - docs/architecture/ROADMAP.md
  - docs/operations/PRODUCTION_READINESS_CHECKLIST.md
  - docs/operations/PRODUCTION_LIKE_VALIDATION_EVIDENCE.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-production-evidence-collection.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-production-evidence-collection.md
  - docs/agents/tasks/active/OTERYN-20260721-phase7-go-live-gate-separation.md
validation:
  - command: live GitHub preflight on main, PR #63, PR #64 and exact PR #63 head workflows
    result: PASS
    evidence: source-of-truth documents and workflow records were revalidated before implementation
  - command: PR #65 required GitHub checks
    result: PENDING
    evidence: final-head CI/governance checks are still running or not yet observed complete
blockers:
  - none
next_action: Inspect PR #65 exact-head CI and Agent Governance results; fix only task-owned failures, then archive this task and merge if the merge gate is satisfied.
```

## Notes

This is a documentation/architecture/governance task only. It performs no production deployment and changes no runtime application behavior.
