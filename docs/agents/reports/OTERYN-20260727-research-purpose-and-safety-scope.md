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

## Explicit non-goals

This work is not intended to:

- cheat in Tibia or any other game;
- obtain an unfair gameplay advantage;
- automate gameplay against third-party services;
- bypass, disable, patch, hook, impersonate or evade BattlEye or another anti-cheat mechanism;
- access accounts, sessions, characters or infrastructure without authorization;
- redistribute proprietary CipSoft binaries, assets, credentials, session data or reusable bypass material;
- exploit or interfere with official game services.

## Operating rule

Prefer the project-owned Canary server, local mocks and disposable local fixtures for protocol development and validation. Treat third-party live-service behavior as external evidence only, keep unknowns explicit, and stop any test at the first decisive result.

Any future packet decoding, map reconstruction or UI-state work must be framed as compatibility work for the Oteryn client against Oteryn/Canary, not as tooling for third-party gameplay manipulation.
