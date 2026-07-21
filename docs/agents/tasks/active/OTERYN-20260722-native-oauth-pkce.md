---
task_id: OTERYN-20260722-native-oauth-pkce
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/adr/0009-oteryn-game-authentication-architecture.md
  - docs/architecture/GAME_AUTH_THREAT_MODEL.md
  - docs/architecture/GAME_AUTH_ROLLOUT_PLAN.md
  - docs/contracts/OTCLIENT_GAME_AUTH_CONTRACT.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
search_first:
  - open PRs and active tasks overlapping Passport, OAuth, native-client authorization, PKCE, or game auth
  - current Laravel 13 Passport documentation and Passport 13.x package compatibility
  - existing Identity password/MFA intended-redirect flow
  - current application auth guards, middleware and security headers
optional_reads:
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/TEST_STRATEGY.md
---

# OTERYN-20260722-native-oauth-pkce

## Goal

Implement Phase 2 of ADR 0009: standards-based first-party native-client OAuth Authorization Code + PKCE using Laravel Passport, integrated with the existing Oteryn Identity password/MFA browser flow. Do not expose Game Login Ticket HTTP issuance/redeem, do not implement Game Gateway, and do not modify OTClient or Canary.

## Acceptance criteria

- [ ] Laravel Passport 13.x is locked in Composer and its package migrations are available to the application.
- [ ] `Identity` implements Passport `OAuthenticatable` and `HasApiTokens` without changing the authoritative password/MFA boundary.
- [ ] An `api` auth guard uses Passport with the existing Identity provider.
- [ ] OAuth access-token and refresh-token lifetimes are explicitly short and bounded for the bootstrap phase; no one-year/default long-lived semantics remain.
- [ ] The only initial game bootstrap scope is `game:ticket`.
- [ ] A first-party native public client can be created/ensured without a confidential client secret and with the registered loopback redirect contract.
- [ ] The native redirect contract uses `http://127.0.0.1/callback`; RFC 8252 dynamic loopback ports remain handled by the selected OAuth server implementation.
- [ ] `/oauth/authorize` uses the existing browser Identity session flow; unauthenticated users are redirected through Oteryn login and confirmed MFA continues via `redirect()->intended(...)`.
- [ ] A bounded Oteryn authorization approval view can approve or deny the requested `game:ticket` scope without exposing credentials.
- [ ] PKCE S256 is required for the native public client; missing/invalid verifier fails closed in focused integration coverage.
- [ ] Unregistered/non-loopback/wrong-path redirects fail closed; the registered loopback path with a dynamic port is accepted according to the package contract.
- [ ] OAuth keys are never committed; deployment uses Passport key generation or injected `PASSPORT_PRIVATE_KEY` / `PASSPORT_PUBLIC_KEY` material.
- [ ] No Game Login Ticket public endpoint, private redeem endpoint, Gateway runtime, or Canary adapter is added.
- [ ] Relevant focused tests, governance, CI, DB-outage, production-like and acceptance workflows pass on final head.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-native-oauth-pkce.md
  - docs/agents/tasks/archive/OTERYN-20260722-game-auth-domain-foundation.md
  - docs/agents/tasks/active/OTERYN-20260722-game-auth-domain-foundation.md
  - composer.json
  - composer.lock
  - app/Console/Commands/
  - app/GameAuth/OAuth/
  - app/Identity/Models/Identity.php
  - app/Providers/AppServiceProvider.php
  - config/auth.php
  - config/game-auth.php
  - resources/views/game-auth/
  - tests/Feature/GameAuth/OAuth/
  - tests/Unit/GameAuth/OAuth/
  - .github/workflows/native-oauth-dependency-bootstrap.yml
modules:
  - auth-identity
  - architecture
  - api
  - security
  - testing
dependencies:
  - Phase 1 merged as fc6b70fa11f3bb9958b405fc76d8918c49381668
  - existing Identity password/MFA web flow
  - Laravel Passport 13.x / League OAuth2 server PKCE support
blockers:
  - none
cross_repository_tasks:
  - OTClient PKCE/browser/loopback implementation remains a separate future task
  - Canary remains untouched in Phase 2
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-21T22:50:00Z
head: fc6b70fa11f3bb9958b405fc76d8918c49381668
branch: task/OTERYN-20260722-native-oauth-pkce
pr: none
status: investigating
context_routes:
  - auth-identity
  - architecture
  - api
  - security
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-native-oauth-pkce.md
  - docs/agents/tasks/archive/OTERYN-20260722-game-auth-domain-foundation.md
  - docs/agents/tasks/active/OTERYN-20260722-game-auth-domain-foundation.md
  - composer.json
  - composer.lock
  - app/Console/Commands/
  - app/GameAuth/OAuth/
  - app/Identity/Models/Identity.php
  - app/Providers/AppServiceProvider.php
  - config/auth.php
  - config/game-auth.php
  - resources/views/game-auth/
  - tests/Feature/GameAuth/OAuth/
  - tests/Unit/GameAuth/OAuth/
  - .github/workflows/native-oauth-dependency-bootstrap.yml
proven:
  - Phase 1 PR 118 was squash-merged to main as fc6b70fa11f3bb9958b405fc76d8918c49381668.
  - No open Oteryn Platform PR overlaps Passport/OAuth/PKCE scope at task start.
  - Current Laravel 13 Passport documentation requires HasApiTokens plus OAuthenticatable on the user model and a passport API guard.
  - Passport provides Authorization Code Grant with PKCE for public clients.
  - Passport default access tokens are long-lived unless explicitly configured, so Oteryn must override lifetimes.
  - Existing Identity login and MFA challenge success both use redirect()->intended(...), preserving an interrupted OAuth authorization request after browser authentication.
  - Existing middleware redirects unauthenticated web users to /login.
derived:
  - Passport should be integrated with the existing Identity provider rather than creating a second user model or credential authority.
  - The first Phase 2 client registration should be a public authorization-code client with the fixed loopback path http://127.0.0.1/callback and no confidential client secret.
  - Short bootstrap token lifetimes should be configured now; Phase 3 will revoke the OAuth token family immediately after successful Game Login Ticket issuance if refresh tokens cannot be suppressed safely.
unknown:
  - Exact production native client ID remains deployment data and must not be guessed.
  - Exact deployed OAuth signing keys remain secret deployment state and must not be committed.
  - Refresh-token immediate revocation after ticket issuance remains a Phase 3 implementation concern because ticket issuance is not public in Phase 2.
conflicts:
  - none
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Custom OAuth protocol remains rejected; Passport provides the required standardized grant and PKCE behavior.
  - Shipping a confidential client secret in OTClient is rejected because native clients cannot keep such a secret.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260722-native-oauth-pkce.md
validation:
  - command: not-run
    result: NOT_RUN
    evidence: implementation has not started
blockers:
  - none
next_action: Archive the merged Phase 1 task, open the Phase 2 draft PR, then lock Passport into Composer using a temporary branch-only dependency bootstrap workflow.
```

## Notes

This phase ends at OAuth/PKCE authorization bootstrap. Game Login Ticket HTTP issuance/redeem is Phase 3.
