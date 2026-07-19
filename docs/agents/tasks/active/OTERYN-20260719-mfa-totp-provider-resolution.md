# OTERYN-20260719 MFA TOTP Provider Resolution

## Goal

Perform the smallest safe Phase 3 T3.4b follow-up: select and deterministically add a maintained TOTP provider dependency for Oteryn Platform MFA using real Composer dependency resolution and a generated `composer.lock`, without adding public MFA enrollment, challenge, recovery, disable or enforcement behavior.

The preferred dependency candidate is `pragmarx/google2fa:^9.0`. Full Laravel Fortify adoption is intentionally outside this task because the Platform already owns a custom login, registration and password-recovery stack.

## Acceptance criteria

- [x] Archive merged `OTERYN-20260719-mfa-state-foundation` task record without changing its contents.
- [x] Evaluate maintained TOTP dependency options against the current Laravel 13 / PHP 8.5 Platform architecture and choose a bounded direct-provider candidate.
- [ ] Run real Composer dependency resolution against the current repository state for `pragmarx/google2fa:^9.0`.
- [ ] Commit `composer.json` and the Composer-generated `composer.lock` only if dependency resolution succeeds; never hand-edit the lockfile.
- [ ] Confirm the resolved dependency graph installs from lockfile in CI.
- [ ] Run Composer validation, lockfile install, Pint, PHPStan/Larastan level 10 and the full test suite on the exact final head.
- [x] Do not add application MFA generation/verification code, public routes, controllers, views, login challenge/enforcement, recovery-code consumption or Admin/RBAC semantics in this task.
- [x] Do not modify Canary/login-server repositories, shared credentials, game sessions or game-login policy.
- [ ] Remove the ephemeral Composer-resolution workflow before readiness so no workflow change is merged to `main`.

## Ownership

```yaml
owned_paths:
  - composer.json
  - composer.lock
  - .github/workflows/mfa-totp-provider-resolution.yml
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-mfa-totp-provider-resolution.md
  - docs/agents/tasks/archive/OTERYN-20260719-mfa-state-foundation.md
modules:
  - Identity MFA dependency boundary
  - security
  - dependency-management
  - testing
  - agent-governance
dependencies:
  - OTERYN-20260719-mfa-state-foundation
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
  - composer.json
  - composer.lock
blockers:
  - local execution environment cannot resolve github.com; use only the bounded ephemeral GitHub Actions resolver described below, and remove it before merge
cross_repository_tasks:
  - blakinio/canary and opentibiabr/login-server remain read-only evidence only; no writes are authorized
```

## Security boundary

This task changes dependency metadata only. It must not make MFA active for any user and must not expose or alter an authentication request path.

Adding a TOTP library does not itself provide MFA enforcement. A later task must deliberately integrate enrollment confirmation, challenge enforcement, replay-resistant verification, recovery-code semantics, session handling, rate limiting and security audit within the existing Platform login stack.

Platform-only MFA must not be described as a global game-login gate while native Canary and external login-server authentication paths remain outside Platform policy.

## Provider decision

`pragmarx/google2fa:^9.0` is the preferred candidate because it is a bounded direct TOTP provider and avoids implicitly replacing the Platform-owned auth stack. The choice remains provisional until real Composer resolution succeeds against the exact current lockfile.

## Ephemeral resolution mechanism

Because the local execution environment cannot resolve `github.com`, this task may temporarily add `.github/workflows/mfa-totp-provider-resolution.yml` on the task branch only. The workflow must:

- run only for PR #15 branch changes involving that workflow file;
- use PHP 8.5 and Composer v2 on a GitHub-hosted runner;
- execute real `composer require pragmarx/google2fa:^9.0 --no-interaction --no-progress`;
- fail if the command changes any tracked path other than `composer.json` or `composer.lock`;
- commit and push only the Composer-generated `composer.json` and `composer.lock` back to the same task branch;
- use no repository secrets beyond the scoped GitHub token;
- be deleted from the task branch after successful resolution and before merge readiness.

This mechanism is not a CI bypass: the generated dependency state still must pass the normal exact-head CI and Agent Governance merge gate after the resolver workflow is removed.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T13:25:00+02:00
head: 5d01beca7bfa4c1fbca1ff72fec60e71616a0d07
branch: task/OTERYN-20260719-mfa-totp-provider-resolution
pr: 15
status: investigating
context_routes:
  - agent-governance
  - auth-identity
  - security
  - dependency-management
  - testing
owned_paths:
  - composer.json
  - composer.lock
  - .github/workflows/mfa-totp-provider-resolution.yml
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-mfa-totp-provider-resolution.md
  - docs/agents/tasks/archive/OTERYN-20260719-mfa-state-foundation.md
proven:
  - PR #14 for OTERYN-20260719-mfa-state-foundation was squash-merged to main as 6109e48d28c305f24a8db56389a433b4a4876750 after the full merge gate passed.
  - The merged T3.4a scope provides encrypted Platform-owned two_factor_* state and internal reset/session-revocation primitives only; no public MFA behavior exists yet.
  - Current composer.json/composer.lock do not contain a TOTP provider.
  - Draft PR #15 is the coordination surface for this dependency-resolution-only task.
  - Local git clone retries fail with `Could not resolve host: github.com`.
  - Existing repository CI runs PHP 8.5 with Composer v2 and validates metadata, installs from lockfile, checks Pint, PHPStan/Larastan and the full test suite.
  - A bounded branch-only ephemeral GitHub Actions resolver can perform real Composer resolution without hand-editing composer.lock; it must be removed before readiness and normal CI remains authoritative.
  - Trust boundary affected by the final task output: dependency metadata only; no authentication request path, secret state transition or session policy may change.
  - Canary/login-server schema or session compatibility changes: none.
  - Secrets or production-only configuration involved: none.
derived:
  - A direct TOTP provider is a smaller integration surface than adopting Fortify wholesale into the current custom auth stack.
  - Dependency resolution must remain separate from public MFA behavior so the project never ships half-integrated enrollment or challenge enforcement.
unknown:
  - Exact transitive dependency versions Composer will select remain unknown until the ephemeral resolver completes.
  - Exact future replay-prevention persistence and challenge integration remain outside this task.
conflicts:
  - Hand-editing composer.lock is prohibited and will not be used.
first_failure:
  marker: local git clone / dependency-resolution environment
  evidence: `git clone --depth 1 https://github.com/blakinio/Oteryn-Platform.git` failed because github.com could not be resolved
rejected_hypotheses:
  - Manually edit composer.json and composer.lock from package metadata: rejected because the lockfile must be generated by Composer against the exact dependency graph.
  - Adopt Laravel Fortify wholesale in T3.4b: rejected because it is broader than dependency resolution and overlaps the existing custom Platform authentication stack.
  - Implement TOTP/HOTP directly in Platform code: rejected because security architecture requires maintained mechanisms rather than custom cryptography.
  - Add public MFA enrollment before challenge enforcement is complete: rejected because it would create configured MFA state without a complete enforced authentication path.
changed_paths:
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/archive/OTERYN-20260719-mfa-state-foundation.md
  - docs/agents/tasks/active/OTERYN-20260719-mfa-totp-provider-resolution.md
validation:
  - command: PR #14 final merge gate
    result: PASS
    evidence: squash merge produced 6109e48d28c305f24a8db56389a433b4a4876750 after final-head CI, Agent Governance, divergence and review gates passed
  - command: git clone --depth 1 https://github.com/blakinio/Oteryn-Platform.git
    result: BLOCKED
    evidence: Could not resolve host: github.com
  - command: composer require pragmarx/google2fa:^9.0 --no-interaction --no-progress
    result: PENDING
    evidence: authorized to run only through the ephemeral branch-only GitHub Actions resolver; no manual lockfile edit is permitted
blockers:
  - local DNS remains unavailable; dependency resolution now proceeds only through the bounded ephemeral GitHub Actions runner mechanism
next_action: Add the ephemeral resolver workflow, inspect its exact run and generated diff, remove the workflow, then validate the resulting dependency-only final head with normal CI and Agent Governance.
```

## Notes

This task remains dependency-resolution-only. The next MFA implementation task may start only after this task produces a Composer-generated lockfile and current-head CI proves the dependency graph installs cleanly.