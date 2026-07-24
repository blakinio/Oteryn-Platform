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
- [x] HTTPS requests forwarded by a configured proxy generate HTTPS absolute form actions and URLs.
- [x] Direct/unconfigured clients cannot spoof forwarded scheme or host.
- [x] `.env.example` documents the deployment boundary.
- [x] Focused regression tests and required Platform CI pass.
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
updated_at: 2026-07-24T10:05:00+02:00
head: 86ce75943ef54234caed2eafa9599a2ea0fb6a27
branch: fix/OTERYN-20260724-trusted-reverse-proxy-scheme
pr: 131
status: review
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
proven:
  - Native-auth rehearsal run 30069293159 attempt 4 reached the real OAuth browser flow and failed because the login form action resolved to the internal HTTP listener rather than the external HTTPS origin.
  - The rehearsal Nginx proxy sends X-Forwarded-Proto https.
  - Prior bootstrap/app.php did not configure trusted proxies.
  - Laravel 13 default middleware includes Illuminate Http TrustProxies and Middleware::replace can replace it without evaluating application config during bootstrap.
  - PR 131 parses explicit comma-separated proxy IP/CIDR values in config/http.php, rejects wildcard trust, replaces the default middleware with TrustConfiguredProxies, documents TRUSTED_PROXIES and includes trusted/untrusted login-form regressions.
  - Standard CI run 30077363907 passed Composer validation, dependency audit, Pint, PHPStan and the complete PHPUnit suite on head 908260b46ba9c63c9aa5d2b86e496f9c16470d19.
  - Focused diagnostic run 30077363304 passed dependency installation, PHPStan and both trusted-proxy regressions.
derived:
  - Platform ignored the forwarded HTTPS boundary and generated internal HTTP absolute URLs.
unknown:
  - production-like rehearsal result on the exact final product-fix SHA
conflicts: []
first_failure:
  marker: none-in-product-ci
  evidence: all required Platform CI stages passed after moving proxy resolution to request-time middleware
rejected_hypotheses:
  - disable TLS verification: rejected because certificate and hostname validation already pass
  - rewrite form actions only inside the rehearsal probe: rejected because production URL generation must respect the reverse-proxy boundary
  - access config directly in withMiddleware: rejected by package-discovery failure and Laravel framework bootstrap ordering
changed_paths:
  - .env.example
  - app/Http/Middleware/TrustConfiguredProxies.php
  - bootstrap/app.php
  - config/http.php
  - docs/agents/tasks/active/OTERYN-20260724-trusted-reverse-proxy-scheme.md
  - tests/Feature/Security/TrustedProxySchemeTest.php
validation:
  - command: Native Auth Ephemeral Cutover Rehearsal run 30069293159 attempt 4
    result: FAIL
    evidence: first product failure isolated to untrusted forwarded HTTPS metadata
  - command: CI run 30077363907 on 908260b46ba9c63c9aa5d2b86e496f9c16470d19
    result: PASS
    evidence: Composer validation/audit, Pint, PHPStan and complete PHPUnit suite passed
  - command: focused diagnostic run 30077363304
    result: PASS
    evidence: Composer install, PHPStan and TrustedProxySchemeTest passed
blockers:
  - none
next_action: verify the final head without the temporary diagnostic workflow, then pin it into the native-auth rehearsal with TRUSTED_PROXIES=10.201.3.0/24.
```
