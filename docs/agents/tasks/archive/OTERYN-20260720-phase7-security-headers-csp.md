# OTERYN-20260720-phase7-security-headers-csp

## Goal

Add provider-independent browser security headers and an enforceable Content Security Policy without breaking the current first-party Blade UI or introducing unsafe inline/eval script policy shortcuts.

## Acceptance criteria

- [x] First-party inline CSS moved to a same-origin static asset.
- [x] Global web middleware adds CSP and baseline browser security headers.
- [x] CSP denies objects/framing and contains no unsafe-inline or unsafe-eval allowance.
- [x] HSTS remains deferred until actual TLS/proxy topology is proven.
- [x] Public/auth regression tests cover the header boundary.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T12:35:00Z
head: eb358a245f35fda1865f13e329c07ef0f4850d2f
branch: task/OTERYN-20260720-phase7-security-headers-csp
pr: 54
status: completed
proven:
  - Exact-head CI #704 and Agent Governance #624 passed on 3d72c43bb82228b488bb3e90d5dcfb721ad98a08.
  - PR #54 was squash-merged to main as eb358a245f35fda1865f13e329c07ef0f4850d2f.
  - CSP/security headers and same-origin CSS asset are merged on main.
blockers:
  - HSTS remains dependent on actual deployed TLS/proxy/hostname evidence.
next_action: Continue Phase 7 with provider-neutral request correlation and structured logging primitives.
```
