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
- [x] Exact product-fix SHA passes the native-auth ephemeral cutover rehearsal.

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
updated_at: 2026-07-24T17:42:00+02:00
head: 5c05fcb86b8f2b7d4ebf059cef84a6708f8d162a
branch: fix/OTERYN-20260724-oauth-token-cache-headers
pr: 133
status: ready
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
  - PR 133 parses explicit comma-separated proxy IP/CIDR values, rejects wildcard trust, replaces the default middleware with TrustConfiguredProxies, documents TRUSTED_PROXIES and includes trusted/untrusted login-form regressions.
  - Standard CI run 30077363907 passed Composer validation, dependency audit, Pint, PHPStan and the complete PHPUnit suite on the trusted-proxy implementation.
  - Focused diagnostic run 30077363304 passed dependency installation, PHPStan and both trusted-proxy regressions.
  - Production-like rehearsal run 30095854266 passed OAuth Authorization Code plus PKCE through the configured HTTPS reverse-proxy boundary using the exact combined Platform implementation.
  - Retained rehearsal artifact 8597730728 has digest sha256:e7e908e9129658654054a96adf641757edc2c904fc2b01a5b9fc97e393d18009 and classification PRODUCTION_LIKE_PROVEN.
derived:
  - Platform now honors explicitly trusted forwarded HTTPS metadata while direct or untrusted clients remain unable to spoof the external origin.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: Focused product validation and the full production-like HTTPS rehearsal passed on the combined implementation.
rejected_hypotheses:
  - disable TLS verification: rejected because certificate and hostname validation pass.
  - rewrite form actions only inside the rehearsal probe: rejected because production URL generation must respect the reverse-proxy boundary.
  - access config directly in withMiddleware: rejected by package-discovery failure and Laravel framework bootstrap ordering.
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
    evidence: First product failure isolated to untrusted forwarded HTTPS metadata.
  - command: CI run 30077363907
    result: PASS
    evidence: Composer validation/audit, Pint, PHPStan and complete PHPUnit suite passed.
  - command: focused diagnostic run 30077363304
    result: PASS
    evidence: Composer install, PHPStan and TrustedProxySchemeTest passed.
  - command: Native Auth Ephemeral Cutover Rehearsal run 30095854266
    result: PASS
    evidence: The exact combined Platform implementation completed the HTTPS OAuth flow behind the explicitly trusted proxy.
blockers: []
next_action: Inspect checks on this checkpoint commit, mark PR 133 ready, and squash-merge when all required checks pass.
```
