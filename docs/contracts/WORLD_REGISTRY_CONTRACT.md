# Oteryn World Registry Contract

## Status

`TARGET CONTRACT — SINGLE-WORLD MVP / MULTIWORLD-READY`

This contract defines the authoritative world-routing model consumed by Oteryn Game Gateway.

The first implementation may contain exactly one world, but no API/domain contract may permanently assume a singleton.

## Ownership

Oteryn Platform logically owns World Registry configuration and policy.

Game Gateway consumes World Registry through a narrow interface.

Canary remains the runtime owner of each game world/process.

OTClient consumes only the sanitized routing data returned by Gateway.

## Goals

- one authoritative source for world names, regions and login routes;
- support one world immediately without singleton coupling;
- future multiworld/multiregion expansion without changing the login architecture;
- world-scoped Game Session semantics;
- explicit maintenance/login availability;
- no client-controlled game endpoint routing;
- future channel allocation can extend, not replace, the model.

## Non-goals

First release does not implement:

- gameplay-state synchronization across Canary instances;
- channel allocation;
- dynamic autoscaling;
- queues/capacity balancing;
- tournament enrollment policy;
- production DNS/load-balancer management.

## World identity

Each world has a stable internal identifier and stable slug.

Minimum logical record:

```text
world_id
slug
name
region
status
login_enabled
game_host
game_port
```

Recommended semantics:

```text
world_id      immutable numeric/UUID-style primary identity selected by implementation
slug          immutable or carefully migrated stable machine identifier
name          player-facing display name
region        normalized region code such as EU / NA
status        operational presentation state
login_enabled authoritative gate for issuing/routing new Game Sessions
game_host     authoritative client routing hostname
 game_port     authoritative client routing port
```

Whitespace before `game_port` above is presentation only; actual schema field is `game_port`.

## Initial world example

Illustrative configuration:

```yaml
world_id: 1
slug: oteryn-eu
name: Oteryn
region: EU
status: online
login_enabled: true
game_host: game-eu.oteryn.com
game_port: 7172
```

Hostnames/ports are examples until deployment configuration is directly verified.

No production endpoint should be inferred from this document.

## Status vocabulary

Initial normalized values:

```text
online
maintenance
offline
unknown
```

Semantics:

- `online`: registry expects the world to accept service, subject to real runtime reachability;
- `maintenance`: intentionally unavailable/degraded for player entry;
- `offline`: intentionally unavailable;
- `unknown`: authoritative runtime status cannot be determined.

`status` is presentation/operational state.

`login_enabled` is the explicit authorization/routing gate for new Game Session creation.

A world with `login_enabled=false` must not receive a new Game Session even if `status=online` due to stale/inconsistent status data.

## Fail-closed rule

For new login routing:

```text
missing world -> deny
unknown authorization -> deny
login_enabled = false -> deny
invalid/missing route -> deny
```

`status=unknown` policy:

- default: deny new session creation unless an explicit product/operations policy says registry status is advisory and another authoritative health source permits entry;
- first implementation should prefer fail closed.

The exact operational-health integration can be added later without changing world identity.

## World authorization

Gateway returns only worlds the redeemed account may access.

Logical interface:

```text
ListAuthorizedWorlds(canary_account_id) -> []World
```

First single-world MVP may implement:

```text
all supported ready accounts -> Oteryn world
```

but the interface remains account-aware so future policies can support:

- test/preview access;
- tournament eligibility;
- region/product restrictions;
- maintenance allowlists;
- staff/internal worlds.

Client input cannot grant world access.

## Character association

Every Gateway-returned character must resolve to a World Registry world.

Logical character projection:

```text
character_name
world_id
```

For a single shared Canary database where existing `players` records do not encode world identity, the MVP may derive all current characters as belonging to the one configured world.

That is an explicit single-world adapter behavior, not a permanent claim that `players` intrinsically stores `world_id`.

Before multiworld implementation, the persistence/ownership model for character-to-world association must be explicitly contracted.

## Game Session world binding

Every target Game Session is logically bound to one `world_id`.

Gateway must create/return routing only for the bound world.

OTClient cannot transform a session into authorization for another world by editing host/port.

The exact enforcement mechanism depends on the selected Game Session↔Canary adapter and is currently an implementation gate.

## Gateway response projection

Sanitized public projection:

```json
{
  "id": 1,
  "slug": "oteryn-eu",
  "name": "Oteryn",
  "region": "EU",
  "status": "online",
  "host": "game-eu.oteryn.com",
  "port": 7172
}
```

Do not expose:

- private/internal hostnames when separate from public route;
- database connection data;
- management ports;
- health-check secrets;
- Canary service credentials;
- private channel/process identifiers not required by client.

## Registry storage

Accepted first implementation options:

### Option A — Platform database

Advantages:

- durable normalized model;
- future admin/audit workflow possible;
- easy multiworld expansion.

Requirements:

- migration is backward compatible/reversible;
- writes are Platform-owned;
- no direct Gateway generic DB access if a narrow API/repository boundary is preferred by deployment architecture.

### Option B — trusted static configuration

Acceptable for initial one-world deployment if:

- configuration is versioned/deployed safely;
- runtime validation fails closed on malformed/missing route;
- Gateway can consume it without hard-coded singleton logic;
- later database migration has an explicit plan.

ADR 0009 selects World Registry as a domain boundary, not a mandatory storage engine in Phase 0.

Phase 1 must select storage based on existing Platform conventions and deployment needs.

## Route validation

At configuration/load time validate:

- `slug` non-empty and normalized;
- `name` non-empty;
- `region` from accepted vocabulary or validated normalized code;
- `status` from allowed enum;
- `login_enabled` boolean;
- `game_host` syntactically valid expected hostname/IP form;
- `game_port` integer in valid TCP port range;
- uniqueness of `world_id` and `slug`.

Do not allow arbitrary URL schemes/paths where only host+port are expected.

## Trust boundary

OTClient is not authoritative for:

- world ID;
- world availability;
- host;
- port;
- region;
- access policy.

Game Gateway uses Registry values, not client-provided routing.

A future admin UI editing worlds is a privileged operation requiring:

- explicit RBAC permission;
- confirmed MFA under existing admin policy;
- validation;
- audit event;
- safe handling of endpoint changes.

Admin editing is not required for the MVP.

## Availability semantics

Registry availability and Canary reachability are distinct.

```text
Registry login_enabled=true
```

means Gateway may issue/rout a new session according to registry policy.

It does not guarantee the Canary endpoint will be reachable milliseconds later.

A future health/availability subsystem may feed `status` or an additional readiness field.

Do not infer authoritative world outage solely from a single failed client connection.

## Multiworld evolution

Future example:

```text
World Registry
├── oteryn-eu
├── oteryn-na
├── tournament
├── test
└── preview
```

Required before enabling multiple worlds:

- character-to-world ownership/persistence contract;
- account/world authorization policy;
- world-scoped Game Session enforcement;
- separate routing endpoints;
- maintenance behavior;
- cross-world character name semantics if databases are separated;
- exact Canary database/runtime topology;
- E2E per world.

## Multiregion evolution

`region` is metadata/policy, not automatic network routing.

Future multiregion may add:

```text
login_ingress_region
preferred_region
latency/capacity policy
region-specific Gateway endpoints
```

The first release must not make client geolocation or latency measurements authoritative for access control.

## Future channel extension

Future shape may add a routing result:

```text
world_id
channel_id
game_host
game_port
```

World remains the persistent player-facing logical world.

Channel is an allocated runtime subdivision/instance.

Channel allocation must not be implemented by overloading `world_id`.

Authentication remains:

```text
Identity -> Ticket -> Gateway -> world authorization -> optional future channel allocation -> Game Session
```

Gameplay-state synchronization between channels is outside this contract.

## Maintenance behavior

Recommended transition:

```text
login_enabled=false
status=maintenance
```

Effects:

- no new Game Sessions;
- Gateway does not return the world as login-available;
- existing player connections are governed by separate operational/Canary policy;
- no automatic forced disconnect is implied by Registry alone.

## Endpoint changes

Changing `game_host`/`game_port` affects new routing responses.

The system must define behavior for already issued Game Sessions if endpoint changes during their lifetime.

Recommended safe operational sequence:

1. disable new login for world;
2. wait/revoke outstanding short-lived entry sessions as policy requires;
3. change route;
4. validate target readiness;
5. re-enable login.

Exact deployment automation is out of Phase 0 scope.

## Required MVP tests

- one world loads successfully through Registry abstraction;
- missing registry record fails closed;
- duplicate slug/ID rejected by persistence/config validation;
- invalid host/port rejected;
- `login_enabled=false` prevents Game Session creation;
- unauthorized account/world combination omitted/denied;
- Gateway response uses registry route rather than client input;
- character references resolvable world;
- single-world adapter assigns current characters to configured world without claiming intrinsic `players.world_id`;
- registry data projection does not expose private configuration fields.

## Required multiworld tests before Phase 9 completion

- account sees only authorized worlds;
- character lists preserve correct world association;
- session for world A cannot be used for world B according to selected Canary adapter;
- maintenance world denies new sessions;
- one world outage does not corrupt other world routing;
- region/routing fields returned correctly;
- duplicate/ambiguous character/world mapping fails closed;
- dynamic endpoint routing uses exact selected world.

## Versioning

Initial registry/API projection is part of game auth:

```text
protocol_version = 1
```

Additive optional fields may be backward compatible.

Changing the meaning of `world_id`, session world binding, or routing authority is a breaking contract change.

## Remaining unknowns

1. Phase 1 storage choice: Platform DB vs trusted configuration for the first one-world implementation.
2. Exact production Oteryn world public hostname/port.
3. Character-to-world persistence model for true multiworld.
4. Exact Game Session world-scope enforcement in Canary.
5. Future runtime health source and how it affects `status` vs `login_enabled`.
6. Future admin/world-management surface.

These are not blockers for defining the single-world-ready domain boundary, but must be resolved before the corresponding functionality is implemented or claimed.
