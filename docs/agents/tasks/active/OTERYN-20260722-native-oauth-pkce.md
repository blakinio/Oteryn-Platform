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

- [x] Laravel Passport 13.x is locked in Composer and its package migrations are available to the application.
- [x] `Identity` implements Passport `OAuthenticatable` and `HasApiTokens` without changing the authoritative password/MFA boundary.
- [x] An `api` auth guard uses Passport with the existing Identity provider.
- [x] OAuth access-token and refresh-token lifetimes are explicitly short and bounded for the bootstrap phase; no one-year/default long-lived semantics remain.
- [x] The only initial game bootstrap scope is `game:ticket`.
- [x] A first-party native public client can be created/ensured without a confidential client secret and with the registered loopback redirect contract.
- [x] The native redirect contract uses `http://127.0.0.1/callback`; RFC 8252 dynamic loopback ports remain handled by the selected OAuth server implementation.
- [x] `/oauth/authorize` uses the existing browser Identity session flow; unauthenticated users are redirected through Oteryn login and confirmed MFA continues via `redirect()->intended(...)`.
- [x] A bounded Oteryn authorization approval view can approve or deny the requested `game:ticket` scope without exposing credentials.
- [x] PKCE S256 is required for the native public client; missing/invalid verifier fails closed in focused integration coverage.
- [x] Unregistered/non-loopback/wrong-path redirects fail closed; the registered loopback path with a dynamic port is accepted according to the package contract.
- [x] OAuth keys are never committed; deployment uses Passport key generation or injected `PASSPORT_PRIVATE_KEY` / `PASSPORT_PUBLIC_KEY` material.
- [x] No Game Login Ticket public endpoint, private redeem endpoint, Gateway runtime, or Canary adapter is added.
- [x] Relevant focused tests, governance, CI, DB-outage, production-like and acceptance workflows pass on final implementation head.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-native-oauth-pkce.md
  - docs/agents/tasks/archive/OTERYN-20260722-game-auth-domain-foundation.md
  - docs/agents/tasks/active/OTERYN-20260722-game-auth-domain-foundation.md
  - composer.json
  - composer.lock
  - database/migrations/
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
updated_at: 2026-07-22T07:10:00Z
head: 1cd4eb9c1efeb6348b3aa1ff0a0d5fbc375dcf03
branch: task/OTERYN-20260722-native-oauth-pkce
pr: 119
status: ready
context_routes:
  - auth-identity
  - architecture
  - api
  - security
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-native-oauth-pkce.md
  - docs/agents/tasks/archive/OTERYN-20260722-game-auth-domain-foundation.md
  - composer.json
  - composer.lock
  - database/migrations/
  - app/Console/Commands/
  - app/GameAuth/OAuth/
  - app/Identity/Models/Identity.php
  - app/Providers/AppServiceProvider.php
  - config/auth.php
  - config/game-auth.php
  - resources/views/game-auth/
  - tests/Feature/GameAuth/OAuth/
  - tests/Unit/GameAuth/OAuth/
proven:
  - Phase 1 PR 118 was squash-merged to main as fc6b70fa11f3bb9958b405fc76d8918c49381668.
  - Laravel Passport v13.7.5 is locked in Composer and its migrations are application-owned.
  - Identity remains the authoritative password and MFA boundary while implementing Passport OAuthenticatable and HasApiTokens.
  - The initial client is a public authorization-code client without a confidential secret and with the registered http://127.0.0.1/callback loopback contract.
  - Dynamic loopback ports work with the registered fixed path; non-loopback and wrong-path redirects fail closed.
  - PKCE S256 is required; missing or invalid verifier and password-grant attempts fail closed.
  - OAuth bootstrap access and refresh token lifetimes are bounded to 5 and 10 minutes by default.
  - The only registered initial scope is game:ticket.
  - Existing browser Identity login and MFA preserve the interrupted OAuth request through redirect()->intended(...).
  - No Game Login Ticket HTTP endpoint, private redeem endpoint, Game Gateway runtime, OTClient change or Canary change is included.
  - Final implementation head 1cd4eb9c1efeb6348b3aa1ff0a0d5fbc375dcf03 passed CI, Agent Governance, Platform DB Outage Validation, Phase 7 Production-Like Validation and Acceptance E2E and Visual UX.
derived:
  - Passport is integrated with the existing Identity provider rather than introducing a second credential authority.
  - Phase 3 must terminate first-release OAuth bootstrap semantics after successful ticket issuance, including refresh-token-family revocation if issuance cannot be disabled safely.
unknown:
  - Exact production native client ID remains deployment data and must not be guessed.
  - Exact deployed OAuth signing keys remain secret deployment state and must not be committed.
conflicts:
  - none
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Custom OAuth protocol remains rejected; Passport provides the required standardized authorization-code grant and PKCE behavior.
  - Shipping a confidential client secret in OTClient remains rejected because native clients cannot keep such a secret.
  - The two earlier cancelled Acceptance attempts were not treated as application failures because cancellation occurred during runner/setup work before application assertions; a fresh head subsequently passed the complete workflow.
changed_paths:
  - app/Console/Commands/EnsureNativeOAuthClient.php
  - app/GameAuth/OAuth/NativeOAuthClientManager.php
  - app/GameAuth/OAuth/RequirePublicClientPkceS256.php
  - app/Identity/Models/Identity.php
  - app/Providers/AppServiceProvider.php
  - composer.json
  - composer.lock
  - config/auth.php
  - config/game-auth.php
  - database/migrations/2026_07_21_225744_create_oauth_auth_codes_table.php
  - database/migrations/2026_07_21_225745_create_oauth_access_tokens_table.php
  - database/migrations/2026_07_21_225746_create_oauth_refresh_tokens_table.php
  - database/migrations/2026_07_21_225747_create_oauth_clients_table.php
  - database/migrations/2026_07_21_225748_create_oauth_device_codes_table.php
  - docs/agents/tasks/active/OTERYN-20260722-native-oauth-pkce.md
  - docs/agents/tasks/archive/OTERYN-20260722-game-auth-domain-foundation.md
  - resources/views/game-auth/oauth/authorize.blade.php
  - tests/Feature/GameAuth/OAuth/Concerns/ConfiguresEphemeralPassportKeys.php
  - tests/Feature/GameAuth/OAuth/NativeOAuthClientManagerTest.php
  - tests/Feature/GameAuth/OAuth/NativeOAuthGrantPolicyTest.php
  - tests/Feature/GameAuth/OAuth/NativeOAuthPkceTest.php
  - tests/Feature/GameAuth/OAuth/PublicClientPkcePolicyTest.php
validation:
  - command: CI run 29898930382
    result: PASS
    evidence: required repository CI passed on implementation head 1cd4eb9c1efeb6348b3aa1ff0a0d5fbc375dcf03
  - command: Agent Governance run 29898930407
    result: PASS
    evidence: governance validation passed on the implementation head
  - command: Platform DB Outage Validation run 29898930472
    result: PASS
    evidence: fail-closed Platform database outage validation passed
  - command: Phase 7 Production-Like Validation run 29898930299
    result: PASS
    evidence: controlled production-like validation passed
  - command: Acceptance E2E and Visual UX run 29898930430
    result: PASS
    evidence: required critical browser, responsive, portability, resilience and accessibility acceptance completed successfully
blockers:
  - none
next_action: Merge PR 119 by squash after the checkpoint-only commit receives its required checks, then archive this task and start the separately bounded Phase 3 Game Login Ticket HTTP issuance and private redeem task from current main.
```

## Notes

This phase ends at OAuth/PKCE authorization bootstrap. Game Login Ticket HTTP issuance/redeem is Phase 3.
