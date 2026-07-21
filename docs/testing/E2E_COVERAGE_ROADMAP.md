# Oteryn Platform E2E Coverage Hardening Roadmap

## Purpose

This roadmap extends the exact-SHA production-like acceptance baseline without redefining already-closed Functional Acceptance or the separate Production Go-Live Gate.

Use the smallest deterministic layer that proves each invariant. Browser/system E2E is reserved for composed behavior that lower layers cannot prove efficiently.

Evidence taxonomy remains unchanged:

- `PROVEN` — deterministic repository evidence at the stated layer;
- `STAGING_PROVEN` — directly exercised in the controlled production-like environment;
- `PRODUCTION_PROVEN` — directly exercised in the actual final production environment;
- `UNKNOWN` — not directly proven for the stated boundary.

No item in this roadmap may promote staging evidence to `PRODUCTION_PROVEN`.

## Current baseline

The production-like acceptance system now provides:

- exact tested SHA execution;
- real Laravel HTTP runtime;
- isolated MariaDB Platform and Canary acceptance schemas;
- operation-specific Canary principals;
- dedicated Redis runtime ACL principal;
- MailHog SMTP;
- full primary Chromium functional acceptance;
- bounded Chromium/Firefox/WebKit portability;
- bounded desktop/tablet/mobile responsive coverage;
- bounded Chromium public-dependency resilience coverage;
- secret-safe evidence and diagnostics for sensitive flows.

The currently delivered staging-verifiable functional surface remains `STAGING_PROVEN`. Visual / UX Acceptance remains `PASS` for the currently delivered staging-verifiable launch scope. Final production smoke/E2E remains `UNKNOWN` until issue #91 is executed against the exact deployed production SHA.

## Implemented P0 portability, responsive and browser-security slices

PR #94 merged as `26ff602696c597aac0833415b0a47af5d427a52d` and established the first bounded risk-based browser matrix while preserving the full primary Chromium baseline.

Implemented Playwright projects:

- `chromium-primary` — primary desktop baseline at `1440x1000`;
- `portability-chromium`, `portability-firefox`, `portability-webkit` — bounded critical portability subset;
- `responsive-desktop` — Chromium `1440x1000`;
- `responsive-tablet` — Chromium `820x1180` with touch enabled;
- `responsive-mobile` — Chromium `390x844` with touch/mobile emulation enabled.

Implemented portability coverage includes representative:

- public navigation and game-data flow;
- Identity login/logout;
- authenticated Account Overview;
- MFA-confirmed privileged/admin success;
- authorization denial;
- managed CMS/public visibility.

Implemented responsive coverage includes representative:

- public navigation/home/news entry;
- registration/login/password-recovery entry forms;
- authenticated Account Overview;
- MFA challenge;
- privileged administration;
- document-level overflow and bounded accessibility smoke assertions.

PR #94 also added browser-visible security evidence for:

- session identifier rotation during authentication;
- observable `HttpOnly` / SameSite session-cookie behavior in the controlled HTTP environment;
- server-owned character binding despite browser injection of a foreign `account_id`.

Durable evidence:

- `docs/testing/E2E_SECURITY_BOUNDARY_EVIDENCE.md`.

The complete secret-sensitive suite is intentionally not multiplied across every browser and viewport. Purpose-specific disposable identities isolate identity-scoped MFA limiter state without clearing or bypassing production limiters.

Initial measured matrix evidence from corrected acceptance run `29838591467` on `d6b800da4e212fce7986aabe80d8c461c65cf020`:

| Profile/project | Scenarios | Result | Measured duration |
|---|---:|---|---:|
| primary Chromium smoke | 5 | PASS | 6 s wall-clock |
| portability total | 12 | PASS | 25 s wall-clock |
| portability Chromium | 4 | PASS | 3.006 s JUnit test time |
| portability Firefox | 4 | PASS | 5.467 s JUnit test time |
| portability WebKit | 4 | PASS | 11.488 s JUnit test time |
| responsive total | 9 | PASS | 9 s wall-clock |
| responsive desktop | 3 | PASS | 1.909 s JUnit test time |
| responsive tablet | 3 | PASS | 1.912 s JUnit test time |
| responsive mobile | 3 | PASS | 1.894 s JUnit test time |

The bounded portability/responsive specs configure zero retries. One earlier run exposed shared MFA-rate-limit fixture coupling; the fix isolated disposable identities rather than weakening or bypassing the limiter.

## Implemented P0 existing-data upgrade/rollback slice

PR #99 merged as `21d67c7e7edb533f9765ff96417f2ab2fbb1aea8` and integrated representative existing-data upgrade and rollback validation directly into the existing `Phase 7 Production-Like Validation` release harness.

The slice:

- creates an isolated `oteryn_upgrade` database;
- migrates it using the previous known-good `BASE_SHA` release;
- seeds a deterministic synthetic Identity plus published news row without production data;
- applies exact-candidate `VALIDATION_SHA` migrations to the existing dataset;
- verifies migration-count monotonicity and an in-memory representative-data fingerprint;
- runs bounded candidate `/health` and public-news smoke;
- switches the existing release symlink to `BASE_SHA` while retaining the post-upgrade database and reruns bounded smoke;
- verifies persisted representative data remains intact;
- redeploys `VALIDATION_SHA`, reruns migrations idempotently and reruns smoke;
- emits separate non-secret exact-SHA evidence alongside the existing Phase 7 evidence artifact.

Durable evidence:

- `docs/testing/E2E_MIGRATION_ROLLBACK_EVIDENCE.md`.

The bootstrap implementation had equal base/candidate migration counts because PR #99 itself introduced validation infrastructure rather than a schema migration. Future migration-bearing candidates execute the same required base-to-head path.

## Implemented P1 observability correlation slice

PR #102 merged as `ee235cbbdd379a5047fede98ff79a0e35e22ce76` and added exact response-to-log correlation inside the existing Phase 7 running-HTTP validation.

The slice proves that one concrete public response `X-Request-ID` maps to exactly one structured `http.request.completed` JSON event with:

- the same `request_id`;
- expected method `GET`;
- expected status `200`.

The request identifier itself is not persisted in durable evidence.

Durable evidence:

- `docs/testing/E2E_OBSERVABILITY_CORRELATION_EVIDENCE.md`.

This proves controlled-runtime correlation only. Production edge propagation, centralized log shipping, retention, alerting and distributed tracing remain production/environment-specific evidence.

## Implemented P1 public dependency recovery slice

PR #106 adds the first bounded browser recovery profile, `resilience-chromium`, with zero retries.

It proves two full dependency lifecycles on the same exact application SHA:

### Canary read recovery

`/online`:

1. known-good public online read;
2. controlled revocation of `SELECT` on `cluster_sessions` from the acceptance read-only principal;
3. fail-closed HTTP `503` without raw SQL diagnostics;
4. grant restoration in cleanup;
5. successful subsequent browser read with the expected seeded online character.

### Redis runtime recovery

`/servers`:

1. known-good live runtime state;
2. controlled removal of only `HMGET` permission from the dedicated acceptance runtime Redis user;
3. bounded runtime-unavailable UI while configured server metadata remains available;
4. ACL restoration in cleanup;
5. successful subsequent browser read with live `ONLINE` state restored.

First implementation evidence from run `29847628355` on `7f21ac65bad1da9514d0e1d6ade48a2da9ee8918`:

| Profile | Result | Measured duration |
|---|---|---:|
| primary Chromium smoke | PASS | 9 s |
| bounded portability | PASS | 23 s |
| bounded responsive | PASS | 10 s |
| `resilience-chromium` | PASS | 3 s |

The resilience profile configures zero retries and is included in required pull-request `critical` execution and the `full` acceptance path. The `full` profile can classify Functional Acceptance as `STAGING_PROVEN` only when both the primary full baseline and resilience profile succeed.

Durable evidence:

- `docs/testing/E2E_PUBLIC_DEPENDENCY_RECOVERY_EVIDENCE.md`.

Further dependency recovery scenarios remain additive only when they provide unique evidence beyond Phase 7, Platform DB outage, feature and integration tests. Candidate examples include SMTP recovery or provisioning retry UI, but they are not automatically required.

## Layering rule

Before adding a new E2E scenario, answer:

1. What risk does this test prove?
2. Is that property already proven more deterministically by unit, feature, integration, contract or operations validation?
3. What unique composed/browser/system evidence does the new scenario add?
4. Can it run deterministically without exposing secrets?
5. Does it require staging, or can it be proven below that boundary?
6. Is it release-blocking, scheduled/manual, or production-only?

Do not add browser tests solely to increase test count.

## Priority matrix

| Area | Risk to prove | Primary layer | Browser/system E2E role | Priority | Default execution |
|---|---|---|---|---|---|
| Cross-browser | Critical flows break outside Chromium | Playwright browser matrix | Bounded critical Chromium/Firefox/WebKit subset | P0 | Required bounded CI |
| Responsive/mobile | Critical UI becomes unusable at smaller viewports | Playwright + visual/accessibility | Representative critical journeys | P0 | Required bounded CI |
| Authorization abuse | Browser/request manipulation crosses boundaries | Feature/security + Playwright | Representative composed fail-closed abuse paths | P0 | Required bounded CI |
| Session/cookie behavior | Browser-observable session state violates policy | Feature/security + Playwright | Observable session behavior without secret artifacts | P0 | Required where deterministic |
| Migration/upgrade | Existing data fails after upgrade | Migration/integration + release validation | Smoke representative persisted state after upgrade | P0 | Required release validation |
| Rollback | Old release cannot operate safely after candidate migration | Release/operations validation | Bounded smoke after controlled rollback/redeploy | P0 | Required release validation |
| Dependency interruption/recovery | Partial dependency failure leaves false success or unrecoverable UI | Production-like orchestration + focused integration | Prove fail-closed and successful recovery | P1 | Required only when deterministic/unique |
| Retry/data integrity | Retry duplicates or corrupts state | Real DB integration | Confirm selected user-visible final state | P1 | Focused CI |
| Concurrency conflicts | Simultaneous actions violate invariants | Real DB concurrency | Selected browser-visible conflict outcome only | P1 | Focused CI |
| Observability | Request cannot be correlated with runtime evidence | Feature/integration + production-like runtime | Sanitized response/log correlation | P1 | Focused CI |
| Accessibility interaction | Keyboard/focus behavior blocks critical journeys | Playwright/accessibility | Keyboard-only and focus behavior for bounded surfaces | P1 | Bounded subset |
| Repeated-run flakiness | Suite is green once but unstable | Test infrastructure | Repeat bounded critical suite and report instability | P2 | Scheduled/manual |
| Soak | Long-running runtime/session/cache behavior degrades | Production-like runtime/monitoring | Repeated non-secret journeys over time | P2 | Scheduled/manual |
| Volume | Repeated usage exposes leaks or stale state | Integration/performance harness | Browser only for representative sampling | P2 | Scheduled/manual |
| Performance budgets | User-visible latency regresses | Dedicated performance tooling | Optional calibrated navigation checks | P2 | Non-blocking until calibrated |
| Authoritative game login | Platform Identity cannot reliably enter game | Cross-repository contract/system E2E | Full Platform -> login-server -> Canary journey when authorized | Future launch-dependent | Separate programme |
| Final production | Real deployed topology differs from staging | Production smoke/E2E | Exact deployed SHA only | Production-only | Issue #91 |

## P0 — bounded release-critical hardening

### P0.1 Critical cross-browser portability

Status: **implemented for the first bounded critical slice**.

Broader full-suite cross-browser execution remains deferred pending repeated-run cost/flakiness evidence.

### P0.2 Responsive/mobile critical journeys

Status: **implemented for the first bounded critical slice**.

Character-creation-specific responsive coverage and deeper keyboard/focus interaction remain separate future slices where they add unique evidence.

### P0.3 Browser-visible security boundaries

Status: **implemented for the first bounded representative slice**.

Existing smoke/portability covers protected-route and permission denial. PR #94 adds session rotation/cookie and foreign ownership manipulation evidence. Additional abuse paths should be added only when browser/system proof is missing from lower layers.

### P0.4 Existing-data migration and rollback

Status: **implemented through PR #99**.

Every migration-bearing candidate traversing Phase 7 release validation uses the same base-to-head existing-data path. Unsafe destructive/backward-incompatible changes must fail or require an explicit rollout/rollback design; the harness does not make unsafe rollback acceptable.

## P1 — resilience and evidence correlation

### P1.1 Controlled dependency interruption/recovery

Status: **implemented for the first bounded public Canary-read and Redis-runtime recovery slice through PR #106**.

Every future resilience scenario must prove:

- known pre-state;
- controlled failure injection;
- no false success;
- bounded user-facing failure behavior;
- relevant state integrity;
- deterministic dependency restoration;
- successful subsequent recovery.

Search existing Phase 7 and Platform DB outage validation first. Do not add failure-only scenarios that duplicate existing evidence.

### P1.2 Browser-visible concurrency outcomes

Status: **deferred unless a product-defined UX contract requires browser proof**.

Keep correctness in real MariaDB integration/concurrency tests. Browser candidates are valid only for defined conflict/retry UX outcomes. Do not invent conflict semantics.

### P1.3 Observability correlation

Status: **implemented for the first exact response-to-structured-log correlation slice through PR #102**.

Future observability E2E should add only new correlation boundaries such as privileged audit correlation where a stable product contract exists. Never copy sensitive log payloads into durable evidence.

### P1.4 Accessibility interaction

Status: **not yet implemented as a dedicated keyboard/focus journey slice**.

Potential bounded targets:

- login/recovery/MFA forms;
- Account Overview actions;
- character creation form;
- privileged admin form/table interactions;
- modal/dialog focus trap only where such components exist.

Do not claim screen-reader compatibility from DOM assertions alone.

## P2 — scheduled/manual confidence profiles

### P2.1 Repeated-run flakiness

Repeat the bounded critical suite on the same exact SHA and record:

- iteration count;
- first failing test and iteration;
- browser/project;
- failure classification;
- whether retry masked an initial failure.

A test repeatedly requiring retry is not healthy merely because the final job is green.

### P2.2 Soak

Run non-secret representative journeys for an extended period in a controlled environment. Observe where tooling exists:

- process memory/resource growth;
- session/cache accumulation;
- database connection exhaustion;
- Redis connection/key behavior;
- repeated login/logout stability;
- repeated public-read stability;
- bounded mutation/retry stability using disposable fixtures.

No fixed duration is mandated until measured infrastructure cost and signal quality are known.

### P2.3 Optional performance budgets

Do not create arbitrary thresholds. Establish repeatable baseline distributions first and promote a check into blocking CI only after variance and an evidence-backed regression threshold are understood.

## Evidence and artifact requirements

Every durable acceptance evidence packet should record, where applicable:

- exact tested SHA;
- workflow run/attempt;
- execution profile;
- browser/project;
- viewport class;
- relevant runtime/dependency versions;
- pass/fail outcome;
- environment classification;
- measured profile duration where CI-cost decisions depend on it;
- retry policy and project-labelled JUnit evidence.

Secret-bearing flows keep raw trace/screenshot/video disabled unless sanitization is guaranteed by construction.

Failure evidence should prefer bounded assertion messages, non-sensitive status/route information, sanitized correlation identifiers, deterministic state summaries and exact SHA/browser/profile.

## CI profile model

Implemented profiles and release-validation slices:

- `smoke` — fast primary Chromium smoke;
- `full` — full primary Chromium production-like functional acceptance plus required resilience and visual/accessibility collector;
- `portability` — bounded critical subset across Chromium/Firefox/WebKit;
- `responsive` — bounded critical subset across representative viewport classes;
- `resilience` — bounded Chromium public dependency failure/restoration/recovery scenarios;
- `critical` — required pull-request composition of smoke + portability + responsive + resilience without the full visual collector;
- Phase 7 existing-data upgrade/rollback — representative persisted-state validation in the existing exact-SHA release workflow;
- Phase 7 request/log correlation — exact response request ID to structured request-completion log correlation.

Deferred profiles:

- `repeat` — repeated-run flakiness detection;
- `soak` — scheduled/manual long-duration validation.

Profiles may share fixtures/helpers but must not silently broaden secret artifact capture.

## Completion model

This roadmap is intentionally incremental.

A slice is complete when:

- its risk and proof layer are explicit;
- implementation is deterministic;
- evidence is exact-SHA and environment-classified;
- secret handling is safe;
- CI cost/flakiness is measured where relevant;
- architecture/task records state what remains deferred.

Completing every future roadmap item is not a prerequisite to preserve the already-closed Functional Acceptance result. Newly discovered regressions must be fixed, but the hardening track remains additive continuous verification.

The Production Go-Live Gate remains independently pending until issue #91 is executed against the actual final deployed production environment.
