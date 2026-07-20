# OTERYN-20260720-phase7-production-config-guardrails

## Goal

Add provider-independent, fail-closed production configuration guardrails that can be run before deployment without assuming a hosting provider or exposing secrets.

## Acceptance criteria

- [x] A reusable verifier reports unsafe production configuration without echoing secret values.
- [x] The verifier requires production environment mode, debug disabled, a configured application encryption key, HTTPS non-loopback APP_URL, Secure and HttpOnly session cookies, and a real delivery-capable mail transport/from address for implemented password-recovery flows.
- [x] Provider-specific choices such as database engine, cache/session backend, queue backend, logging sink and Cloudflare policy are not incorrectly hard-coded as universal requirements.
- [x] An Artisan command exits non-zero on violations and zero only when all invariant checks pass.
- [x] Focused tests prove each unsafe invariant is rejected and a compliant configuration passes.
- [x] No secret, credential, production endpoint, deployment action, external repository or payment functionality is introduced.

## Ownership

```yaml
owned_paths:
  - app/Operations/**
  - routes/console.php
  - tests/Feature/Operations/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-production-config-guardrails.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-production-topology-discovery.md
modules:
  - PlatformOperations
dependencies:
  - PR #48 / 676a77590e3ec93bcad0247b3065d203ac209c40
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T11:25:00Z
head: 59884b0b3e4e2f3645a11d11033f66a7c268cd33
branch: task/OTERYN-20260720-phase7-production-config-guardrails
pr: 49
status: ready
context_routes:
  - security
  - testing
  - architecture
  - agent-governance
owned_paths:
  - app/Operations/**
  - routes/console.php
  - tests/Feature/Operations/**
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-production-config-guardrails.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-production-topology-discovery.md
proven:
  - PR #48 merged as 676a77590e3ec93bcad0247b3065d203ac209c40 and established the Phase 7 evidence baseline.
  - ProductionConfigurationVerifier checks production environment mode, debug disabled, configured APP_KEY, HTTPS non-localhost/loopback APP_URL, Secure and HttpOnly session cookies, delivery-capable default mail transport and valid non-test sender address.
  - Violation output names unsafe configuration classes without printing APP_KEY or other secret values.
  - production:verify-configuration exits 1 on violations and 0 only when all checks pass.
  - Focused tests cover every configured invariant, compliant configuration, array/log mail rejection and secret non-disclosure in command output.
  - The verifier does not hard-code database engine, cache/session storage, queue backend, logging sink, Cloudflare policy or hosting provider.
  - PR #49 changed files are limited to the declared operations/test/task/state scope plus archival movement of the completed PR #48 task.
  - PR #49 has no comments or review threads.
  - CI #680 and Agent Governance #601 passed on 59884b0b3e4e2f3645a11d11033f66a7c268cd33.
derived:
  - The command can be inserted into a future deployment preflight without depending on provider-specific infrastructure choices.
  - Passing this verifier proves only repository-defined invariant configuration, not Cloudflare/origin/database/backup/monitoring deployment state.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: implementation passed formatting, PHPStan and full tests on the validated head
rejected_hypotheses:
  - Production must always use MySQL: rejected because actual deployed DB topology remains UNKNOWN.
  - Production must always use Redis sessions/cache: rejected because scaling topology remains UNKNOWN and current repository has no Platform Redis cache store.
  - Production must always use an asynchronous queue: rejected because no current required background workload proves otherwise.
changed_paths:
  - app/Operations/ProductionConfigurationVerifier.php
  - routes/console.php
  - tests/Feature/Operations/ProductionConfigurationVerifierTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-production-config-guardrails.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-production-topology-discovery.md
validation:
  - command: CI #680 on 59884b0b3e4e2f3645a11d11033f66a7c268cd33
    result: PASS
    evidence: Composer validation/install, Pint, PHPStan and full test suite passed.
  - command: Agent Governance #601 on 59884b0b3e4e2f3645a11d11033f66a7c268cd33
    result: PASS
    evidence: active checkpoint validation passed.
  - command: final exact-head CI and Agent Governance after this evidence-only update
    result: NOT_RUN
    evidence: required before squash merge.
blockers:
  - none
next_action: Verify required checks on the final evidence-only head and squash-merge PR #49 if the merge gate remains satisfied.
```

## Notes

Violation messages name the unsafe setting/class of problem without printing application keys, passwords, credentials or full sensitive connection values.
