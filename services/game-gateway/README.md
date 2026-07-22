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

Phase 4 implements this adapter boundary only. A concrete Canary-compatible Session Issuer is selected and proven in Phase 6. Until then, successful full world-entry E2E is not claimed.

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
