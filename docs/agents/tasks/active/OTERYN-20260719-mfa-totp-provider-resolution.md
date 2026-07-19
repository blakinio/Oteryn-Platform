# OTERYN-20260719 MFA TOTP Provider Resolution

## Goal

Perform the smallest safe Phase 3 T3.4b follow-up: select and deterministically add a maintained TOTP provider dependency for Oteryn Platform MFA using real Composer dependency resolution and a generated `composer.lock`, without adding public MFA enrollment, challenge, recovery, disable or enforcement behavior.

The selected dependency is `pragmarx/google2fa:^9.0`. Full Laravel Fortify adoption remains outside this task because the Platform already owns a custom login, registration and password-recovery stack.

## Acceptance criteria

- [x] Archive merged `OTERYN-20260719-mfa-state-foundation` task record without changing its contents.
- [x] Evaluate maintained TOTP dependency options against the current Laravel 13 / PHP 8.5 Platform architecture and choose a bounded direct-provider candidate.
- [x] Run real Composer dependency resolution against the current repository state for `pragmarx/google2fa:^9.0`.
- [x] Commit `composer.json` and the Composer-generated `composer.lock` only after successful dependency resolution; never hand-edit the lockfile.
- [x] Confirm the resolved dependency graph installs from lockfile in CI.
- [x] Run Composer validation, lockfile install, Pint, PHPStan/Larastan level 10 and the full test suite on the generated dependency head.
- [x] Do not add application MFA generation/verification code, public routes, controllers, views, login challenge/enforcement, recovery-code consumption or Admin/RBAC semantics in this task.
- [x] Do not modify Canary/login-server repositories, shared credentials, game sessions or game-login policy.
- [x] Remove the ephemeral Composer-resolution workflow before readiness so no workflow change is merged to `main`.

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
  - none for T3.4b dependency-resolution scope
cross_repository_tasks:
  - blakinio/canary and opentibiabr/login-server remain read-only evidence only; no writes are authorized
```

## Security boundary

This task changes dependency metadata only. It does not make MFA active for any user and does not expose or alter an authentication request path.

Adding a TOTP library does not itself provide MFA enforcement. A later task must deliberately integrate enrollment confirmation, challenge enforcement, replay-resistant verification, recovery-code semantics, session handling, rate limiting and security audit within the existing Platform login stack.

Platform-only MFA must not be described as a global game-login gate while native Canary and external login-server authentication paths remain outside Platform policy.

## Provider resolution

A temporary task-owned workflow ran real Composer resolution on a GitHub-hosted runner using PHP 8.5 and Composer v2. The resolver executed `composer require pragmarx/google2fa:^9.0 --no-interaction --no-progress`, validated that only `composer.json` and `composer.lock` changed, committed those generated files, and was then deleted from the task branch.

The generated lockfile resolves:

- `pragmarx/google2fa` `v9.0.0`;
- `paragonie/constant_time_encoding` `v3.1.3` as the new transitive dependency.

Normal repository CI subsequently validated the metadata, installed dependencies from the generated lockfile, passed Pint, PHPStan/Larastan and the full test suite.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-19T13:32:00+02:00
head: cfcbc94bdbac251b34091076358f3434acc7786f
branch: task/OTERYN-20260719-mfa-totp-provider-resolution
pr: 15
status: validating
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
  - Resolver workflow run 29684799693 completed successfully and every resolver step passed, including real Composer resolution, dependency-only working-tree verification and commit/push of generated metadata.
  - Composer generated a direct requirement pragmarx/google2fa ^9.0 and locked pragmarx/google2fa v9.0.0 plus paragonie/constant_time_encoding v3.1.3.
  - The ephemeral resolver workflow was deleted from the task branch after successful resolution and is absent from the intended final merge diff.
  - Normal CI run 29685009921 passed Composer validation, lockfile installation, Pint, PHPStan/Larastan and the full test suite on dependency head cfcbc94bdbac251b34091076358f3434acc7786f.
  - No application MFA flow, public auth route, Admin/RBAC semantic, Canary/login-server write or global game-login MFA claim was added.
  - Trust boundary affected by the final task output is dependency metadata only; no authentication request path, secret state transition or session policy changes.
  - Canary/login-server schema or session compatibility changes: none.
  - Secrets or production-only configuration involved: none.
derived:
  - A direct TOTP provider remains a smaller integration surface than adopting Fortify wholesale into the current custom auth stack.
  - Dependency resolution remains separate from public MFA behavior so the project does not ship half-integrated enrollment or challenge enforcement.
unknown:
  - Exact future replay-prevention persistence, recovery-code consumption and login challenge integration remain outside this task.
conflicts:
  - Platform web MFA still cannot be represented as global game-login MFA while alternate Canary/login-server auth paths remain.
first_failure:
  marker: Agent Governance run 29685009894 / Validate active task checkpoints
  evidence: the checkpoint used unsupported validation result PENDING; GOVERNANCE_CONTRACT.json allows only PASS, FAIL, BLOCKED and NOT_RUN
rejected_hypotheses:
  - Manually edit composer.json and composer.lock from package metadata: rejected because the lockfile must be generated by Composer against the exact dependency graph.
  - Adopt Laravel Fortify wholesale in T3.4b: rejected because it is broader than dependency resolution and overlaps the existing custom Platform authentication stack.
  - Implement TOTP/HOTP directly in Platform code: rejected because security architecture requires maintained mechanisms rather than custom cryptography.
  - Add public MFA enrollment before challenge enforcement is complete: rejected because it would create configured MFA state without a complete enforced authentication path.
changed_paths:
  - composer.json
  - composer.lock
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/tasks/archive/OTERYN-20260719-mfa-state-foundation.md
  - docs/agents/tasks/active/OTERYN-20260719-mfa-totp-provider-resolution.md
validation:
  - command: PR #14 final merge gate
    result: PASS
    evidence: squash merge produced 6109e48d28c305f24a8db56389a433b4a4876750 after final-head CI, Agent Governance, divergence and review gates passed
  - command: git clone --depth 1 https://github.com/blakinio/Oteryn-Platform.git
    result: BLOCKED
    evidence: local execution environment could not resolve github.com, so no local dependency resolution claim is made
  - command: composer require pragmarx/google2fa:^9.0 --no-interaction --no-progress
    result: PASS
    evidence: ephemeral GitHub Actions resolver run 29684799693 completed successfully and committed Composer-generated composer.json/composer.lock only
  - command: composer validate --strict; composer install --no-interaction --prefer-dist --no-progress; composer format:check; composer analyse; composer test
    result: PASS
    evidence: normal CI run 29685009921 passed all required steps on dependency head cfcbc94bdbac251b34091076358f3434acc7786f
  - command: python tools/agents/test_checkpoint.py; python tools/agents/checkpoint.py --tasks docs/agents/tasks/active --require-checkpoint
    result: FAIL
    evidence: Agent Governance run 29685009894 rejected the previous checkpoint because validation result PENDING is outside the governance contract; this checkpoint replaces it with supported results
blockers:
  - none for T3.4b dependency-resolution scope
next_action: Re-run normal CI and Agent Governance on the final documentation checkpoint head, then perform the full PR #15 merge gate and squash-merge if both checks pass.
```

## Notes

This task remains dependency-resolution-only. The next MFA implementation task may start only after PR #15 is merged and the generated dependency graph remains green on the exact final head.