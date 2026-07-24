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
  - app/Http/Middleware/TrustConfiguredProxies.php
  - bootstrap/app.php
  - config/http.php
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
updated_at: 2026-07-24T09:55:00+02:00
head: 20d4b268fa7cbeaee25a2db38fb199e7e76e8507
branch: fix/OTERYN-20260724-trusted-reverse-proxy-scheme
pr: 131
status: validating
context_routes:
  - auth-identity
  - security
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260724-trusted-reverse-proxy-scheme.md
  - app/Http/Middleware/TrustConfiguredProxies.php
  - bootstrap/app.php
  - config/http.php
  - .env.example
  - tests/Feature/Security/TrustedProxySchemeTest.php
  - .github/workflows/trusted-proxy-static-analysis-diagnostic.yml
proven:
  - Native-auth rehearsal run 30069293159 attempt 4 reached the real OAuth browser flow and failed because the login form action resolved to the internal HTTP listener rather than the external HTTPS origin.
  - The rehearsal Nginx proxy sends X-Forwarded-Proto https.
  - Prior bootstrap/app.php did not configure trusted proxies.
  - Laravel 13 default middleware includes Illuminate Http TrustProxies and Middleware::replace can replace it without evaluating application config during bootstrap.
  - PR 131 parses explicit comma-separated proxy IP/CIDR values in config/http.php, rejects wildcard trust, replaces the default middleware with TrustConfiguredProxies, documents TRUSTED_PROXIES and includes trusted/untrusted login-form regressions.
derived:
  - Platform ignored the forwarded HTTPS boundary and generated internal HTTP absolute URLs.
unknown:
  - final focused test and CI result
conflicts: []
first_failure:
  marker: config-helper-before-binding
  evidence: diagnostic artifact 8590168963 showed package discovery failed because config() was called inside withMiddleware before the config binding existed
rejected_hypotheses:
  - disable TLS verification: rejected because certificate and hostname validation already pass
  - rewrite form actions only inside the rehearsal probe: rejected because production URL generation must respect the reverse-proxy boundary
  - access config directly in withMiddleware: rejected by package-discovery failure and Laravel framework bootstrap ordering
changed_paths:
  - .env.example
  - .github/workflows/trusted-proxy-static-analysis-diagnostic.yml
  - app/Http/Middleware/TrustConfiguredProxies.php
  - bootstrap/app.php
  - config/http.php
  - docs/agents/tasks/active/OTERYN-20260724-trusted-reverse-proxy-scheme.md
  - tests/Feature/Security/TrustedProxySchemeTest.php
validation:
  - command: Native Auth Ephemeral Cutover Rehearsal run 30069293159 attempt 4
    result: FAIL
    evidence: first product failure isolated to untrusted forwarded HTTPS metadata
  - command: CI run 30076173754 on 1d99a7bcd4424b7e45dde5f8bc7977125b55bed4
    result: FAIL
    evidence: Composer validation/audit and Pint passed; PHPStan reported env outside config and an untyped TestResponse
  - command: diagnostic run 30076782399 artifact 8590168963
    result: FAIL
    evidence: package discovery proved config() is unavailable during the withMiddleware bootstrap callback
blockers:
  - none
next_action: run standard CI against the custom request-time TrustConfiguredProxies implementation and fix the first concrete failure.
```
