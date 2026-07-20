# OTERYN-20260720-phase7-dependency-security-scanning

## Goal

Add repository-owned dependency vulnerability scanning and automated dependency update coverage for the current Composer and GitHub Actions dependency surfaces.

## Acceptance criteria

- [x] Required CI fails when Composer reports a known security advisory for locked/installed dependencies.
- [x] Composer audit runs as an explicit named CI step without weakening existing Composer/Pint/PHPStan/test gates.
- [x] Dependabot is configured for Composer and GitHub Actions with bounded scheduled update PRs.
- [x] No production deployment, secret, external repository, dependency version bump or payment functionality is introduced by this task.
- [x] Test/architecture documentation reflects dependency security scanning as part of the Phase 7 repository gate.
- [x] Exact-head CI and Agent Governance pass before merge.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T12:05:00Z
head: 3973774727c35aea22d0a646f479a0ff079042cc
branch: task/OTERYN-20260720-phase7-dependency-security-scanning
pr: 50
status: completed
proven:
  - Composer advisory scanning is a required CI step through composer audit --no-interaction.
  - CI #689 and Agent Governance #610 passed on exact head 675f10b142b4247a10ecb74a6598daf81a65bf1f.
  - The validated current Composer lockfile contained no reported advisory.
  - Dependabot is configured for bounded weekly Composer and GitHub Actions update PRs.
  - PR #50 was squash-merged to main as 3973774727c35aea22d0a646f479a0ff079042cc.
blockers:
  - none
next_action: Continue Phase 7 with repository-owned security headers and CSP hardening while deployment-specific infrastructure remains evidence-blocked.
```
