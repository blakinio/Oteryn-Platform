# Phase 7 Production-Like Validation Evidence

## Purpose

This document records non-secret evidence produced by the controlled Phase 7 production-like validation workflow.

It does **not** treat CI/staging as final production.

Evidence classifications used here:

- `STAGING_PROVEN` — directly demonstrated by a successful controlled production-like workflow run on an exact commit SHA;
- `PRODUCTION_PROVEN` — directly demonstrated in the final production environment;
- `UNKNOWN` — not yet proven for the stated environment.

A successful staging result may prove that a procedure, guardrail or least-privilege design works under controlled production-like conditions. It does not prove final production DNS, TLS, firewall, network isolation, provider configuration, deployed credentials, backup schedule, monitoring sink or exact deployed production SHA.

## Validation workflow

Workflow:

`Phase 7 Production-Like Validation`

Repository path:

`.github/workflows/phase7-production-like-validation.yml`

The workflow records the exact validation SHA and previous known-good SHA in its run output and step summary.

Current durable evidence state before the first successful exact-head run of PR #63:

| Boundary | Classification | Evidence |
|---|---|---|
| Exact staging validation SHA | `UNKNOWN` | Awaiting successful PR #63 workflow run on final head. |
| Clean deployment and migrations | `UNKNOWN` | Workflow implemented; successful exact-head run required. |
| Rollback to previous known-good SHA | `UNKNOWN` | Workflow implemented; successful exact-head run required. |
| Interrupted release isolation | `UNKNOWN` | Workflow implemented; successful exact-head run required. |
| Redeploy current SHA | `UNKNOWN` | Workflow implemented; successful exact-head run required. |
| Production configuration guardrails | `UNKNOWN` | Workflow implemented; successful exact-head run required. |
| Generic Canary read-only effective grants | `UNKNOWN` | Workflow provisions a real MariaDB principal and runs the application verifier; successful exact-head run required. |
| Provisioning effective grants | `UNKNOWN` | Workflow provisions a real MariaDB column-level principal and runs the application verifier; successful exact-head run required. |
| Character-create effective grants | `UNKNOWN` | Workflow provisions a real MariaDB column-level principal and runs the application verifier; successful exact-head run required. |
| Excess DB privilege fail-closed | `UNKNOWN` | Workflow intentionally introduces and removes excessive privilege; successful exact-head run required. |
| Insufficient DB privilege fail-closed | `UNKNOWN` | Workflow intentionally removes and restores required privilege; successful exact-head run required. |
| Generic Canary write denial | `UNKNOWN` | Workflow attempts an unauthorized update with the read-only principal; successful exact-head run required. |
| Runtime Redis ACL/key/command boundary | `UNKNOWN` | Workflow provisions an ACL user limited to runtime read commands and key pattern; successful exact-head run required. |
| Runtime Redis missing/malformed/unavailable behavior | `UNKNOWN` | Workflow exercises real Redis plus unavailable-endpoint failure; successful exact-head run required. |
| SMTP delivery-capable staging path | `UNKNOWN` | Workflow delivers through a real SMTP test service, not Laravel `array`/`log`; successful exact-head run required. |
| Mail-unavailable failure behavior | `UNKNOWN` | Workflow exercises an unavailable SMTP endpoint; successful exact-head run required. |
| Critical implemented regression flows | `UNKNOWN` | Full repository test suite runs on the exact validation SHA; successful exact-head run required. |
| Running health endpoint | `UNKNOWN` | Workflow boots the release and probes `/health`; successful exact-head run required. |
| CSP/browser security headers | `UNKNOWN` | Workflow inspects live HTTP response headers; successful exact-head run required. |
| Secure/HttpOnly session cookie attributes | `UNKNOWN` | Workflow inspects the live login response; successful exact-head run required. |
| Request correlation and structured request completion logging | `UNKNOWN` | Workflow checks live `X-Request-ID` and JSON request-completion output; successful exact-head run required. |
| Representative sensitive-error/log secret leakage check | `UNKNOWN` | Workflow checks representative error/log output for the ephemeral app key; successful exact-head run required. |
| Platform DB backup/restore procedure | `UNKNOWN` | Workflow performs a real MariaDB dump and clean restore; successful exact-head run required. |
| Restore integrity | `UNKNOWN` | Workflow compares table/migration counts and a SHA-tagged restore probe; successful exact-head run required. |
| Measured staging restore time | `UNKNOWN` | Recorded only after successful exact-head workflow run. |
| Restored-environment smoke | `UNKNOWN` | Workflow runs migration-status/configuration checks against the restored database; successful exact-head run required. |

## What the controlled environment proves when successful

A successful exact-head run may promote the applicable rows above to `STAGING_PROVEN`.

The controlled environment uses ephemeral, non-production services and credentials. It is intended to prove application behavior and operational procedures without exposing production secrets or requiring final production access.

The deployment/rollback portion validates a clean release directory, migration execution, atomic release-pointer switching, rollback to the PR base SHA, isolation of an incomplete release and redeployment of the validation SHA. This is evidence for the controlled release model only; it is not evidence of the final provider's deployment mechanism.

The backup/restore portion measures restore time for the controlled MariaDB dataset. The result is a staging recovery measurement only and must not be declared as production RTO or RPO.

The SMTP portion uses a real SMTP protocol path to a safe test SMTP service. It proves that the application is not relying on Laravel's `array` or `log` transport for the staging validation. It does not prove the final production mail provider, sender-domain authentication, bounce handling or delivery monitoring.

## Final production verification pass

The following items remain `UNKNOWN` until directly proven in the final production environment. This is the minimal production-only pass after staging-verifiable work is closed:

- [ ] `PRODUCTION_PROVEN` — exact deployed Oteryn Platform SHA and relevant Canary/login-server versions.
- [ ] `PRODUCTION_PROVEN` — production DNS/proxy/Cloudflare/WAF/Access state and actual TLS termination/certificate behavior.
- [ ] `PRODUCTION_PROVEN` — direct-origin exposure decision and effective ingress firewall/reverse-proxy restrictions.
- [ ] `PRODUCTION_PROVEN` — production Platform DB endpoint/topology/network isolation and effective credential ownership/rotation.
- [ ] `PRODUCTION_PROVEN` — production generic Canary DB effective grants using `canary:verify-db-privileges`.
- [ ] `PRODUCTION_PROVEN` — production provisioning DB effective grants using `canary:verify-provisioning-db-privileges`, if provisioning is enabled.
- [ ] `PRODUCTION_PROVEN` — production character-create DB effective grants using `canary:verify-character-create-db-privileges`, if character creation is enabled.
- [ ] `PRODUCTION_PROVEN` — production runtime Redis endpoint, network/TLS state and effective ACL for the dedicated read principal.
- [ ] `PRODUCTION_PROVEN` — production session/cache/queue choices and worker supervision where applicable.
- [ ] `PRODUCTION_PROVEN` — production mail provider, sender-domain readiness and delivery/bounce monitoring.
- [ ] `PRODUCTION_PROVEN` — production structured logging/metrics/alerting sink, retention/access policy and request-ID preservation.
- [ ] `PRODUCTION_PROVEN` — actual production deployment/migration/rollback mechanism and authorized rollback operator path.
- [ ] `PRODUCTION_PROVEN` — production backup schedule, retention/access policy and dated production restore test; staging restore time is not production RTO/RPO.
- [ ] `PRODUCTION_PROVEN` — final production health/readiness and critical smoke/E2E checks against the exact deployed SHA.
- [ ] Resolve the separately authorized authoritative game-login bridge if Platform-originated game login is part of launch scope.

Phase 7 must remain IN PROGRESS until the applicable final production-only items are directly proven or an eligible risk is explicitly accepted by the owner under repository policy.
