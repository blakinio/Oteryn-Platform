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

- [ ] Forwarded scheme/host/port headers are trusted only when `TRUSTED_PROXIES` explicitly configures the presenting proxy IP or CIDR.
- [ ] HTTPS requests forwarded by a configured proxy generate HTTPS absolute form actions and URLs.
- [ ] Direct/unconfigured clients cannot spoof forwarded scheme or host.
- [ ] `.env.example` documents the deployment boundary.
- [ ] Focused regression tests and required Platform CI pass.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260724-trusted-reverse-proxy-scheme.md
  - bootstrap/app.php
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
updated_at: 2026-07-24T09:34:00+02:00
head: 60b12fb2d1748fb016484eca521a6c61af505d37
branch: fix/OTERYN-20260724-trusted-reverse-proxy-scheme
pr: none
status: implementing
context_routes:
  - auth-identity
  - security
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260724-trusted-reverse-proxy-scheme.md
  - bootstrap/app.php
  - .env.example
  - tests/Feature/Security/TrustedProxySchemeTest.php
proven:
  - Native-auth rehearsal run 30069293159 attempt 4 reached the real OAuth browser flow and failed because the login form action resolved to the internal HTTP listener rather than the external HTTPS origin.
  - The rehearsal Nginx proxy sends X-Forwarded-Proto https.
  - bootstrap/app.php does not currently configure trusted proxies.
  - Laravel 13 provides Middleware::trustProxies with explicit proxy IP/CIDR and forwarded-header selection.
derived:
  - Platform is ignoring the forwarded HTTPS boundary and generating internal HTTP absolute URLs.
unknown:
  - final focused test and CI result
conflicts: []
first_failure:
  marker: forwarded-https-not-trusted
  evidence: oauth-probe-diagnostics.log from artifact 8589703457 shows login POST connection refused after successful HTTPS login-page retrieval
rejected_hypotheses:
  - disable TLS verification: rejected because certificate and hostname validation already pass
  - rewrite form actions only inside the rehearsal probe: rejected because production URL generation must respect the reverse-proxy boundary
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-trusted-reverse-proxy-scheme.md
validation:
  - command: Native Auth Ephemeral Cutover Rehearsal run 30069293159 attempt 4
    result: FAIL
    evidence: first product failure isolated to untrusted forwarded HTTPS metadata
blockers:
  - none
next_action: implement explicit TRUSTED_PROXIES parsing, regression tests and environment documentation.
```
