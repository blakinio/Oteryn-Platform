# Oteryn Web-to-Game Authentication Contract

## Status

`DISCOVERY REQUIRED`

This document defines the required contract between Oteryn Platform identity/account security and the component(s) responsible for authenticating a player into the game.

The exact implementation is not yet proven. Do not assume that Laravel, login-server or Canary currently owns a particular step until verified.

## Objective

A security rule must not be enforceable only on the website while an alternate game-login path bypasses it.

Target outcome:

- one authoritative account/credential policy;
- explicit session/token semantics;
- predictable revocation;
- no undocumented fallback password path that bypasses stronger controls.

## Actors/components

Potential components:

- Oteryn Platform `Identity` module;
- Oteryn account data store;
- login-server component;
- Canary game server;
- official/custom Tibia client.

Actual responsibilities must be discovered.

## Required discovery

Prove and document:

1. Which component receives the game client's initial authentication request?
2. Which component validates the password or token?
3. What password hash format(s) are accepted?
4. Which database fields are read during authentication?
5. Are game sessions/tokens stored? Where?
6. Are tokens single-use or replayable?
7. What is their TTL?
8. Which component revokes them?
9. Does password change/reset invalidate active/pending game sessions?
10. How are account bans/disabled state enforced?
11. Is email verification intended to gate game login?
12. Is MFA intended to gate game login, website login only, or selected sensitive operations?
13. Is there a direct Canary password login path that can bypass login-server policy?
14. What happens during login-server/platform outage?

## Target security invariants

The final contract should satisfy, where product policy requires:

- credentials have one authoritative verification/migration strategy;
- password reset/change invalidates credentials/sessions according to explicit policy;
- disabled/banned accounts cannot use an alternate login path;
- temporary game-login tokens are cryptographically random and narrowly scoped;
- token replay is prevented when single-use semantics are intended;
- token TTL is bounded;
- tokens are never logged in plaintext;
- authentication rate limits exist at every externally reachable brute-force path;
- session/token validation is atomic where replay prevention requires it;
- no component trusts client-supplied account identity without cryptographic/session proof.

## Candidate target flow

This is `DERIVED DESIGN DIRECTION`, not current proven behavior:

```text
Client / Browser
      |
      v
Oteryn Platform Identity
(password + security policy)
      |
      v
short-lived game-login authorization/session contract
      |
      v
login-server
      |
      v
Canary
```

An alternate design may be accepted if discovery proves a safer or more compatible model. Any final choice requires an ADR.

## Password migration rule

Do not switch the web account store to a new hashing format until game-login compatibility is proven.

A migration design must define:

- legacy format detection;
- verification authority;
- upgrade-on-login behavior if used;
- compatibility window;
- rollback;
- effect on game login;
- effect on existing sessions.

## Revocation matrix to complete

| Event | Web sessions | Remember-me | Pending game tokens | Active game sessions | Notes |
|---|---|---|---|---|---|
| Password change | UNKNOWN | UNKNOWN | UNKNOWN | UNKNOWN | Define policy |
| Password reset | UNKNOWN | UNKNOWN | UNKNOWN | UNKNOWN | Define policy |
| MFA reset | UNKNOWN | UNKNOWN | UNKNOWN | UNKNOWN | Define policy |
| Account banned/disabled | UNKNOWN | UNKNOWN | UNKNOWN | UNKNOWN | Must prevent bypass |
| Email changed | UNKNOWN | UNKNOWN | UNKNOWN | UNKNOWN | Define based on verification policy |

## Verification requirement

Before calling authentication production-ready, run an end-to-end test matrix against the actual deployed components covering:

- valid login;
- wrong password;
- rate limit;
- password change;
- password reset;
- session revocation;
- banned/disabled account;
- expired token/session;
- replay attempt;
- direct alternate login path;
- component outage/failure behavior.

Evidence must include exact component versions/commit SHAs where possible.
