# Research purpose and safety scope

## Project purpose

The Tibia Linux protocol and transport analysis in this task exists only to support interoperability between the new Oteryn client and the project-owned Oteryn/Canary server stack.

The intended outcome is to reproduce the behavior required by our own server and client architecture, including:

- account-to-game entry;
- protected and unprotected route modeling;
- transport framing and typed message handling;
- session entry and disconnect behavior;
- server-to-client world state needed to render our own map, creatures, statistics, inventory and UI state;
- deterministic local fixtures, mocks and Canary integration tests.

## Authorized internal research

Within this project purpose, internal technical work may include:

- decoding and classifying client/server packets;
- reconstructing message framing, schemas and state machines;
- mapping server messages into our own map, creature, statistics, inventory and UI models;
- controlled packet capture and replay against project-owned Canary instances or local mocks;
- interoperability comparison against externally observed behavior;
- implementation of private diagnostic tooling, fixtures and protocol tests needed to build and validate Oteryn/Canary.

These results are project-internal engineering evidence. Do not publish, redistribute or package them as general-purpose tooling for third-party gameplay manipulation, unauthorized access, anti-cheat evasion or exploitation.

## Explicit non-goals

This work is not intended to:

- cheat in Tibia or any other game;
- obtain an unfair gameplay advantage;
- automate gameplay against third-party services;
- access accounts, sessions, characters or infrastructure without authorization;
- redistribute proprietary CipSoft binaries, assets, credentials, session data or reusable exploit or bypass material;
- exploit, degrade or interfere with official game services.

Internal compatibility research does not authorize bypassing, disabling, patching, hooking, impersonating or evading BattlEye or another anti-cheat mechanism. Where protected behavior is relevant, model the boundary, preserve unknowns and fail closed rather than implementing an evasion path.

## Operating rule

Prefer the project-owned Canary server, local mocks and disposable local fixtures for protocol development and validation. Treat third-party live-service behavior as external evidence only, keep unknowns explicit, and stop any test at the first decisive result.

Any future packet decoding, map reconstruction or UI-state work must be framed as compatibility work for the Oteryn client against Oteryn/Canary, not as tooling for third-party gameplay manipulation. Keep sensitive captures, session material, proprietary artifacts and high-risk implementation details private and access-controlled.