# ADR 0003 — Defer payments and keep them modular

- Status: Accepted
- Date: 2026-07-18

## Context

The initial Oteryn Platform goal is to replace MyAAC for website, accounts, identity, game-data pages, CMS and administration. Payment processing adds substantial security, financial consistency, provider integration, webhook, fraud and reconciliation complexity.

The owner explicitly intends to add payments later rather than require them for the initial platform.

## Decision

Do not include payment processing, coins or shop fulfillment in the initial platform implementation phases.

Design core modules so `Identity`, basic `Accounts`, CMS and public game data have no dependency on a payment provider.

When payments are introduced, create a dedicated module with its own ADR, threat model and integration contracts.

## Future requirements

The future module must address at minimum:

- provider-hosted payment flows where practical;
- webhook authenticity;
- idempotency and replay protection;
- immutable transaction ledger;
- concurrency-safe balances;
- reconciliation;
- refunds and chargebacks;
- fulfillment contract;
- administrator permissions and audit;
- dedicated security tests.

## Consequences

### Positive

- smaller and safer initial scope;
- identity/account design is not coupled to a vendor;
- production launch can occur without financial processing risk;
- payments can be reviewed as a separate security boundary.

### Negative

- shop monetization is unavailable until a later phase;
- future integration still requires deliberate account/character fulfillment design.
