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

- [ ] Revalidate the current Phase 7 roadmap exit gate against merged production-like staging evidence and exact-head CI evidence.
- [ ] Record a durable ADR deciding whether Phase 7 engineering completion is separate from final production go-live verification.
- [ ] If separation is accepted, mark Phase 7 engineering/hardening COMPLETE while preserving `STAGING_PROVEN` evidence classification and keeping all final production facts non-passing until directly proven.
- [ ] Establish the existing production readiness checklist, or an equivalent durable document, as a fail-closed Production Go-Live Gate independent from Phase 7 engineering completion.
- [ ] Keep exact deployed SHA, DNS/edge/TLS/origin/firewall, production DB/Redis, session/cache/queue, mail, observability, provider deployment controls, backup/restore and final smoke/E2E requirements pending until real production evidence exists.
- [ ] Do not declare production RTO/RPO from staging restore measurements and do not infer production DB/Redis privileges from staging.
- [ ] Do not modify runtime application behavior, Canary/login-server, payments or production infrastructure.
- [ ] Update roadmap/project/task status consistently and leave exactly one concrete next action.

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
updated_at: 2026-07-20T22:12:11Z
head: 221a13f6d7fba28ba765d67594a5cce4bf9523c4
branch: task/OTERYN-20260721-phase7-go-live-gate-separation
pr: none
status: investigating
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
  - docs/agents/tasks/active/OTERYN-20260720-phase7-production-evidence-collection.md
  - docs/agents/tasks/active/OTERYN-20260721-phase7-go-live-gate-separation.md
proven:
  - main HEAD is 221a13f6d7fba28ba765d67594a5cce4bf9523c4 after merged PR #64.
  - Current ROADMAP Phase 7 status is IN PROGRESS and its exit gate requires the production readiness checklist to be complete.
  - Current production readiness checklist explicitly requires actual deployed-environment evidence and states Phase 7 remains incomplete until those requirements are met.
  - PR #63 merged the production-like validation harness; final PR head 7842f78ec4ac2d07d3800ffe8bde9809b055822d passed Production-Like Validation #9, CI #759 and Agent Governance #679.
  - Production-like evidence remains correctly classified as STAGING_PROVEN and does not prove final production state.
  - No open pull request currently overlaps this governance/architecture scope.
derived:
  - The current documents conflate Phase 7 engineering completion with final production go-live verification even though the delivered Phase 7 mechanisms and controlled staging validation are complete.
  - Changing Phase 7 to COMPLETE without a durable architecture/roadmap decision would conflict with the current explicit exit gate.
unknown:
  - Whether the durable project model should accept separation of Phase 7 engineering completion from final production go-live verification; this task resolves that decision.
conflicts:
  - ROADMAP deliverables describe engineering/hardening mechanisms, while the current exit gate delegates completion to a checklist dominated by final production environment facts.
first_failure:
  marker: Phase 7 completion semantics
  evidence: current roadmap/checklist make final production deployment evidence an integral Phase 7 completion requirement despite completed staging-verifiable engineering scope
rejected_hypotheses:
  - Staging evidence is production evidence: rejected.
  - Phase 7 can be marked COMPLETE by status-only edit without an ADR/roadmap decision: rejected because current exit gate explicitly requires production readiness checklist completion.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260721-phase7-go-live-gate-separation.md
validation:
  - command: live GitHub preflight on main, PR #63, PR #64 and exact PR #63 head workflows
    result: PASS
    evidence: source-of-truth documents and workflow records were revalidated before implementation
blockers:
  - none
next_action: Create the durable ADR separating Phase 7 engineering completion from the fail-closed Production Go-Live Gate, then align ROADMAP and operations status documents without changing evidence classifications.
```

## Notes

This is a documentation/architecture/governance task only. It must not perform a production deployment or modify runtime behavior.
