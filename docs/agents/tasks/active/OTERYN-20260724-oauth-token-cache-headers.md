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

- [ ] `/oauth/token` success and error responses include `Cache-Control: no-store, no-cache, must-revalidate, private`, `Pragma: no-cache` and `Expires: 0`.
- [ ] Existing Game Login Ticket issue/redeem responses preserve the same headers.
- [ ] Unrelated endpoints do not inherit sensitive-response cache headers.
- [ ] Focused tests and required Platform CI pass.
- [ ] The native-auth rehearsal verifies the headers over the real HTTPS boundary.

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
updated_at: 2026-07-24T10:20:00+02:00
head: 9b80d3f4399c2d4a638a4d040f34cf60792acefc
branch: fix/OTERYN-20260724-oauth-token-cache-headers
base_branch: fix/OTERYN-20260724-trusted-reverse-proxy-scheme
pr: none
status: implementing
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
  - PreventSensitiveGameAuthResponseCaching already centralizes the required header values for game ticket issue/redeem paths.
derived:
  - OAuth token responses should reuse the existing middleware contract rather than introduce a second header implementation.
unknown:
  - final focused and full CI result
conflicts: []
first_failure:
  marker: oauth-token-cache-contract
  evidence: artifact 8590639830 oauth-pkce-result.json from run 30077854561
rejected_hypotheses:
  - OAuth PKCE exchange is broken: rejected because authorization_code_exchange is true and code reuse is rejected
  - ticket issuance failed: rejected because Platform log records POST /api/v1/game-auth/tickets status 200 and token-family reuse is rejected
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260724-oauth-token-cache-headers.md
validation:
  - command: Native Auth Ephemeral Cutover Rehearsal run 30077854561
    result: FAIL
    evidence: OAuth behavior passed; cache-header assertions and obsolete ticket status expectation failed
blockers:
  - none
next_action: make the shared middleware conditional and global, include /oauth/token, add focused tests, and run Platform CI.
```
