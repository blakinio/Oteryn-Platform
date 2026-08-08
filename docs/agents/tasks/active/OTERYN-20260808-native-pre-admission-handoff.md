---
task_id: OTERYN-20260808-native-pre-admission-handoff
repository: blakinio/Oteryn-Platform
issue: 888
required_reads:
  - AGENTS.md
  - AGENTS.override.md
  - docs/agents/AGENTS.md
  - docs/agents/EXECUTION_PROTOCOL.md
  - docs/agents/BUILD_TEST_MATRIX.md
  - docs/architecture/ARCHITECTURE_AUTHORITY.md
  - docs/architecture/adr/0031-native-oteryn-v2-integration-boundary.md
  - docs/architecture/OTERYN_V2_INTEGRATION_ARCHITECTURE.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
  - docs/contracts/OTERYN_NATIVE_GAMEPLAY_PROTOCOL_CONTRACT.md
  - docs/contracts/OTERYN_V2_RUNTIME_STATUS_PROJECTION_CONTRACT.md
optional_reads:
  - read-only Oteryn-v2 ADR-0003 Platform Identity/Game Gateway/admission boundary
---

# OTERYN-20260808 native pre-admission handoff

## Goal

Define the Platform-side semantic boundary for native pre-admission material handed from Platform/Game Gateway to Oteryn-v2, preserving accepted authority while leaving final game-domain admission/session/lease/fencing implementation external and read-only.

## Acceptance criteria

- [ ] Focused contract defines pre-admission purpose, authority, canonical identities, binding, freshness, expiry and replay semantics.
- [ ] Pre-admission material is explicitly not canonical `GameSessionId`, gameplay lease or proof of final admission.
- [ ] Issuance preconditions compose authoritative ticket redemption, character authorization, World Registry policy and fresh applicable runtime evidence.
- [ ] Failure, ambiguity, duplicate/replay, channel-switch and reconnect boundaries fail closed without inventing Oteryn-v2 implementation.
- [ ] Focused integration and Gateway/Identity documentation route to the contract without reactivating historical Platform gameplay authority.
- [ ] Oteryn-v2 remains read-only; exact transport/encoding/signing/lease/fencing/GameSessionId wire format remain deferred.
- [ ] Exact-head Agent Governance and repository-selected CI pass; full diff/review has zero unresolved material findings.
- [x] Runtime/browser E2E is `NOT_APPLICABLE` because this task is architecture/documentation only.

## Ownership

```yaml
owned_paths:
  - docs/contracts/OTERYN_V2_PRE_ADMISSION_HANDOFF_CONTRACT.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
  - docs/architecture/OTERYN_V2_INTEGRATION_ARCHITECTURE.md
  - docs/agents/reports/OTERYN-20260808-native-pre-admission-handoff.md
  - docs/agents/programs/OTERYN_PLATFORM_ARCHITECTURE_REVIEW.md
  - docs/agents/tasks/active/OTERYN-20260808-native-pre-admission-handoff.md
modules:
  - Identity
  - Integration
  - GameGateway
  - architecture-governance
dependencies:
  - Issue #888
  - ADR 0031
  - Oteryn-v2 ADR-0003 read-only evidence
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-08-08T09:25:00+02:00
head: pending-claim-commit
branch: docs/OTERYN-20260808-native-pre-admission-handoff
pr: none
status: investigating
phase: analyze
execution_mode: github_only
invocation_started_at: 2026-08-08T09:23:00+02:00
last_progress_at: 2026-08-08T09:25:00+02:00
ci_checks_for_current_head: 0
ci_check_generation: none
terminal_ci_wait_started_at: none
terminal_ci_checks_for_current_generation: 0
unchanged_state_checks: 0
identical_failure_retries: 0
repair_cycles_for_current_gate: 0
context_reconstruction_attempts: 0
stall_warnings: 0
context_routes:
  - architecture
  - security
  - api
  - operations
owned_paths:
  - docs/contracts/OTERYN_V2_PRE_ADMISSION_HANDOFF_CONTRACT.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
  - docs/architecture/OTERYN_V2_INTEGRATION_ARCHITECTURE.md
  - docs/agents/reports/OTERYN-20260808-native-pre-admission-handoff.md
  - docs/agents/programs/OTERYN_PLATFORM_ARCHITECTURE_REVIEW.md
  - docs/agents/tasks/active/OTERYN-20260808-native-pre-admission-handoff.md
proven:
  - Platform ADR 0031 owns Identity, Game Login Ticket, World Registry and Gateway pre-admission orchestration while assigning final gameplay admission and authoritative admitted session/lease/fencing to Oteryn-v2.
  - Read-only Oteryn-v2 ADR-0003 makes the same authority distinction and explicitly distinguishes pre-admission material from canonical logical GameSessionId.
  - Historical Platform native gameplay/Game Session v2 artifacts are reconciliation evidence only and cannot define current Oteryn-v2 admission/session authority.
derived:
  - A focused Platform semantic contract can close the Platform-side handoff ambiguity without selecting unfinished Oteryn-v2 wire or lease implementation.
unknown:
  - exact Oteryn-v2 FND-04 admission/session state machine, material transport/encoding/signing primitive, replay store, lease/fencing algorithm and canonical GameSessionId wire form
conflicts: []
first_failure:
  marker: none-observed
  evidence: fresh overlap search found no open Platform Issue/PR owning this focused admission-handoff architecture scope
rejected_hypotheses:
  - Platform-issued pre-admission material is a canonical gameplay session
  - historical Platform native Game Session v2 bytes define the Oteryn-v2 target
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260808-native-pre-admission-handoff.md
validation:
  - command: live overlap and authority preflight
    result: PASS
    evidence: Issue #888 is the bounded owner; Oteryn-v2 is read-only and no overlapping Platform PR/Issue was found by admission/session/lease handoff search.
  - command: runtime/browser E2E
    result: NOT_APPLICABLE
    evidence: architecture/documentation only; no executable runtime or deployment behavior is authorized.
blockers: []
next_action: Draft the focused Platform pre-admission semantic contract, reconcile Gateway/Identity and focused v2 architecture routing, then run full-diff review and exact-head validation.
```