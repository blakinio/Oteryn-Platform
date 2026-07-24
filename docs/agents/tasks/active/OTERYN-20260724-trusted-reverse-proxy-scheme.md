---
task_id: OTERYN-20260724-trusted-reverse-proxy-scheme
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
search_first:
  - open pull requests and active tasks touching bootstrap/app.php or reverse-proxy handling
  - existing trusted-proxy configuration and tests
optional_reads:
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
---

# OTERYN-20260724-trusted-reverse-proxy-scheme

## Goal

Make Oteryn Platform generate externally correct HTTPS URLs when deployed behind an explicitly configured TLS-terminating reverse proxy, without trusting forwarded headers by default.

## Acceptance criteria

- [x] Forwarded scheme/host/port headers are trusted only when `TRUSTED_PROXIES` explicitly configures the presenting proxy IP or CIDR.
- [x] HTTPS requests forwarded by a configured proxy generate HTTPS absolute form actions and URLs in implementation.
- [x] Direct/unconfigured clients cannot spoof forwarded scheme or host in regression coverage.
- [x] `.env.example` documents the deployment boundary.
- [ ] Focused regression tests and required Platform CI pass.
- [ ] Exact product-fix SHA passes the native-auth ephemeral cutover rehearsal.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260724-trusted-reverse-proxy-scheme.md
  - bootstrap/app.php
  - .env.example
  - tests/Feature/Security/TrustedProxySchemeTest.php
  - .github/workflows/trusted-proxy-static-analysis-diagnostic.yml
modules:
  - Laravel reverse-proxy request boundary
  - native OAuth browser flow URL generation
dependencies:
  - OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal
blockers:
  - none
cross_repository_tasks:
  - CAN-20260724-game-session-cache-headers
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-24T09:44:00+02:00
head: 1d99a7bcd4424b7e45dde5f8bc7977125b55bed4
branch: fix/OTERYN-20260724-trusted-reverse-proxy-scheme
pr: 131
status: validating
context_routes:
  - auth-identity
  - security
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260724-trusted-reverse-proxy-scheme.md
  - bootstrap/app.php
  - .env.example
  - tests/Feature/Security/TrustedProxySchemeTest.php
  - .github/workflows/trusted-proxy-static-analysis-diagnostic.yml
proven:
  - Native-auth rehearsal run 30069293159 attempt 4 reached the real OAuth browser flow and failed because the login form action resolved to the internal HTTP listener rather than the external HTTPS origin.
  - The rehearsal Nginx proxy sends X-Forwarded-Proto https.
  - Prior bootstrap/app.php did not configure trusted proxies.
  - Laravel 13 provides Middleware::trustProxies with explicit proxy IP/CIDR and forwarded-header selection.
  - PR 131 implements explicit comma-separated proxy IP/CIDR trust, rejects wildcard trust, documents TRUSTED_PROXIES and includes trusted/untrusted login-form regressions.
derived:
  - Platform ignored the forwarded HTTPS boundary and generated internal HTTP absolute URLs.
unknown:
  - exact PHPStan diagnostic text from CI run 30076173754
  - final focused test and CI result
conflicts: []
first_failure:
  marker: phpstan-before-tests
  evidence: CI run 30076173754 passed Composer validation/audit and Pint, then failed static analysis before PHPUnit
rejected_hypotheses:
  - disable TLS verification: rejected because certificate and hostname validation already pass
  - rewrite form actions only inside the rehearsal probe: rejected because production URL generation must respect the reverse-proxy boundary
changed_paths:
  - .env.example
  - bootstrap/app.php
  - docs/agents/tasks/active/OTERYN-20260724-trusted-reverse-proxy-scheme.md
  - tests/Feature/Security/TrustedProxySchemeTest.php
validation:
  - command: Native Auth Ephemeral Cutover Rehearsal run 30069293159 attempt 4
    result: FAIL
    evidence: first product failure isolated to untrusted forwarded HTTPS metadata
  - command: CI run 30076173754 on 1d99a7bcd4424b7e45dde5f8bc7977125b55bed4
    result: FAIL
    evidence: Composer validation/audit and Pint passed; PHPStan failed; PHPUnit was skipped
blockers:
  - none
next_action: retain the exact PHPStan output as an artifact, fix the reported type issue, and rerun required CI.
```
