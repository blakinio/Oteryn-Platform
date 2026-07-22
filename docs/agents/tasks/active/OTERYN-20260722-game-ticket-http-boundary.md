---
task_id: OTERYN-20260722-game-ticket-http-boundary
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/adr/0009-oteryn-game-authentication-architecture.md
  - docs/architecture/GAME_AUTH_THREAT_MODEL.md
  - docs/contracts/OTCLIENT_GAME_AUTH_CONTRACT.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
  - docs/architecture/SECURITY_ARCHITECTURE.md
search_first:
  - open PRs and active tasks overlapping Game Login Ticket issuance, redeem, Passport token revocation, API routing, or Gateway service authentication
  - current Phase 1 ticket domain implementation and concurrency tests
  - current Passport 13.7.5 access/refresh-token revocation APIs
  - existing request correlation, rate limiting and JSON error conventions
optional_reads:
  - docs/architecture/TEST_STRATEGY.md
  - docs/architecture/GAME_AUTH_ROLLOUT_PLAN.md
---

# OTERYN-20260722-game-ticket-http-boundary

## Goal

Implement Phase 3 of ADR 0009 inside Oteryn Platform: expose the authenticated public Game Login Ticket issuance API and the service-authenticated private atomic redeem API over the already merged Phase 1 ticket domain. Revoke the successful native OAuth bootstrap token family after issuance. Do not implement Game Gateway and do not modify OTClient, Canary or upstream login-server.

## Acceptance criteria

- [ ] `POST /api/v1/game-auth/tickets` requires a valid Passport bearer from the configured first-party public native client with `game:ticket` scope.
- [ ] The issuance request accepts only protocol version 1 and never accepts client-supplied Identity or Canary account ownership.
- [ ] Issuance uses the existing `IssueGameLoginTicket` domain operation and returns the opaque ticket once with bounded expiry metadata.
- [ ] Successful issuance atomically revokes the current OAuth access token and its related refresh token so OAuth remains a short bootstrap lifecycle.
- [ ] Failed issuance does not revoke a still-valid OAuth bootstrap credential unless policy explicitly requires terminal denial.
- [ ] `POST /internal/v1/game-auth/tickets/redeem` requires a dedicated externally injected Gateway service credential and never trusts network location alone.
- [ ] Service authentication stores/configures only SHA-256 credential hashes, supports bounded rotation through multiple accepted hashes, and never logs raw credentials.
- [ ] Redeem validates protocol version, expected audience and bounded ticket shape before invoking the existing atomic `RedeemGameLoginTicket` domain operation.
- [ ] Invalid, expired, reused, revoked, disabled-Identity and unavailable-binding outcomes fail closed with bounded non-secret JSON errors.
- [ ] Issuance and redeem are rate-limited defensively without introducing fallback behavior.
- [ ] No request/response/error/audit logging records raw OAuth tokens, Game Login Tickets or Gateway credentials.
- [ ] Focused feature tests cover success, wrong client, missing scope, invalid protocol, token-family revocation, invalid service credential, wrong audience, reuse, outage and response/log redaction boundaries.
- [ ] No Gateway runtime, Game Session adapter, OTClient change, Canary change or legacy password fallback is introduced.
- [ ] Required CI, governance, DB-outage, production-like and acceptance workflows pass on final head.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-http-boundary.md
  - docs/agents/tasks/archive/OTERYN-20260722-native-oauth-pkce.md
  - docs/agents/tasks/active/OTERYN-20260722-native-oauth-pkce.md
  - bootstrap/app.php
  - routes/api.php
  - routes/internal.php
  - app/Http/Controllers/GameAuth/
  - app/Http/Requests/GameAuth/
  - app/Http/Middleware/RequireGameGatewayService.php
  - app/GameAuth/OAuth/
  - app/Providers/AppServiceProvider.php
  - config/game-auth.php
  - .env.example
  - tests/Feature/GameAuth/Http/
  - docs/contracts/OTCLIENT_GAME_AUTH_CONTRACT.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
modules:
  - auth-identity
  - api
  - security
  - database
  - testing
dependencies:
  - Phase 1 game-auth domain foundation merged as fc6b70fa11f3bb9958b405fc76d8918c49381668
  - Phase 2 native OAuth PKCE merged as 27fa277c5def0e151d7ee013acef188dbfd6f463
  - existing ready immutable Identity to Canary account binding
blockers:
  - none
cross_repository_tasks:
  - Game Gateway remains a separately bounded future task in this repository
  - OTClient implementation remains separately authorized
  - Canary and upstream login-server remain read-only
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-22T07:15:00Z
head: 27fa277c5def0e151d7ee013acef188dbfd6f463
branch: task/OTERYN-20260722-game-ticket-http-boundary
pr: none
status: implementing
context_routes:
  - auth-identity
  - api
  - security
  - database
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-http-boundary.md
  - docs/agents/tasks/archive/OTERYN-20260722-native-oauth-pkce.md
  - docs/agents/tasks/active/OTERYN-20260722-native-oauth-pkce.md
  - bootstrap/app.php
  - routes/api.php
  - routes/internal.php
  - app/Http/Controllers/GameAuth/
  - app/Http/Requests/GameAuth/
  - app/Http/Middleware/RequireGameGatewayService.php
  - app/GameAuth/OAuth/
  - app/Providers/AppServiceProvider.php
  - config/game-auth.php
  - .env.example
  - tests/Feature/GameAuth/Http/
  - docs/contracts/OTCLIENT_GAME_AUTH_CONTRACT.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
proven:
  - Phase 2 PR 119 was squash-merged to main as 27fa277c5def0e151d7ee013acef188dbfd6f463 with all required workflows successful.
  - Existing IssueGameLoginTicket and RedeemGameLoginTicket already provide transaction locking, exact binding and generation validation, hashed ticket storage, expiry enforcement and single-use consume semantics.
  - Passport 13.7.5 exposes the authenticated current Token through Identity HasApiTokens and supports revoking the access token plus its related refresh token.
  - Oteryn request-completion logging records method, named route, status, duration and correlation ID but not request bodies or Authorization headers.
derived:
  - Phase 3 should add thin HTTP/authentication orchestration over the existing domain rather than duplicate ticket lifecycle logic.
  - Public issuance and private redeem require separate route files and middleware boundaries; browser web routes must remain unchanged.
  - OAuth ticket issuance and token-family revocation should share one Platform database transaction so neither durable outcome commits alone.
unknown:
  - Exact production Gateway service credential hashes remain secret deployment state and must not be committed.
  - Exact production internal ingress/TLS/mTLS mechanism remains a deployment verification concern; v1 application authentication will use rotatable bearer-secret hashes.
conflicts:
  - none
first_failure:
  marker: none
  evidence: none
rejected_hypotheses:
  - Reimplementing ticket state in controllers is rejected because Phase 1 already owns atomic lifecycle correctness.
  - Using the OAuth access token directly at Gateway is rejected by ADR 0009 and both integration contracts.
  - Authenticating Gateway by private IP/network placement alone is rejected.
changed_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-http-boundary.md
validation:
  - command: not-run
    result: NOT_RUN
    evidence: implementation has not started
blockers:
  - none
next_action: Open the draft PR, archive the completed Phase 2 task record, then add versioned API routing and focused public issuance orchestration with atomic OAuth token-family revocation.
```

## Notes

This task ends at Identity-owned ticket HTTP issuance/redeem. Game Gateway login orchestration and Game Session creation remain Phase 4 and later work.
