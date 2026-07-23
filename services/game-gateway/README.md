# Oteryn Game Gateway

Standalone Go runtime for the Oteryn game-login orchestration boundary defined by ADR 0009.

## Current Phase 4 scope

Implemented public surface:

```text
GET  /health
GET  /ready
GET  /version
POST /v1/login
```

`POST /v1/login` accepts only protocol v1 plus one opaque Game Login Ticket.

The Gateway:

1. redeems the ticket through the Oteryn Platform private Identity API;
2. receives the exact authorized Canary account ID;
3. obtains the single-world-ready World Registry/character login context through a separate narrow Platform private API;
4. invokes a configurable Game Session issuer through the internal HTTP contract;
5. returns the Game Session plus sanitized world and character data.

The Gateway has no Platform or Canary database credentials.

## Environment

Required:

```text
OTERYN_PLATFORM_BASE_URL
OTERYN_PLATFORM_SERVICE_TOKEN
GAME_SESSION_SERVICE_BASE_URL
GAME_SESSION_SERVICE_TOKEN
```

Optional:

```text
GATEWAY_LISTEN_ADDR=:8080
GATEWAY_REQUEST_TIMEOUT=5s
GATEWAY_VERSION=dev
```

Service credentials are injected runtime secrets. Do not commit them or place them in URLs.

Dependency URL policy is fail-closed:

- non-loopback Platform and Game Session service URLs must use `https://`;
- plain `http://` is accepted only for `localhost`, `127.0.0.0/8` and `::1` loopback development/test dependencies;
- standard Go HTTPS certificate and hostname verification remains enabled because the Gateway does not install an insecure TLS transport.

A private network is defense in depth, not a replacement for TLS or service authentication. Production routing should expose the Platform private API and Canary Game Session issuer only to the Gateway through explicit internal ingress/firewall rules.

## Service credential rotation

### Gateway -> Platform

Platform accepts one current and one optional previous Gateway service credential SHA-256 hash:

```text
GAME_AUTH_GATEWAY_SERVICE_TOKEN_SHA256
GAME_AUTH_GATEWAY_PREVIOUS_SERVICE_TOKEN_SHA256
```

The Gateway receives the corresponding plaintext bearer value only through `OTERYN_PLATFORM_SERVICE_TOKEN` at runtime.

Safe rotation order:

1. generate a new high-entropy service credential in the approved secret manager;
2. configure Platform `PREVIOUS` with the retiring hash and `SERVICE_TOKEN_SHA256` with the new hash;
3. deploy/reload Platform and verify both credentials are temporarily accepted;
4. roll Gateway instances to the new plaintext `OTERYN_PLATFORM_SERVICE_TOKEN`;
5. verify all Gateway instances use the new credential;
6. clear the Platform `PREVIOUS` hash and redeploy/reload Platform.

Never reuse the same service credential for Gateway -> Platform and Gateway -> Canary.

### Gateway -> Canary Game Session issuer

`GAME_SESSION_SERVICE_TOKEN` is a separate runtime secret. The Canary issuer must be configured with the matching SHA-256 service-credential hash set before the Gateway token is rotated. Use the same overlap sequence: add the new hash while retaining the old hash, roll Gateway, verify, then remove the old hash.

## Dependency contracts

### Platform ticket redeem

```text
POST /internal/v1/game-auth/tickets/redeem
Authorization: Bearer <platform service credential>
```

### Platform login context

```text
GET /internal/v1/game-auth/accounts/{canaryAccountId}/login-context
Authorization: Bearer <platform service credential>
```

The Phase 4 Platform endpoint is intentionally single-world-ready and fails closed when zero or more than one eligible world exists because persistent character-to-world ownership is not yet defined for true multiworld.

### Game Session issuer

```text
POST /internal/v1/game-sessions
Authorization: Bearer <session service credential>
```

Request semantics:

```json
{
  "protocol_version": 1,
  "canary_account_id": 1001,
  "world_id": 1,
  "login_attempt_id": "server-generated-id"
}
```

Response semantics:

```json
{
  "protocol_version": 1,
  "session": {
    "credential": "<opaque-game-session-secret>",
    "expires_at": "2026-07-22T08:30:00Z"
  }
}
```

The concrete Canary-compatible Session Issuer is delivered by Canary PR #722. Its bounded OTClient -> Gateway -> Canary E2E is proven, but production activation still requires exact private/TLS routing, injected credential rotation and production-like re-verification against the deployed revisions.

## Response caching

`POST /v1/login` returns opaque Game Session material and therefore sets `Cache-Control: no-store, no-cache, must-revalidate, private`, `Pragma: no-cache` and `Expires: 0` on success and bounded failure responses.

## Logging

Structured logs contain bounded request metadata only:

- request ID;
- HTTP method;
- path;
- status;
- duration.

The service does not log request/response bodies, headers, Game Login Tickets, service credentials or Game Session secrets.

## Local validation

```bash
gofmt -w .
go test ./...
go vet ./...
go build ./cmd/game-gateway
```

The repository workflow `Game Gateway CI` performs formatting, tests, vet and build independently from the Laravel CI.
