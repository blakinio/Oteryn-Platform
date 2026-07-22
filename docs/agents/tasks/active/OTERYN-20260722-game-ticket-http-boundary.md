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

- [x] `POST /api/v1/game-auth/tickets` requires a valid Passport bearer from the configured first-party public native client with `game:ticket` scope.
- [x] The issuance request accepts only protocol version 1 and never accepts client-supplied Identity or Canary account ownership.
- [x] Issuance uses the existing `IssueGameLoginTicket` domain operation and returns the opaque ticket once with bounded expiry metadata.
- [x] Successful issuance atomically revokes the current OAuth access token and its related refresh token so OAuth remains a short bootstrap lifecycle.
- [x] Failed issuance does not revoke a still-valid OAuth bootstrap credential unless policy explicitly requires terminal denial.
- [x] `POST /internal/v1/game-auth/tickets/redeem` requires a dedicated externally injected Gateway service credential and never trusts network location alone.
- [x] Service authentication stores/configures only SHA-256 credential hashes, supports bounded rotation through multiple accepted hashes, and never logs raw credentials.
- [x] Redeem validates protocol version, expected audience and bounded ticket shape before invoking the existing atomic `RedeemGameLoginTicket` domain operation.
- [x] Invalid, expired, reused, revoked, disabled-Identity and unavailable-binding outcomes fail closed with bounded non-secret JSON errors.
- [x] Issuance and redeem are rate-limited defensively without introducing fallback behavior.
- [x] No request/response/error/audit logging records raw OAuth tokens, Game Login Tickets or Gateway credentials.
- [x] Focused feature tests cover success, wrong client, missing scope, invalid protocol, token-family revocation, invalid service credential, wrong audience, reuse and response redaction boundaries.
- [x] No Gateway runtime, Game Session adapter, OTClient change, Canary change or legacy password fallback is introduced.
- [ ] Deterministic concurrent redeem coverage proves exactly one successful consume against the shared transactional store.
- [ ] Required CI, governance, DB-outage, production-like and acceptance workflows pass on final head.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-http-boundary.md
  - docs/agents/tasks/archive/OTERYN-20260722-native-oauth-pkce.md
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
  - final CI and deterministic concurrent redeem validation
cross_repository_tasks:
  - Game Gateway remains a separately bounded future task in this repository
  - OTClient implementation remains separately authorized
  - Canary and upstream login-server remain read-only
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-22T07:38:00Z
head: dc147403a42cc5a3eb1e8ec00fc051dd0ca575db
branch: task/OTERYN-20260722-game-ticket-http-boundary
pr: 120
status: validating
context_routes:
  - auth-identity
  - api
  - security
  - database
  - testing
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-http-boundary.md
  - docs/agents/tasks/archive/OTERYN-20260722-native-oauth-pkce.md
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
  - Existing IssueGameLoginTicket and RedeemGameLoginTicket provide transaction locking, exact binding and generation validation, hashed ticket storage, expiry enforcement and single-use consume semantics.
  - Public issuance is routed separately under /api/v1/game-auth/tickets and requires the Passport API guard.
  - Passport 13.7.5 supplies an authenticated Laravel Passport AccessToken wrapper; issuance resolves its authenticated oauth_access_token_id and locks the corresponding persisted Token row.
  - Ticket creation and access/refresh-token-family revocation execute in one Platform database transaction.
  - Private redeem is routed separately under /internal/v1/game-auth/tickets/redeem and requires a rotatable externally injected Gateway bearer credential represented only by configured SHA-256 hashes.
  - Public and private requests prohibit client-supplied Identity or Canary account ownership.
  - Controllers and service-authentication failures use bounded JSON envelopes and no-store/no-cache responses without echoing bearer material.
  - Focused feature tests exercise the real browser authorization, PKCE exchange, Passport bearer, ticket issuance, OAuth family revocation, service authentication and replay denial paths.
  - A one-shot formatter applied repository Pint rules and removed its temporary workflow in commit dc147403a42cc5a3eb1e8ec00fc051dd0ca575db.
derived:
  - Thin HTTP orchestration over the Phase 1 domain preserves one authoritative ticket lifecycle implementation.
  - Locking is scoped to the presented access token and per-Identity ticket/binding state; the shared native-client row is not locked, avoiding global login serialization.
  - A normal checkpoint commit is required after the bot formatter commit because GitHub-token pushes do not trigger downstream workflows.
unknown:
  - Exact production Gateway service credential hashes remain secret deployment state and must not be committed.
  - Exact production internal ingress/TLS/mTLS mechanism remains a deployment verification concern; v1 application authentication uses rotatable bearer-secret hashes.
  - Final PHP 8.5 static-analysis and feature-test results for the formatted implementation head are not yet known.
conflicts:
  - none
first_failure:
  marker: final-formatted-head-not-yet-validated
  evidence: the previous CI head stopped at Pint before static analysis/tests; formatting was then applied by a bot commit that did not trigger workflows
rejected_hypotheses:
  - Reimplementing ticket state in controllers is rejected because Phase 1 already owns atomic lifecycle correctness.
  - Using the OAuth access token directly at Gateway is rejected by ADR 0009 and both integration contracts.
  - Authenticating Gateway by private IP/network placement alone is rejected.
  - Treating Passport Identity token context as an Eloquent Token is rejected; Passport 13 attaches an AccessToken wrapper and the persisted token must be resolved by its authenticated identifier.
  - Locking the shared native OAuth client row during every issuance is rejected because it would serialize unrelated logins.
changed_paths:
  - .env.example
  - app/GameAuth/OAuth/GameOAuthBootstrapDenied.php
  - app/GameAuth/OAuth/IssueGameLoginTicketFromOAuth.php
  - app/GameAuth/OAuth/NativeOAuthClientManager.php
  - app/Http/Controllers/GameAuth/IssueGameLoginTicketController.php
  - app/Http/Controllers/GameAuth/RedeemGameLoginTicketController.php
  - app/Http/Middleware/RequireGameGatewayService.php
  - app/Http/Requests/GameAuth/IssueGameLoginTicketRequest.php
  - app/Http/Requests/GameAuth/RedeemGameLoginTicketRequest.php
  - app/Providers/AppServiceProvider.php
  - bootstrap/app.php
  - config/game-auth.php
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-http-boundary.md
  - docs/agents/tasks/archive/OTERYN-20260722-native-oauth-pkce.md
  - routes/api.php
  - routes/internal.php
  - tests/Feature/GameAuth/Http/GameLoginTicketIssuanceApiTest.php
  - tests/Feature/GameAuth/Http/GameLoginTicketRedeemApiTest.php
validation:
  - command: Agent Governance on implementation heads through 806d7b3314535cd8a5c68e3825b06011c5467dcc
    result: PASS
    evidence: governance remained green throughout implementation
  - command: Platform DB Outage Validation run 29900389109
    result: PASS
    evidence: fail-closed database-outage profile passed before the formatting-only change
  - command: CI run 29900389106
    result: FAIL
    evidence: stopped at Pint before static analysis/tests; formatter subsequently corrected the reported style delta
  - command: Phase 7 Production-Like Validation run 29900389087
    result: FAIL
    evidence: critical regression suite failed on the pre-format/pre-final-runtime-fix head and requires rerun
  - command: final formatted implementation validation
    result: NOT_RUN
    evidence: triggered by this checkpoint commit
blockers:
  - final required workflows
  - deterministic concurrent redeem proof
next_action: Inspect the first final-head CI failure marker, fix only proven defects, then add deterministic two-competitor redeem coverage and finalize the contracts/checkpoint.
```

## Notes

This task ends at Identity-owned ticket HTTP issuance/redeem. Game Gateway login orchestration and Game Session creation remain Phase 4 and later work.
