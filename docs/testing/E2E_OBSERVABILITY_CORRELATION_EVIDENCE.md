# E2E Observability Correlation Evidence

## Purpose

This record captures the bounded P1 production-like observability-correlation assertion added through PR #102.

The goal is narrower than general production observability readiness: prove that one concrete application-generated response `X-Request-ID` is the same identifier recorded by the corresponding structured `http.request.completed` JSON log event in the controlled Phase 7 runtime.

## Risk closed

Before this slice, Phase 7 independently proved that:

- an HTTP response contained `X-Request-ID`;
- the runtime emitted at least one `http.request.completed` structured log event.

Those checks did not prove that the response header and log event referred to the same request.

The new assertion closes that gap by correlating one non-sensitive public `GET /` request end to end.

## Validation sequence

The existing Phase 7 running-HTTP validation now:

1. starts the exact-SHA Laravel production-like runtime with `LOG_CHANNEL=stderr_json`;
2. sends a public `GET /` request and captures response headers/body;
3. extracts the server-generated `X-Request-ID` without writing the identifier to durable evidence;
4. validates the identifier is a UUID;
5. parses the mixed runtime log line by line as JSON where possible;
6. finds `http.request.completed` events whose `context.request_id` exactly matches the captured response identifier;
7. requires exactly one matching completion event;
8. requires the correlated event method to be `GET` and status to be `200`;
9. fails closed if the identifier is missing/malformed, correlation is absent, multiple matching completion events exist, or method/status disagree.

The helper intentionally ignores non-JSON server-startup lines and never dumps the runtime log on correlation failure.

## Exact-SHA evidence

Implementation head:

`b41099313e562df2ed4192af192f7a3caa3b32fa`

Required workflow results on that head:

- `Phase 7 Production-Like Validation` run `29845924558` — PASS;
- `CI` run `29845924513` — PASS;
- `Agent Governance` run `29845924702` — PASS;
- `Platform DB Outage Validation` run `29845924425` — PASS.

The Phase 7 run passed the existing `Validate running health, headers, cookies, correlation and error behavior` step with the new exact response-to-log correlation assertion enabled, then passed the remaining backup/restore and existing-data upgrade/rollback/redeploy steps.

Artifact:

- `phase7-production-like-evidence-29845924558`;
- artifact id `8501348409`;
- digest `sha256:bd3cff51bb61e361996afbe795b07396a02fd749c53d7407d052eb8235aa3e30`.

The durable Phase 7 evidence now includes:

`"request_id_log_correlation": "PASS"`

The request identifier itself is not stored in this evidence record.

## Classification

The correlated request/log chain is `STAGING_PROVEN` for the controlled production-like runtime.

It does not prove:

- production log collection/shipping;
- production retention or access control;
- production metrics or alert routing;
- production on-call behavior;
- correlation across an external reverse proxy, CDN or WAF;
- production trace propagation across future distributed services.

Those facts remain production/environment-specific evidence under issue #91 where applicable.
