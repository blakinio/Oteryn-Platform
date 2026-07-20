# OTERYN-20260720-phase7-request-correlation-logging

## Goal

Add provider-neutral request correlation and structured logging primitives without assuming or claiming a deployed centralized logging/metrics provider.

## Acceptance criteria

- [ ] Every Laravel-handled request receives a server-generated request identifier that does not trust a browser-supplied correlation value.
- [ ] Normal responses expose the generated identifier through `X-Request-ID`.
- [ ] Completed requests emit one structured-context application log event with request ID, HTTP method, route name, status and bounded duration, without request body/query/credentials.
- [ ] A selectable JSON-to-stderr logging channel exists for container/platform log collection without making it the universal local default.
- [ ] Tests prove request IDs are UUIDs, attacker-supplied IDs are not reflected, IDs differ between requests, and logging context excludes sensitive request data.
- [ ] No external observability provider, production log sink, secret, deployment action, external repository or payment functionality is introduced.

## Ownership

```yaml
owned_paths:
  - .github/workflows/phase7-correlation-debug.yml
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
  - PHPStan failure on the current request-correlation implementation requires exact diagnostic evidence
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T12:52:00Z
head: 1dd975a5d2ac092c77893debbc7f82c113fb628c
branch: task/OTERYN-20260720-phase7-request-correlation-logging
pr: 55
status: debugging
context_routes:
  - security
  - testing
  - architecture
  - agent-governance
owned_paths:
  - .github/workflows/phase7-correlation-debug.yml
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
  - Normal responses receive X-Request-ID and request completion logging is intentionally bounded to request_id, method, route, status and duration_ms.
  - config/logging.php now exposes an optional stderr_json Monolog channel while preserving the existing default logging choice.
  - Agent Governance #631 and #632 passed on implementation/debug heads.
  - CI #711 and #712 both passed Composer advisory audit and Pint but failed at PHPStan before tests.
  - Narrowing the Mockery log-spy callback from typed string/array to mixed/runtime checks did not resolve the PHPStan failure, so further guessing is rejected.
derived:
  - Exact static-analysis output is required before the next implementation change.
  - A temporary narrow diagnostic workflow is justified and must be removed before merge.
unknown:
  - exact PHPStan error text and affected line
conflicts: []
first_failure:
  marker: CI #711/#712 Run static analysis
  evidence: Composer audit and Pint passed; PHPStan failed; full CI logs are truncated before the diagnostic lines in the connector output
rejected_hypotheses:
  - Trust inbound X-Request-ID as authoritative correlation: rejected because it is untrusted browser input and can enable spoofed log correlation.
  - Hard-code a specific observability vendor: rejected because actual production logging/metrics topology remains UNKNOWN.
  - Log full URLs, query strings or request bodies: rejected because they can contain credentials, tokens or unnecessary personal data.
  - The only PHPStan issue was the typed Mockery callback: rejected because CI #712 still failed PHPStan after runtime narrowing.
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
    evidence: PHPStan only; Composer advisory audit and Pint passed; tests were skipped after static-analysis failure.
  - command: temporary focused PHPStan diagnostic workflow
    result: NOT_RUN
    evidence: workflow will capture exact composer analyse output as an artifact and be removed before merge.
blockers:
  - exact PHPStan diagnostic pending
next_action: Run the temporary focused static-analysis diagnostic workflow, read the artifact, fix the exact reported issue, then remove the workflow and rerun full CI.
```

## Notes

The temporary diagnostic workflow is task-owned only for root-cause isolation and must not remain in the merged PR.
