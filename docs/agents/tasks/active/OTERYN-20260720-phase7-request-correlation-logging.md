# OTERYN-20260720-phase7-request-correlation-logging

## Goal

Add provider-neutral request correlation and structured logging primitives without assuming or claiming a deployed centralized logging/metrics provider.

## Acceptance criteria

- [x] Every Laravel-handled request receives a server-generated request identifier that does not trust a browser-supplied correlation value.
- [x] Normal responses expose the generated identifier through `X-Request-ID`.
- [x] Completed requests emit one structured-context application log event with request ID, HTTP method, route name, status and bounded duration, without request body/query/credentials.
- [x] A selectable JSON-to-stderr logging channel exists for container/platform log collection without making it the universal local default.
- [x] Tests prove request IDs are UUIDs, attacker-supplied IDs are not reflected, IDs differ between requests, and logging context excludes sensitive request data.
- [x] No external observability provider, production log sink, secret, deployment action, external repository or payment functionality is introduced.

## Ownership

```yaml
owned_paths:
  - app/Http/Middleware/RequestCorrelation.php
  - bootstrap/app.php
  - config/logging.php
  - tests/Feature/Operations/RequestCorrelationTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-request-correlation-logging.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-security-headers-csp.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md
modules:
  - PlatformOperations
  - Web
dependencies:
  - PR #54 / eb358a245f35fda1865f13e329c07ef0f4850d2f
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T13:05:00Z
head: 61b35564cac3aacfa788a692affe1553698f2029
branch: task/OTERYN-20260720-phase7-request-correlation-logging
pr: 55
status: validating
context_routes:
  - security
  - testing
  - architecture
  - agent-governance
owned_paths:
  - app/Http/Middleware/RequestCorrelation.php
  - bootstrap/app.php
  - config/logging.php
  - tests/Feature/Operations/RequestCorrelationTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-request-correlation-logging.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-security-headers-csp.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md
proven:
  - RequestCorrelation generates a new server-owned UUID per request and does not copy inbound X-Request-ID.
  - Normal responses receive X-Request-ID and request completion logging is bounded to request_id, method, route, status and duration_ms.
  - Request log context intentionally excludes full URLs, query strings, request bodies, headers and credentials.
  - config/logging.php exposes an optional stderr_json Monolog channel while preserving the existing default logging choice.
  - The temporary diagnostic workflow captured the exact PHPStan errors and was removed before merge readiness.
  - Exact diagnostic: PHPStan rejected static Log::shouldHaveReceived() in RequestCorrelationTest; the test now keeps the typed object returned by Log::spy() and asserts on that object instead.
  - Agent Governance #631 and #632 passed before the temporary debug-workflow checkpoint drift; the final synchronized head still requires fresh validation.
derived:
  - Server-generated correlation is provider-neutral and can be collected by a future centralized sink without trusting client-supplied IDs.
  - Passing repository tests can prove safe application log shape but cannot prove a deployed centralized log/metrics/alerting service.
unknown: []
conflicts: []
first_failure:
  marker: CI #711/#712 Run static analysis
  evidence: PHPStan reported three errors in RequestCorrelationTest for undefined static Log::shouldHaveReceived() and chained calls on mixed; diagnostic artifact identified the exact lines.
rejected_hypotheses:
  - Trust inbound X-Request-ID as authoritative correlation: rejected because it is untrusted browser input and can enable spoofed log correlation.
  - Hard-code a specific observability vendor: rejected because actual production logging/metrics topology remains UNKNOWN.
  - Log full URLs, query strings or request bodies: rejected because they can contain credentials, tokens or unnecessary personal data.
  - The only PHPStan issue was the callback parameter typing: rejected because CI #712 still failed after runtime narrowing.
changed_paths:
  - app/Http/Middleware/RequestCorrelation.php
  - bootstrap/app.php
  - config/logging.php
  - tests/Feature/Operations/RequestCorrelationTest.php
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-security-headers-csp.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-request-correlation-logging.md
validation:
  - command: CI #711 and #712
    result: FAIL
    evidence: Composer advisory audit and Pint passed; PHPStan failed before tests.
  - command: temporary Phase 7 Correlation Debug workflow run 1
    result: EXPECTED_FAIL_DIAGNOSTIC
    evidence: artifact identified static Log::shouldHaveReceived() as the exact PHPStan root cause; workflow removed after use.
  - command: full CI and Agent Governance after exact fix and debug-workflow removal
    result: NOT_RUN
    evidence: required before documentation synchronization and merge.
blockers:
  - none
next_action: Run full CI and Agent Governance on the fixed branch; if green, synchronize Phase 7 state/evidence and complete exact-head merge validation.
```

## Notes

No temporary diagnostic workflow remains on the branch. The merged implementation must contain only the provider-neutral request-correlation/logging changes and their tests/documentation.
