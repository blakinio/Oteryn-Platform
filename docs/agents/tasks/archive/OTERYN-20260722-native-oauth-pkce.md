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
- [x] OAuth access-token and refresh-token lifetimes are explicitly short and bounded for the bootstrap phase.
- [x] The only initial game bootstrap scope is `game:ticket`.
- [x] A first-party native public client can be created/ensured without a confidential client secret and with the registered loopback redirect contract.
- [x] The native redirect contract uses `http://127.0.0.1/callback` with RFC 8252 dynamic loopback ports.
- [x] `/oauth/authorize` uses the existing browser Identity password/MFA flow.
- [x] The Oteryn authorization view can approve or deny `game:ticket` without exposing credentials.
- [x] PKCE S256 is required and invalid verification fails closed.
- [x] Non-loopback and wrong-path redirects fail closed.
- [x] OAuth keys are never committed.
- [x] No Game Login Ticket endpoint, Gateway runtime or Canary adapter is included.
- [x] Required focused and repository workflows pass.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/archive/OTERYN-20260722-native-oauth-pkce.md
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
modules:
  - auth-identity
  - architecture
  - api
  - security
  - testing
dependencies:
  - Phase 1 merged as fc6b70fa11f3bb9958b405fc76d8918c49381668
blockers:
  - none
cross_repository_tasks:
  - OTClient native-client implementation remains separate
  - Canary remained untouched
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-22T07:16:00Z
head: 27fa277c5def0e151d7ee013acef188dbfd6f463
branch: task/OTERYN-20260722-native-oauth-pkce
pr: 119
status: completed
context_routes:
  - auth-identity
  - architecture
  - api
  - security
  - testing
owned_paths:
  - docs/agents/tasks/archive/OTERYN-20260722-native-oauth-pkce.md
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
  - PR 119 was squash-merged to main as 27fa277c5def0e151d7ee013acef188dbfd6f463.
  - Laravel Passport v13.7.5 is locked and application migrations are owned by Oteryn Platform.
  - Identity remains the sole reusable password and MFA authority.
  - The native OAuth client is public, authorization-code only and has no confidential secret.
  - PKCE S256, dynamic IPv4 loopback ports, exact callback path and bounded token lifetimes are enforced and tested.
  - Wrong verifier, missing verifier, password grant, invalid scope and non-loopback redirects fail closed.
  - Final PR head faa47af04be135e8e518f10eda1bae167114b888 passed CI, Agent Governance, Platform DB Outage Validation, Phase 7 Production-Like Validation and Acceptance E2E and Visual UX.
derived:
  - Phase 3 must revoke the OAuth access/refresh token family after successful Game Login Ticket issuance.
unknown:
  - Exact production native client ID and OAuth signing keys remain deployment state.
conflicts:
  - none
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Custom OAuth protocol and a shipped confidential client secret remain rejected.
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
  - resources/views/game-auth/oauth/authorize.blade.php
  - tests/Feature/GameAuth/OAuth/
validation:
  - command: CI run 29899279330
    result: PASS
    evidence: final checkpoint-only head passed required repository CI
  - command: Agent Governance run 29899279416
    result: PASS
    evidence: final checkpoint-only head passed governance
  - command: Platform DB Outage Validation run 29899279328
    result: PASS
    evidence: final checkpoint-only head passed fail-closed outage validation
  - command: Phase 7 Production-Like Validation run 29899279297
    result: PASS
    evidence: final checkpoint-only head passed production-like validation
  - command: Acceptance E2E and Visual UX run 29899279279
    result: PASS
    evidence: final checkpoint-only head passed required browser acceptance
blockers:
  - none
next_action: Continue Phase 3 in OTERYN-20260722-game-ticket-http-boundary.
```

## Notes

Completed and archived after PR 119 merged. Game Login Ticket HTTP issuance/redeem remains Phase 3.
