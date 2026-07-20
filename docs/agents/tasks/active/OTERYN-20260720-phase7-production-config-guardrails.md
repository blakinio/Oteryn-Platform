# OTERYN-20260720-phase7-production-config-guardrails

## Goal

Add provider-independent, fail-closed production configuration guardrails that can be run before deployment without assuming a hosting provider or exposing secrets.

## Acceptance criteria

- [ ] A reusable verifier reports unsafe production configuration without echoing secret values.
- [ ] The verifier requires production environment mode, debug disabled, a configured application encryption key, HTTPS non-loopback APP_URL, Secure and HttpOnly session cookies, and a real delivery-capable mail transport/from address for implemented password-recovery flows.
- [ ] Provider-specific choices such as database engine, cache/session backend, queue backend, logging sink and Cloudflare policy are not incorrectly hard-coded as universal requirements.
- [ ] An Artisan command exits non-zero on violations and zero only when all invariant checks pass.
- [ ] Focused tests prove each unsafe invariant is rejected and a compliant configuration passes.
- [ ] No secret, credential, production endpoint, deployment action, external repository or payment functionality is introduced.

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
  - docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md
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
updated_at: 2026-07-20T11:10:00Z
head: 676a77590e3ec93bcad0247b3065d203ac209c40
branch: task/OTERYN-20260720-phase7-production-config-guardrails
pr: none
status: implementing
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
  - docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md
proven:
  - PR #48 merged as 676a77590e3ec93bcad0247b3065d203ac209c40 and established the Phase 7 evidence baseline.
  - config/app.php exposes environment, debug, URL and encryption key through configuration.
  - config/session.php exposes Secure and HttpOnly cookie settings.
  - config/mail.php supports smtp/log/array transports; .env.example explicitly says production should configure a real mail transport.
  - Password recovery is an implemented production-facing Identity capability, so a non-delivery mail transport would make that flow operationally non-functional.
derived:
  - Production environment/debug/key/HTTPS/cookie/mail checks are provider-independent invariants and can be verified without knowing the hosting provider.
  - Database engine, cache/session storage backend, queue backend, logging sink and Cloudflare policy depend on actual topology/use-case evidence and must not be universal hard failures in this verifier.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: implementation not yet validated
rejected_hypotheses:
  - Production must always use MySQL: rejected because repository supports SQLite/MySQL and actual deployed DB topology remains UNKNOWN.
  - Production must always use Redis sessions/cache: rejected because scaling topology remains UNKNOWN and current repository has no Platform Redis cache store.
  - Production must always use an asynchronous queue: rejected because current application queue configuration is synchronous and no current required background workload proves otherwise.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-production-topology-discovery.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-production-config-guardrails.md
validation:
  - command: GitHub Actions on draft PR head
    result: NOT_RUN
    evidence: implementation not yet pushed
blockers:
  - none
next_action: Open the draft PR, implement the secret-free verifier/command, and add focused regression tests.
```

## Notes

Violation messages must name the unsafe setting/class of problem without printing application keys, passwords, credentials or full sensitive connection values.
