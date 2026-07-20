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
  - none for provider-neutral application primitives
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T12:38:00Z
head: eb358a245f35fda1865f13e329c07ef0f4850d2f
branch: task/OTERYN-20260720-phase7-request-correlation-logging
pr: none
status: implementing
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
  - config/logging.php currently provides single-file and stderr channels but no dedicated JSON stderr channel.
  - Actual centralized production logging, metrics and alerting sinks remain UNKNOWN from repository evidence.
  - Security architecture forbids credentials, tokens and unnecessary personal data in logs.
derived:
  - Server-generated request IDs avoid trusting/spoofing browser-supplied correlation identifiers.
  - Logging route name, method, status and duration is sufficient for a first provider-neutral request-completion event without storing request bodies, query strings or credentials.
  - A JSON stderr channel is deployable across many hosting/container platforms while remaining optional until actual topology is proven.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: implementation not yet validated
rejected_hypotheses:
  - Trust inbound X-Request-ID as authoritative correlation: rejected because it is untrusted browser input and can enable spoofed log correlation.
  - Hard-code a specific observability vendor: rejected because actual production logging/metrics topology remains UNKNOWN.
  - Log full URLs, query strings or request bodies: rejected because they can contain credentials, tokens or unnecessary personal data.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-security-headers-csp.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-request-correlation-logging.md
validation:
  - command: GitHub Actions on draft PR head
    result: NOT_RUN
    evidence: implementation not yet pushed
blockers:
  - none
next_action: Open the draft PR and implement server-generated request correlation, bounded completion logging and an optional JSON stderr channel.
```
