# OTERYN-20260719 MFA TOTP Provider Resolution

## Goal

Perform the smallest safe Phase 3 T3.4b follow-up: select and deterministically add a maintained TOTP provider dependency for Oteryn Platform MFA using real Composer dependency resolution and a generated `composer.lock`, without adding public MFA enrollment, challenge, recovery, disable or enforcement behavior.

The preferred dependency candidate is `pragmarx/google2fa:^9.0`. Full Laravel Fortify adoption is intentionally not part of this task because the Platform already owns a custom login, registration and password-recovery stack and Fortify registers overlapping authentication and two-factor routes when enabled. Any future Fortify use must be a separate explicit integration decision rather than an incidental dependency side effect.

## Acceptance criteria

- [x] Archive merged `OTERYN-20260719-mfa-state-foundation` task record without changing its contents.
- [x] Evaluate maintained TOTP dependency options against the current Laravel 13 / PHP 8.5 Platform architecture and choose a bounded direct-provider candidate.
- [ ] Run real Composer dependency resolution against the current repository state for `pragmarx/google2fa:^9.0`.
- [ ] Commit `composer.json` and the Composer-generated `composer.lock` only if dependency resolution succeeds; never hand-edit the lockfile.
- [ ] Confirm the resolved dependency graph installs from lockfile in CI.
- [ ] Run Composer validation, lockfile install, Pint, PHPStan/Larastan level 10 and the full test suite on the exact final head.
- [x] Do not add application MFA generation/verification code, public routes, controllers, views, login challenge/enforcement, recovery-code consumption or Admin/RBAC semantics in this task.
- [x] Do not modify Canary/login-server repositories, shared credentials, game sessions or game-login policy.

## Ownership

```yaml
owned_paths:
  - composer.json
  - composer.lock
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
  - current execution environment cannot resolve github.com, so a local checkout and deterministic Composer dependency resolution cannot currently run
cross_repository_tasks:
  - blakinio/canary and opentibiabr/login-server remain read-only evidence only; no writes are authorized
```

## Security boundary

This task changes dependency metadata only. It must not make MFA active for any user and must not expose or alter an authentication request path.

Adding a TOTP library does not itself provide MFA enforcement. A later task must deliberately integrate enrollment confirmation, challenge enforcement, replay-resistant verification, recovery-code semantics, session handling, rate limiting and security audit within the existing Platform login stack.

Platform-only MFA must not be described as a global game-login gate while native Canary and external login-server authentication paths remain outside Platform policy.

## Provider decision

`pragmarx/google2fa:^9.0` is the preferred candidate for this bounded dependency task because it is a direct TOTP implementation dependency and avoids implicitly replacing the existing Platform authentication route/controller stack.

Current external package evidence reviewed on 2026-07-19:

- Packagist lists stable `pragmarx/google2fa` v9.0.0 and PHP 8.x compatibility, including the current package line used for PHP 8.5.
- Laravel Fortify currently supports Laravel 13, but Laravel documentation shows that enabled Fortify features register `/login`, password recovery and `/two-factor-challenge` routes; wholesale Fortify adoption therefore overlaps the Platform-owned authentication stack and is outside this dependency-only task.

This selection is provisional until real Composer resolution succeeds against the repository's exact current lockfile.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T13:06:30+02:00
head: 6109e48d28c305f24a8db56389a433b4a4876750
branch: task/OTERYN-20260719-mfa-totp-provider-resolution
pr: none
status: blocked
context_routes:
  - agent-governance
  - auth-identity
  - security
  - dependency-management
  - testing
owned_paths:
  - composer.json
  - composer.lock
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/active/OTERYN-20260719-mfa-totp-provider-resolution.md
  - docs/agents/tasks/archive/OTERYN-20260719-mfa-state-foundation.md
proven:
  - PR #14 for OTERYN-20260719-mfa-state-foundation was squash-merged to main as 6109e48d28c305f24a8db56389a433b4a4876750 after final-head CI run 29684289863 and Agent Governance run 29684289878 passed, branch divergence was behind_by 0, and no review/comment/thread blocker existed.
  - The merged T3.4a scope provides only encrypted Platform-owned two_factor_* state and internal reset/session-revocation primitives; no public MFA behavior exists yet.
  - Current composer.json/composer.lock do not contain a TOTP provider from the completed T3.4a scope.
  - Current package research identifies pragmarx/google2fa v9.0.0 as the stable direct-provider candidate and Laravel Fortify as a broader authentication package whose default route surface overlaps the existing Platform-owned auth stack.
  - A fresh local git clone retry after PR #14 merge failed with `Could not resolve host: github.com`.
  - Trust boundary affected by this task: dependency metadata only; no authentication request path, secret state transition or session policy may change.
  - Canary/login-server schema or session compatibility changes: none.
  - Rollback requirement: dependency addition, if resolved, must be reversible by a real Composer remove/update operation that regenerates the lockfile.
  - Secrets or production-only configuration involved: none.
derived:
  - A direct TOTP provider is a smaller integration surface than adopting Fortify wholesale into the current custom auth stack.
  - Dependency resolution must be separated from public MFA behavior so the project never ships half-integrated enrollment or challenge enforcement.
unknown:
  - Exact transitive dependency versions that Composer will select against the current lockfile remain unknown until real dependency resolution can run.
  - Exact future replay-prevention persistence and challenge integration remain outside this task.
conflicts:
  - Hand-editing composer.lock would violate repository governance and cannot be used to bypass the current environment blocker.
first_failure:
  marker: local git clone / dependency-resolution environment
  evidence: `git clone --depth 1 https://github.com/blakinio/Oteryn-Platform.git` failed because github.com could not be resolved
rejected_hypotheses:
  - Manually edit composer.json and composer.lock from package metadata: rejected because the lockfile must be generated by Composer against the exact dependency graph.
  - Adopt Laravel Fortify wholesale in T3.4b: rejected because it is broader than dependency resolution and overlaps the existing custom Platform authentication routes and controllers.
  - Implement TOTP/HOTP directly in Platform code: rejected because security architecture requires maintained mechanisms rather than custom cryptography.
  - Add public MFA enrollment before challenge enforcement is complete: rejected because it would create configured MFA state without a complete enforced authentication path.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260719-mfa-state-foundation.md
  - docs/agents/tasks/active/OTERYN-20260719-mfa-totp-provider-resolution.md
validation:
  - command: PR #14 final merge gate
    result: PASS
    evidence: final head b05def23854d33d6de321c509273bfd28efec597 passed CI 29684289863 and Agent Governance 29684289878; behind_by 0; no reviews, PR comments or review threads; squash merge produced 6109e48d28c305f24a8db56389a433b4a4876750
  - command: git clone --depth 1 https://github.com/blakinio/Oteryn-Platform.git
    result: BLOCKED
    evidence: Could not resolve host: github.com
  - command: composer require pragmarx/google2fa:^9.0 --no-interaction
    result: NOT_RUN
    evidence: no local checkout is available and the lockfile will not be fabricated or hand-edited
blockers:
  - deterministic Composer dependency resolution is blocked by the current execution environment DNS failure
next_action: Open a draft PR for coordination, update ACTIVE_WORK, and retry real Composer dependency resolution only when a working checkout/network path is available. Do not add public MFA behavior in this task.
```

## Notes

This task is intentionally allowed to remain blocked rather than weakening dependency integrity. The next implementation task may start only after this task produces a Composer-generated lockfile and current-head CI proves the dependency graph installs cleanly.
