# OTERYN-20260720-phase7-request-correlation-logging

## Goal

Add provider-neutral request correlation and structured logging primitives without assuming or claiming a deployed centralized logging/metrics provider.

## Acceptance criteria

- [x] Every Laravel-handled request receives a server-generated request identifier that does not trust a browser-supplied correlation value.
- [x] Normal responses expose the generated identifier through `X-Request-ID`.
- [x] Completed requests emit one bounded request-completion log event without request body/query/credentials.
- [x] A selectable JSON-to-stderr logging channel exists without changing the universal default.
- [x] Focused tests cover UUID generation, inbound-ID non-trust, uniqueness, safe context and health correlation.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T13:35:00Z
head: b6650966fe877a0e7872f29606b32b6394dde99f
branch: task/OTERYN-20260720-phase7-request-correlation-logging
pr: 55
status: completed
proven:
  - Request correlation and bounded request-completion logging are merged on main.
  - Optional JSON-to-stderr logging is available without claiming a deployed centralized sink.
  - Temporary diagnostic workflows were removed before merge.
  - Final exact-head CI #727 and Agent Governance #647 passed on 222674a1ccc06ebe72ec5e8fc594df1c868b1b07.
  - PR #55 was squash-merged to main as b6650966fe877a0e7872f29606b32b6394dde99f.
derived:
  - Application-side correlation is ready to bind to a future real production log/metrics sink once deployment evidence exists.
unknown:
  - deployed centralized logging, metrics and alerting sink
conflicts: []
first_failure:
  marker: CI #711/#712 static analysis
  evidence: exact diagnostic identified dynamic facade-spy typing; a typed in-memory PSR logger fixed the test, followed by one Pint-only formatting correction.
rejected_hypotheses:
  - Trust inbound X-Request-ID as authoritative correlation: rejected because it is untrusted client input.
changed_paths:
  - app/Http/Middleware/RequestCorrelation.php
  - bootstrap/app.php
  - config/logging.php
  - tests/Feature/Operations/RequestCorrelationTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/operations/PRODUCTION_TOPOLOGY_EVIDENCE.md
validation:
  - command: CI #727 and Agent Governance #647
    result: PASS
    evidence: exact-head Composer audit, Pint, PHPStan, full tests and checkpoint validation passed before merge.
blockers:
  - none
next_action: Continue Phase 7 with provider-neutral production readiness and incident/recovery runbooks while environment-specific operational evidence remains external.
```
