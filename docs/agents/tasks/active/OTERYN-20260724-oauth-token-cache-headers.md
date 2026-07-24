---
task_id: OTERYN-20260724-oauth-token-cache-headers
required_reads:
  - AGENTS.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/contracts/AUTH_GAME_LOGIN_CONTRACT.md
search_first:
  - existing sensitive-response cache middleware and tests
  - active tasks touching OAuth token responses
optional_reads:
  - docs/agents/tasks/active/OTERYN-20260723-native-auth-ephemeral-cutover-rehearsal.md
---

# OTERYN-20260724-oauth-token-cache-headers

## Goal

Apply the complete sensitive-response cache contract to OAuth token success and error responses without changing OAuth, PKCE, scope, token-family or ticket semantics.

## Acceptance criteria

- [x] `/oauth/token` success and error responses are routed through the complete sensitive cache policy in implementation.
- [x] Existing Game Login Ticket issue/redeem responses preserve the same headers in focused coverage.
- [x] Unrelated endpoints do not inherit sensitive-response cache headers in focused coverage.
- [x] Focused tests and required Platform CI pass.
- [x] The native-auth rehearsal verifies the headers over the real HTTPS boundary.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260724-oauth-token-cache-headers.md
  - app/Http/Middleware/GameAuth/PreventSensitiveGameAuthResponseCaching.php
  - bootstrap/app.php
  - tests/Unit/Http/Middleware/PreventSensitiveGameAuthResponseCachingTest.php
modules:
  - OAuth token response security headers
  - native game-auth sensitive-response caching boundary
dependencies:
  - OTERYN-20260724-trusted-reverse-proxy-scheme
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
head: 6170bb21b00ca55211272be24649a5930c14150b
branch: fix/OTERYN-20260724-oauth-token-cache-headers
pr: 133
status: ready
context_routes:
  - auth-identity
  - security
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260724-oauth-token-cache-headers.md
  - app/Http/Middleware/GameAuth/PreventSensitiveGameAuthResponseCaching.php
  - bootstrap/app.php
  - tests/Unit/Http/Middleware/PreventSensitiveGameAuthResponseCachingTest.php
proven:
  - Rehearsal run 30077854561 completed real OAuth Authorization Code + PKCE exchange and code-reuse rejection.
  - The same run recorded missing complete cache headers on OAuth token success and 400 error responses.
  - Game Login Ticket issuance succeeded with HTTP 200 and complete cache headers; the old probe incorrectly expected HTTP 201.
  - PreventSensitiveGameAuthResponseCaching centralizes the required header values for game ticket issue/redeem paths.
  - PR 133 makes that middleware conditional and global, adds /oauth/token, and preserves the exception response hook for 4xx responses.
  - Focused unit coverage checks OAuth token, ticket issue, ticket redeem and an unrelated health response.
  - Standard CI run 30079059960 passed Composer validation, dependency audit, Pint, PHPStan and the complete PHPUnit suite on implementation head b5dd6a7be5c704d5706241240e06f8bb8c4b5efe.
  - Production-like rehearsal run 30095854266 passed the OAuth token success and error cache-header assertions over the real HTTPS boundary using the exact combined Platform implementation.
  - Retained rehearsal artifact 8597730728 has digest sha256:e7e908e9129658654054a96adf641757edc2c904fc2b01a5b9fc97e393d18009 and classification PRODUCTION_LIKE_PROVEN.
derived:
  - OAuth token responses reuse the existing middleware contract rather than introducing a second header implementation.
unknown: []
conflicts: []
first_failure:
  marker: none
  evidence: Product CI and the full production-like HTTPS rehearsal passed the cache-header contract.
rejected_hypotheses:
  - OAuth PKCE exchange is broken: rejected because authorization_code_exchange is true and code reuse is rejected.
  - ticket issuance failed: rejected because Platform records POST /api/v1/game-auth/tickets status 200 and token-family reuse is rejected.
  - apply no-cache to every response: rejected because the middleware checks an explicit sensitive path allowlist.
changed_paths:
  - app/Http/Middleware/GameAuth/PreventSensitiveGameAuthResponseCaching.php
  - bootstrap/app.php
  - docs/agents/tasks/active/OTERYN-20260724-oauth-token-cache-headers.md
  - tests/Unit/Http/Middleware/PreventSensitiveGameAuthResponseCachingTest.php
validation:
  - command: Native Auth Ephemeral Cutover Rehearsal run 30077854561
    result: FAIL
    evidence: OAuth behavior passed; cache-header assertions and obsolete ticket status expectation failed.
  - command: CI run 30079059960 on b5dd6a7be5c704d5706241240e06f8bb8c4b5efe
    result: PASS
    evidence: Composer validation/audit, Pint, PHPStan and complete PHPUnit suite passed.
  - command: Native Auth Ephemeral Cutover Rehearsal run 30095854266
    result: PASS
    evidence: OAuth token success and error responses carried the complete sensitive no-cache policy over HTTPS.
blockers: []
next_action: Inspect checks on this checkpoint commit, mark PR 133 ready, and squash-merge when all required checks pass.
```
