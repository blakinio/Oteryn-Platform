# Canary Data Integration Contract

## Status

`DISCOVERY REQUIRED`

This document prevents Oteryn Platform from silently depending on generic TFS/MyAAC database assumptions. It becomes authoritative only as fields are verified against the actual `blakinio/canary` schema/source and validated integration behavior.

## Scope

Define how Oteryn Platform may read or mutate game-related data owned by or shared with Canary.

## Current proven facts

- Canary is a separate repository/component from Oteryn Platform.
- Oteryn Platform must not silently modify Canary repository code or schema.
- Shared database behavior is an explicit cross-repository contract.

## Current unknowns

The following are intentionally `UNKNOWN` until a bounded discovery task verifies them:

- actual account table/model and columns;
- actual player/character table/model and required columns;
- guild schema relevant to public pages;
- account status/ban fields;
- character deletion semantics;
- online-character detection mechanism;
- world/server identifiers and multi-world model;
- which fields are safe for web writes;
- caches/runtime invariants affected by direct DB mutation.

## Contract sections to complete during discovery

### Accounts

Document:

- owner;
- table/model;
- primary key;
- public/account-profile fields;
- security/status fields;
- allowed platform reads;
- allowed platform writes;
- transaction requirements.

### Characters

Document:

- owner;
- table/model;
- ownership relationship to account;
- name normalization/uniqueness;
- vocation/sex/town/world constraints;
- create defaults and dependent rows;
- deletion/soft-deletion semantics;
- online restrictions;
- allowed web mutations.

### Guilds

Document:

- read tables/models;
- membership/leadership relationships;
- public fields;
- whether any web mutation will be supported.

### Highscores

Document:

- source fields;
- filtering rules;
- ordering/precision rules;
- excluded/deleted/hidden players;
- query/index expectations.

### Online/status

Document:

- authoritative source;
- freshness expectations;
- failure behavior when Canary/status source is unavailable.

## Shared write template

Every approved shared write operation must add a section with:

```text
Operation:
Primary owner:
Caller:
Tables/models/fields:
Preconditions:
Authorization:
Validation:
Transaction boundary:
Locking/concurrency:
Runtime/cache side effects:
Failure/rollback behavior:
Compatibility version/evidence:
Tests:
```

## Safety rules

- No agent may copy MyAAC SQL and call it compatible without verification.
- No agent may add a migration that alters Canary-owned schema without explicit cross-repository coordination.
- Public read features should be isolated from privileged write models/services.
- Character/account mutation work is blocked until its required contract section is proven.
- Schema drift must fail visibly rather than silently corrupting data.

## Discovery task recommendation

Create a dedicated task that reads the actual Oteryn Canary migration/schema/model source and produces evidence-backed sections in this document before Phase 3/5 implementation uses shared data.
