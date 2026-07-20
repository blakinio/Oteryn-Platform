# OTERYN-20260720-phase7-dependency-security-scanning

## Goal

Add repository-owned dependency vulnerability scanning and automated dependency update coverage for the current Composer and GitHub Actions dependency surfaces.

## Acceptance criteria

- [ ] Required CI fails when Composer reports a known security advisory for locked/installed dependencies.
- [ ] Composer audit runs as an explicit named CI step without weakening existing Composer/Pint/PHPStan/test gates.
- [ ] Dependabot is configured for Composer and GitHub Actions with bounded scheduled update PRs.
- [ ] No production deployment, secret, external repository, dependency version bump or payment functionality is introduced by this task.
- [ ] Test/architecture documentation reflects dependency security scanning as part of the Phase 7 repository gate.
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
updated_at: 2026-07-20T11:38:00Z
head: 0f876d4f2209399a85cafcff1623d8e6c810b914
branch: task/OTERYN-20260720-phase7-dependency-security-scanning
pr: none
status: implementing
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
  - Current CI validates Composer metadata, installs locked dependencies, runs Pint, PHPStan and the full test suite, but has no explicit dependency vulnerability audit step.
  - composer.json uses a committed composer.lock workflow and current CI installs from that lockfile.
  - The repository has no Dependabot configuration found by targeted search at task start.
  - GitHub Actions workflow dependencies are version-pinned by major tags but currently have no repository-owned update automation.
derived:
  - Composer audit belongs in required CI because a known advisory in shipped locked dependencies should fail the repository gate before merge.
  - Dependabot update automation complements but does not replace fail-closed vulnerability scanning.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: implementation not yet validated
rejected_hypotheses:
  - Dependabot alone is sufficient vulnerability scanning: rejected because update automation does not provide a required merge-time fail-closed advisory gate.
  - This task should upgrade dependencies immediately: rejected because dependency upgrades should remain separate reviewable PRs unless a current advisory forces a bounded remediation task.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-production-config-guardrails.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-dependency-security-scanning.md
validation:
  - command: GitHub Actions on draft PR head
    result: NOT_RUN
    evidence: implementation not yet pushed
blockers:
  - none
next_action: Open the draft PR, add Composer audit to CI and configure bounded Dependabot updates for Composer and GitHub Actions.
```
