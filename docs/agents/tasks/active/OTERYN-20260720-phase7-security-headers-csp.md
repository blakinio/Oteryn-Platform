# OTERYN-20260720-phase7-security-headers-csp

## Goal

Add provider-independent browser security headers and an enforceable Content Security Policy without breaking the current first-party Blade UI or introducing `unsafe-inline`/`unsafe-eval` script policy shortcuts.

## Acceptance criteria

- [x] Current first-party inline CSS is moved to a static same-origin asset before enforcing `style-src 'self'`.
- [x] A reusable web middleware adds CSP plus baseline browser security headers to Platform web responses.
- [x] CSP denies objects/framing, restricts form submissions and scripts/styles to same-origin, and contains no `unsafe-eval` or inline-script allowance.
- [x] X-Content-Type-Options, Referrer-Policy, Permissions-Policy and legacy frame-denial headers are present.
- [x] HSTS is not falsely hard-coded before deployed TLS/proxy topology is proven.
- [x] Feature tests prove headers on public and authentication surfaces and verify the CSP does not permit unsafe inline/eval execution.
- [x] No production deployment, external repository, secret, payment functionality or provider-specific infrastructure claim is introduced.

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
updated_at: 2026-07-20T12:25:00Z
head: bc862811a0b712f809134a2457947f413bfef1ed
branch: task/OTERYN-20260720-phase7-security-headers-csp
pr: 54
status: validating
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
  - PR #50 merged as 3973774727c35aea22d0a646f479a0ff079042cc after exact-head CI #689 and Agent Governance #610.
  - Public first-party inline CSS was moved to public/css/app.css and public/admin layouts load the same-origin stylesheet.
  - SecurityHeaders middleware is applied to the Laravel web middleware group.
  - CSP uses same-origin default/script/style/connect/font boundaries, self/data images, form-action self, base-uri none, frame-ancestors none and object-src none.
  - CSP contains neither unsafe-inline nor unsafe-eval allowances.
  - Browser responses include nosniff, DENY frame protection, strict-origin-when-cross-origin referrer policy and restrictive camera/geolocation/microphone/payment/USB permissions policy.
  - HSTS remains absent by design until actual TLS termination/proxy/hostname topology is proven.
  - Feature tests cover public and authentication response headers plus the externalized stylesheet/no-inline-style public layout.
  - CI #701 and Agent Governance #621 passed on implementation/documentation head 614a8b8b961bc5b309e15af0d30d6de8ad22f143.
derived:
  - The current first-party Blade surfaces can enforce same-origin script/style CSP without requiring unsafe inline/eval execution.
  - HSTS remains an external deployment-topology validation item rather than an application invariant at this stage.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: implementation passed Composer advisory audit, formatting, PHPStan and full tests on the validated head
rejected_hypotheses:
  - Add CSP with style-src unsafe-inline: rejected because owned inline CSS was moved to a static same-origin asset.
  - Add HSTS unconditionally: rejected because actual HTTPS termination and proxy/origin topology remain unproven.
changed_paths:
  - app/Http/Middleware/SecurityHeaders.php
  - bootstrap/app.php
  - public/css/app.css
  - resources/views/game/layout.blade.php
  - resources/views/admin/layout.blade.php
  - tests/Feature/Operations/SecurityHeadersTest.php
  - docs/agents/ACTIVE_WORK.md
  - docs/agents/PROJECT_STATE.md
  - docs/agents/tasks/active/OTERYN-20260720-phase7-security-headers-csp.md
  - docs/agents/tasks/archive/OTERYN-20260720-phase7-dependency-security-scanning.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
validation:
  - command: CI #701 on 614a8b8b961bc5b309e15af0d30d6de8ad22f143
    result: PASS
    evidence: Composer audit, Pint, PHPStan and full tests passed.
  - command: Agent Governance #621 on 614a8b8b961bc5b309e15af0d30d6de8ad22f143
    result: PASS
    evidence: checkpoint validation passed.
  - command: final exact-head CI and Agent Governance after state/checkpoint synchronization
    result: NOT_RUN
    evidence: required before squash merge.
blockers:
  - none
next_action: Verify required checks on the final synchronized head and squash-merge PR #54 if the merge gate remains satisfied.
```
