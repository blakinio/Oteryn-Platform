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
updated_at: 2026-07-20T13:18:00Z
head: 8f14651bcb204f405cf5b8c1b9bde679a148a613
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
  - Exact PHPStan diagnostic identified static Log::shouldHaveReceived() in RequestCorrelationTest as the original static-analysis failure; the test now uses a typed in-memory PSR logger through Log::swap().
  - Exact Pint diagnostic identified one formatting-only difference; the Pint-formatted version was applied.
  - Both temporary diagnostic workflows were removed before merge readiness and do not remain in the final PR diff.
  - CI #721 passed Composer advisory audit, Pint, PHPStan and the full test suite on 8f14651bcb204f405cf5b8c1b9bde679a148a613.
derived:
  - Server-generated correlation is provider-neutral and can be collected by a future centralized sink without trusting client-supplied IDs.
  - Passing repository tests proves the application-side correlation/log shape but does not prove a deployed centralized log, metrics or alerting service.
unknown: []
conflicts: []
first_failure:
  marker: CI #711/#712 Run static analysis
  evidence: PHPStan reported undefined static Log::shouldHaveReceived() and chained calls on mixed; a focused diagnostic artifact identified the exact lines before the test was rewritten with a typed in-memory PSR logger.
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
    result: FAIL
    evidence: intentional diagnostic run captured the exact PHPStan failure artifact and was removed after use.
  - command: CI #718
    result: FAIL
    evidence: PHPStan issue was removed, then Pint exposed one formatting-only difference in the typed test logger.
  - command: temporary Phase 7 Correlation Format Debug workflow run 1
    result: PASS
    evidence: Pint produced the exact formatted test artifact; that output was applied and the workflow was removed.
  - command: CI #721 on 8f14651bcb204f405cf5b8c1b9bde679a148a613
    result: PASS
    evidence: Composer audit, formatting, PHPStan and full tests all passed.
  - command: final exact-head CI and Agent Governance after documentation synchronization
    result: NOT_RUN
    evidence: required before squash merge.
blockers:
  - none
next_action: Synchronize Phase 7 project/security/topology documentation, then verify exact-head CI and Agent Governance and squash-merge PR #55 if green.
```

## Notes

No temporary diagnostic workflow remains on the branch. The implementation is provider-neutral and does not claim that a centralized production observability sink is deployed.
