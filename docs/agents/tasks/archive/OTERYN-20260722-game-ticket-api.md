---
task_id: OTERYN-20260722-game-ticket-api
required_reads:
  - AGENTS.md
  - docs/agents/CONTEXT_HANDOFF.md
  - docs/architecture/adr/0009-oteryn-game-authentication-architecture.md
  - docs/architecture/GAME_AUTH_THREAT_MODEL.md
  - docs/architecture/GAME_AUTH_ROLLOUT_PLAN.md
  - docs/contracts/GAME_GATEWAY_IDENTITY_CONTRACT.md
  - docs/contracts/OTCLIENT_GAME_AUTH_CONTRACT.md
search_first:
  - open PRs and active tasks overlapping Game Login Ticket issuance, private redeem, Passport token lifecycle, API routes, or Gateway service authentication
  - current Game Login Ticket domain services and Passport 13.7.5 token models
  - current application routing/bootstrap and rate-limiter patterns
  - MariaDB-capable CI patterns for true concurrent consume proof
optional_reads:
  - docs/architecture/SECURITY_ARCHITECTURE.md
  - docs/architecture/TEST_STRATEGY.md
---

# OTERYN-20260722-game-ticket-api

## Goal

Implement Phase 3 of ADR 0009: public OAuth-scoped Game Login Ticket issuance and private service-authenticated atomic redeem, including deterministic bootstrap OAuth token-family revocation and an executable independent-connection MariaDB proof that exactly one concurrent redeem succeeds.

## Acceptance criteria

- [x] `POST /api/v1/game-auth/tickets` requires Passport authentication, the `game:ticket` scope, and the exact first-party public native client contract.
- [x] Ticket issuance accepts no client-authoritative Identity or Canary account ID.
- [x] Successful issuance returns protocol v1, the opaque ticket once, and bounded integer expiry information.
- [x] Ticket issuance and revocation of the current OAuth access token plus associated refresh-token family commit atomically; a successful bootstrap credential cannot mint a second ticket.
- [x] Concurrent issuance attempts using the same OAuth access token cannot both succeed.
- [x] `POST /internal/v1/game-auth/tickets/redeem` is outside the public `/api` prefix and requires a dedicated Gateway service credential verified against a configured SHA-256 hash.
- [x] Missing/invalid service authentication fails closed and raw service credentials are never committed or logged.
- [x] Private redeem validates protocol version and configured `oteryn-game-gateway` audience without trusting client-supplied account ownership.
- [x] Successful redeem returns only bounded authorization data required by Gateway; no password, MFA, OAuth or web-session material is exposed.
- [x] Expired/reused/revoked/wrong-audience/stale-generation/binding-drift tickets fail closed without password or longer-lived-session fallback.
- [x] A real MariaDB test with independent concurrent processes proves exactly one redeem winner for one ticket.
- [x] Public/private endpoints have bounded rate limiting and no credential-bearing query-string transport.
- [x] No Game Gateway runtime, OTClient runtime change, Canary adapter or legacy-login removal is included in this task.
- [x] Governance, CI, concurrency proof, DB-outage, production-like and acceptance workflows pass on the validated clean-diff checkpoint head.

## Ownership

```yaml
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-api.md
  - docs/agents/tasks/archive/OTERYN-20260722-native-oauth-pkce.md
  - docs/agents/tasks/active/OTERYN-20260722-native-oauth-pkce.md
  - app/GameAuth/OAuth/
  - app/GameAuth/Tickets/
  - app/Http/Controllers/GameAuth/
  - app/Http/Middleware/GameAuth/
  - app/Providers/AppServiceProvider.php
  - bootstrap/app.php
  - config/game-auth.php
  - routes/api.php
  - routes/internal.php
  - tests/Feature/GameAuth/
  - .github/workflows/game-auth-ticket-concurrency.yml
modules:
  - auth-identity
  - api
  - security
  - testing
  - canary-integration
dependencies:
  - Phase 2 merged on main as 27fa277c5def0e151d7ee013acef188dbfd6f463
  - Phase 1 Game Login Ticket domain and revocation generation
  - Passport 13.7.5 native public-client OAuth bootstrap
blockers:
  - none
cross_repository_tasks:
  - Game Gateway consumes the private redeem contract in Phase 4
  - OTClient consumes public ticket issuance and Gateway login in its coordinated future task
  - Canary remains untouched in Phase 3
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-22T07:49:00Z
head: 491852173c15b9dbdabe95bca622793756b20d1d
branch: task/OTERYN-20260722-game-ticket-api
pr: 121
status: ready
context_routes:
  - auth-identity
  - api
  - security
  - testing
  - canary-integration
owned_paths:
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-api.md
  - docs/agents/tasks/archive/OTERYN-20260722-native-oauth-pkce.md
  - docs/agents/tasks/active/OTERYN-20260722-native-oauth-pkce.md
  - app/GameAuth/OAuth/
  - app/GameAuth/Tickets/
  - app/Http/Controllers/GameAuth/
  - app/Http/Middleware/GameAuth/
  - app/Providers/AppServiceProvider.php
  - bootstrap/app.php
  - config/game-auth.php
  - routes/api.php
  - routes/internal.php
  - tests/Feature/GameAuth/
  - .github/workflows/game-auth-ticket-concurrency.yml
proven:
  - Phase 2 PR 119 merged to main as 27fa277c5def0e151d7ee013acef188dbfd6f463 and Phase 3 started from that exact main.
  - Public ticket issuance is exposed only as POST /api/v1/game-auth/tickets behind Passport auth:api and bounded rate limiting.
  - The issuance request accepts only protocol_version and explicitly prohibits identity_id, account_id and canary_account_id ownership inputs.
  - Passport bearer authentication attaches a Passport AccessToken containing oauth_access_token_id; the issuance controller extracts that identifier and the exchange service re-locks the authoritative oauth_access_tokens row.
  - IssueGameLoginTicketFromOAuth validates current user, expiry, revoked state, game:ticket scope and exact first-party native client before ticket issuance.
  - Ticket creation, refresh-token revocation and access-token revocation participate in one outer Platform database transaction.
  - Private redeem is exposed as POST /internal/v1/game-auth/tickets/redeem under API middleware without the public /api prefix.
  - Gateway service authentication stores only an injected SHA-256 hash of the dedicated bearer credential and compares the presented credential with hash_equals; missing configuration fails closed with 503 and invalid credentials fail with 401.
  - Private redeem returns only protocol version, canary_account_id, security_generation and redeemed_at; it does not return Identity IDs, passwords, OAuth tokens, tickets, MFA or web-session material.
  - Durable workflow Game Auth Ticket Concurrency uses MariaDB 11.8 and two independent forked processes.
  - Game Auth Ticket Concurrency run 29901500641 succeeded on clean-diff checkpoint head 491852173c15b9dbdabe95bca622793756b20d1d, proving exactly one concurrent redeem success and exactly one concurrent OAuth-token-to-ticket exchange success.
  - Temporary diagnostic workflow was removed before clean-diff validation.
  - Diagnostic run 29901380157 on implementation head 6846a37449fae02a964885f6dcd794820cf4c9fc reported Pint exit 0, PHPStan exit 0 and composer test exit 0 with 33 tests / 1280 assertions.
  - Agent Governance run 29901500552 succeeded on clean-diff checkpoint head 491852173c15b9dbdabe95bca622793756b20d1d.
  - CI run 29901500545 succeeded on clean-diff checkpoint head 491852173c15b9dbdabe95bca622793756b20d1d.
  - Platform DB Outage Validation run 29901500619 succeeded on clean-diff checkpoint head 491852173c15b9dbdabe95bca622793756b20d1d.
  - Phase 7 Production-Like Validation run 29901500531 succeeded on clean-diff checkpoint head 491852173c15b9dbdabe95bca622793756b20d1d.
  - Acceptance E2E and Visual UX run 29901500535 succeeded on clean-diff checkpoint head 491852173c15b9dbdabe95bca622793756b20d1d.
derived:
  - The first-release OAuth credential is now a true bootstrap credential: successful game-ticket issuance consumes its useful game-login authority by revoking both the current access token and associated refresh-token family.
  - The database row lock on the OAuth access token prevents two concurrent exchanges of the same bootstrap credential from both minting tickets; this is directly proven by the MariaDB concurrency workflow.
  - The ticket row lock and used_at transition provide exactly-one concurrent redeem semantics across independent processes; this is directly proven by the same durable workflow.
unknown:
  - Exact production Gateway service credential value and secret-manager delivery remain deployment state and are intentionally not committed.
  - Production private-network/mTLS topology remains a later deployment verification gate; Phase 3 implements independent application-level service authentication as the minimum accepted boundary.
conflicts:
  - none
first_failure:
  marker: initial Phase 3 CI/diagnostic validation
  evidence: exact diagnostics found Passport bearer currentAccessToken is AccessToken rather than the Eloquent Token model, several PHPStan mixed-value assumptions, and expires_in JSON numeric typing; each was corrected against Passport 13.7.5 and the final diagnostics/clean workflows passed
rejected_hypotheses:
  - Reusing the OAuth access token itself as the Game Login Ticket remains rejected.
  - Persisting the raw Gateway service credential in repository configuration remains rejected.
  - Treating lockForUpdate alone as concurrent proof remains rejected; the dedicated MariaDB workflow now provides executable independent-process evidence.
  - Catching all Throwable during native-client validation was rejected; only the expected LogicException contract mismatch is converted to OAuthBootstrapDenied.
changed_paths:
  - .github/workflows/game-auth-ticket-concurrency.yml
  - app/GameAuth/OAuth/IssueGameLoginTicketFromOAuth.php
  - app/GameAuth/OAuth/NativeOAuthClientManager.php
  - app/GameAuth/OAuth/OAuthBootstrapDenied.php
  - app/Http/Controllers/GameAuth/GameLoginTicketIssueController.php
  - app/Http/Controllers/GameAuth/GameLoginTicketRedeemController.php
  - app/Http/Middleware/GameAuth/RequireGatewayServiceCredential.php
  - app/Providers/AppServiceProvider.php
  - bootstrap/app.php
  - config/game-auth.php
  - docs/agents/tasks/active/OTERYN-20260722-game-ticket-api.md
  - docs/agents/tasks/archive/OTERYN-20260722-native-oauth-pkce.md
  - routes/api.php
  - routes/internal.php
  - tests/Feature/GameAuth/Concurrency/GameTicketConcurrencyTest.php
  - tests/Feature/GameAuth/GameLoginTicketApiTest.php
  - tests/Feature/GameAuth/GameLoginTicketRedeemApiTest.php
  - tests/Feature/GameAuth/OAuth/Concerns/CreatesNativeOAuthBootstrapToken.php
validation:
  - command: GitHub changed-file inventory for PR 121
    result: PASS
    evidence: temporary diagnostic workflow is absent; remaining paths are within declared Phase 3 ownership
  - command: Native Game Auth Ticket diagnostics run 29901380157
    result: PASS
    evidence: Pint exit 0, PHPStan exit 0, composer test exit 0 with 33 tests / 1280 assertions
  - command: GitHub Actions Game Auth Ticket Concurrency run 29901500641
    result: PASS
    evidence: independent-process MariaDB exactly-one proof passed for ticket redeem and OAuth exchange on 491852173c15b9dbdabe95bca622793756b20d1d
  - command: GitHub Actions Agent Governance run 29901500552
    result: PASS
    evidence: governance passed on 491852173c15b9dbdabe95bca622793756b20d1d
  - command: GitHub Actions CI run 29901500545
    result: PASS
    evidence: Composer validation/audit, Pint, PHPStan and full tests passed on 491852173c15b9dbdabe95bca622793756b20d1d
  - command: GitHub Actions Platform DB Outage Validation run 29901500619
    result: PASS
    evidence: fail-closed database outage/recovery validation passed on 491852173c15b9dbdabe95bca622793756b20d1d
  - command: GitHub Actions Phase 7 Production-Like Validation run 29901500531
    result: PASS
    evidence: production-like migration, privilege, dependency, critical regression, runtime, backup/restore and upgrade/rollback validation passed on 491852173c15b9dbdabe95bca622793756b20d1d
  - command: GitHub Actions Acceptance E2E and Visual UX run 29901500535
    result: PASS
    evidence: required browser portability, responsive, resilience, accessibility and durable acceptance evidence passed on 491852173c15b9dbdabe95bca622793756b20d1d
blockers:
  - none
next_action: Verify the same required workflows pass on the final checkpoint commit, then merge PR 121 if review and merge gates remain satisfied.
```

## Notes

This phase remains Platform-only. Game Gateway runtime starts in Phase 4.
