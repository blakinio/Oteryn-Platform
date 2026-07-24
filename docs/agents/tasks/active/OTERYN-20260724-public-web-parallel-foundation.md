# OTERYN-20260724-public-web-parallel-foundation

## Status

ACTIVE

## Program

Oteryn Public Website Expansion

## Goal

Deliver Slice 1 from `docs/architecture/PUBLIC_WEBSITE_EXPANSION_PLAN.md`: promote the approved homepage to `/`, establish the responsive public shell, consume existing CMS and PublicGameData boundaries, and create minimal module-local route and permission registration conventions for parallel feature agents.

## Repository scope

- Writable: `blakinio/Oteryn-Platform`
- Read-only: every other repository

## Ownership

This task owns only the shared integration paths required by the public shell and future parallel modules:

- central public route bootstrap and module route loading;
- shared public layout, navigation, footer and shell CSS/components;
- production homepage controller/query/view model/view;
- minimal permission registration extension mechanism or central reservation of planned permission keys;
- focused homepage/public-shell tests;
- this task record.

It does not own Downloads, Events, Support, Wiki domain implementation, new PublicGameData queries, media upload, commerce, or cross-repository changes.

## Preconditions

- PR #142 merged into `main`.
- PR #143 merged into `main`.
- `docs/architecture/PUBLIC_WEBSITE_EXPANSION_PLAN.md` exists on `main`.
- `docs/architecture/WIKI_IMPLEMENTATION_PLAN.md` exists on `main`.

## Checkpoint

- Branch: `feat/OTERYN-20260724-public-web-parallel-foundation`
- Draft PR: pending first commit
- Current phase: lean preflight and repository inspection
- Validation: not started

## Parallel path ownership contract

Pending final implementation. The final checkpoint will identify the exact shared files owned here and the module-local paths reserved for Downloads, Events, Support, Wiki and future PublicGameData agents.
