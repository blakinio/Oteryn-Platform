# OTERYN-20260720-phase7-production-config-guardrails

## Goal

Add provider-independent, fail-closed production configuration guardrails that can be run before deployment without assuming a hosting provider or exposing secrets.

## Acceptance criteria

- [x] A reusable verifier reports unsafe production configuration without echoing secret values.
- [x] The verifier requires production environment mode, debug disabled, a configured application encryption key, HTTPS non-loopback APP_URL, Secure and HttpOnly session cookies, and a real delivery-capable mail transport/from address for implemented password-recovery flows.
- [x] Provider-specific choices such as database engine, cache/session backend, queue backend, logging sink and Cloudflare policy are not incorrectly hard-coded as universal requirements.
- [x] An Artisan command exits non-zero on violations and zero only when all invariant checks pass.
- [x] Focused tests prove each unsafe invariant is rejected and a compliant configuration passes.
- [x] No secret, credential, production endpoint, deployment action, external repository or payment functionality is introduced.

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-20T11:35:00Z
head: 0f876d4f2209399a85cafcff1623d8e6c810b914
branch: task/OTERYN-20260720-phase7-production-config-guardrails
pr: 49
status: completed
proven:
  - ProductionConfigurationVerifier and production:verify-configuration are merged on main.
  - Final exact-head CI #681 and Agent Governance #602 passed on d7039ae16e660b1f4b976d1f81d5e5908b495022.
  - PR #49 was squash-merged to main as 0f876d4f2209399a85cafcff1623d8e6c810b914.
  - The verifier checks only provider-independent production configuration invariants and does not print secret values.
blockers:
  - none
next_action: Continue Phase 7 with repository-owned dependency/security scanning while deployment-specific topology remains external-evidence blocked.
```
