# OTERYN-20260720-phase7-security-headers-csp

## Goal

Add provider-independent browser security headers and an enforceable Content Security Policy without breaking the current first-party Blade UI or introducing `unsafe-inline`/`unsafe-eval` script policy shortcuts.

## Acceptance criteria

- [ ] Current first-party inline CSS is moved to a static same-origin asset before enforcing `style-src 'self'`.
- [ ] A reusable web middleware adds CSP plus baseline browser security headers to Platform web responses.
- [ ] CSP denies objects/framing, restricts form submissions and scripts/styles to same-origin, and contains no `unsafe-eval` or inline-script allowance.
- [ ] X-Content-Type-Options, Referrer-Policy, Permissions-Policy and legacy frame-denial headers are present.
- [ ] HSTS is not falsely hard-coded before deployed TLS/proxy topology is proven.
- [ ] Feature tests prove headers on public and authentication surfaces and verify the CSP does not permit unsafe inline/eval execution.
- [ ] No production deployment, external repository, secret, payment functionality or provider-specific infrastructure claim is introduced.

## Ownership

```yaml
owned_paths:
  - app/Http/Middleware/SecurityHeaders.php
  - bootstrap/app.php
  - public/css/**
  - resources/views/game/layout.blade.php
  - resources/views/admin/layout.blade.php
  - tests/Feature/Operations/SecurityHeadersTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-security-headers-csp.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-dependency-security-scanning.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
modules:
  - PlatformOperations
  - Web
  - Admin
dependencies:
  - PR #50 / 3973774727c35aea22d0a646f479a0ff079042cc
blockers:
  - none
cross_repository_tasks:
  - none
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T12:08:00Z
head: 3973774727c35aea22d0a646f479a0ff079042cc
branch: task/OTERYN-20260720-phase7-security-headers-csp
pr: none
status: implementing
context_routes:
  - security
  - testing
  - architecture
  - agent-governance
owned_paths:
  - app/Http/Middleware/SecurityHeaders.php
  - bootstrap/app.php
  - public/css/**
  - resources/views/game/layout.blade.php
  - resources/views/admin/layout.blade.php
  - tests/Feature/Operations/SecurityHeadersTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-security-headers-csp.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-dependency-security-scanning.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
proven:
  - Public game/site layout currently contains first-party inline CSS in a style element.
  - No current repository evidence proves a production TLS termination/proxy topology, so HSTS deployment semantics cannot be assumed.
  - Current repository search/inspection found no required inline script execution path for the implemented Blade surfaces.
  - Admin layout is server-rendered and does not require third-party script/style origins.
derived:
  - Moving owned inline CSS to a same-origin static asset allows an enforceable style-src self CSP without unsafe-inline.
  - HSTS should remain an explicit deployment-topology follow-up rather than a universal application header in this slice.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: implementation not yet validated
rejected_hypotheses:
  - Add CSP with style-src unsafe-inline: rejected because the current inline CSS is first-party and can be moved to a static same-origin asset.
  - Add HSTS unconditionally: rejected because actual HTTPS termination and proxy/origin topology remain unproven.
changed_paths:
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-dependency-security-scanning.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-security-headers-csp.md
validation:
  - command: GitHub Actions on draft PR head
    result: NOT_RUN
    evidence: implementation not yet pushed
blockers:
  - none
next_action: Open the draft PR, move public inline CSS to a same-origin asset, add security-header middleware, and cover public/auth responses with regression tests.
```
