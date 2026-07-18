# Oteryn Platform Test Strategy

## Goal

Make security, business invariants and Canary/login-server compatibility verifiable rather than dependent on manual assumptions.

## Test layers

### Unit tests

Use for pure domain/application logic:

- validation rules not coupled to HTTP;
- permission decisions with deterministic inputs;
- value objects and transformations;
- token/expiry policy helpers;
- future ledger calculations.

### Feature/HTTP tests

Use Laravel feature tests for:

- routes and middleware;
- authentication and logout;
- CSRF-relevant browser flows;
- authorization policies;
- validation/error behavior;
- rate limiting;
- admin access boundaries;
- CMS/public pages.

### Database integration tests

Use an isolated test database for:

- migrations;
- transactions;
- row locking/atomic mutations;
- uniqueness constraints;
- account/character integration adapters.

Never point automated tests at production data.

### Contract tests

Required for shared Canary/login-server assumptions.

Contract tests should verify only evidence-backed schemas/interfaces and fail visibly when incompatible changes occur.

Examples:

- required shared columns/types exist;
- read queries return expected shapes;
- approved character/account mutations preserve invariants;
- auth/session token behavior matches documented contract.

### End-to-end tests

Critical flows to automate when the components exist:

- registration -> verification -> login;
- password reset -> old session revocation;
- admin login -> MFA -> authorized action;
- web account -> game login;
- password change -> game-login/session behavior;
- banned/disabled account rejection;
- token expiry/replay rejection;
- character creation -> visible/usable in Canary.

## Security regression tests

Every confirmed vulnerability fix should add a focused regression test where practical.

Priority areas:

- IDOR/account ownership;
- privilege escalation;
- CSRF;
- XSS/sanitization;
- SQL injection/query safety;
- session fixation/revocation;
- password reset enumeration/replay;
- MFA bypass;
- rate-limit bypass;
- shared-data race conditions;
- future webhook/payment replay.

## Test data

- use factories/seeders designed for test environments;
- never copy production dumps into CI;
- fixtures must not contain real emails, tokens, credentials or personal data;
- cross-repository fixtures should include the schema/version evidence they represent.

## CI direction

Phase 1 should establish CI that at minimum:

1. installs pinned PHP/Composer dependencies;
2. validates dependency lock consistency;
3. runs syntax/lint/format/static checks selected by the bootstrap task;
4. runs unit/feature tests;
5. runs isolated migration/database tests where configured.

Later workflows may add:

- dependency vulnerability scanning;
- contract tests against pinned schema fixtures;
- browser/E2E tests;
- deployment image/build verification.

## Merge expectations

- security-critical changes require relevant regression tests;
- shared data changes require contract/integration evidence;
- do not merge on tests from an old commit when current head changed;
- do not weaken or delete failing tests to make CI green;
- document exact unavailable environments rather than claiming tests passed.

## Production readiness E2E matrix

Before first production-ready claim, verify at minimum:

| Flow | Required |
|---|---|
| Registration | Yes if registration is enabled |
| Web login/logout | Yes |
| Password change | Yes |
| Password reset | Yes |
| Session revocation | Yes |
| Admin MFA | Yes |
| Authorization denial | Yes |
| Canary/game login | Yes |
| Ban/disabled state | Yes |
| Auth token/session expiry | Yes |
| Replay attempt | Yes where tokenized flow exists |
| Backup restore | Operational test |

Results should be tied to exact deployed versions/commit SHAs where practical.
