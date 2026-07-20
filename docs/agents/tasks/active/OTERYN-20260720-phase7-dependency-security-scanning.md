# OTERYN-20260720-phase7-dependency-security-scanning

## Goal

Add repository-owned dependency vulnerability scanning and automated dependency update coverage for the current Composer and GitHub Actions dependency surfaces.

## Acceptance criteria

- [x] Required CI fails when Composer reports a known security advisory for locked/installed dependencies.
- [x] Composer audit runs as an explicit named CI step without weakening existing Composer/Pint/PHPStan/test gates.
- [x] Dependabot is configured for Composer and GitHub Actions with bounded scheduled update PRs.
- [x] No production deployment, secret, external repository, dependency version bump or payment functionality is introduced by this task.
- [x] Test/architecture documentation reflects dependency security scanning as part of the Phase 7 repository gate.
- [ ] Exact-head CI and Agent Governance pass before merge.

## Ownership

```yaml
owned_paths:
  - .github/dependabot.yml
  - .github/workflows/ci.yml
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-dependency-security-scanning.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-production-config-guardrails.md
modules:
  - PlatformOperations
dependencies:
  - PR #49 / 0f876d4f2209399a85cafcff1623d8e6c810b914
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T11:58:00Z
head: bc6fea68decb300aeb14e02cb4b3b8875c4af6b8
branch: task/OTERYN-20260720-phase7-dependency-security-scanning
pr: 50
status: validating
context_routes:
  - security
  - testing
  - ci-repair
  - agent-governance
owned_paths:
  - .github/dependabot.yml
  - .github/workflows/ci.yml
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-dependency-security-scanning.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-production-config-guardrails.md
proven:
  - PR #49 merged as 0f876d4f2209399a85cafcff1623d8e6c810b914 after exact-head CI #681 and Agent Governance #602.
  - CI now includes an explicit Audit Composer dependencies step running composer audit --no-interaction after install from the committed lockfile.
  - CI #687 completed the Composer audit successfully on the current dependency set and preserved successful Composer validation/install, Pint, PHPStan and full tests.
  - Dependabot configuration schedules bounded weekly update PRs for Composer and GitHub Actions.
  - No dependency version was changed directly by this task.
  - TEST_STRATEGY documents the Composer advisory gate and distinguishes Dependabot update automation from required vulnerability scanning.
  - Agent Governance #608 passed on bc6fea68decb300aeb14e02cb4b3b8875c4af6b8.
derived:
  - A future known Composer advisory will fail required CI before merge while Dependabot can independently propose bounded dependency updates.
  - GitHub Actions update automation reduces stale workflow dependency risk but does not itself prove action security.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: Composer audit found no advisory in the validated current lockfile
rejected_hypotheses:
  - Dependabot alone is sufficient vulnerability scanning: rejected because update automation does not provide a required merge-time fail-closed advisory gate.
  - This task should upgrade dependencies immediately: rejected because upgrades remain separate reviewable PRs unless an advisory forces remediation.
changed_paths:
  - .github/dependabot.yml
  - .github/workflows/ci.yml
  - docs/architecture/TEST_STRATEGY.md
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-dependency-security-scanning.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-production-config-guardrails.md
validation:
  - command: CI #687 on bc6fea68decb300aeb14e02cb4b3b8875c4af6b8
    result: PASS
    evidence: Composer audit plus existing formatting/static-analysis/test gates passed.
  - command: Agent Governance #608 on bc6fea68decb300aeb14e02cb4b3b8875c4af6b8
    result: PASS
    evidence: active checkpoint validation passed.
  - command: final exact-head CI and Agent Governance after documentation synchronization
    result: NOT_RUN
    evidence: required before squash merge.
blockers:
  - none
next_action: Synchronize PROJECT_STATE, verify final exact-head required checks, and squash-merge PR #50 if the merge gate remains satisfied.
```
