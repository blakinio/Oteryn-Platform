# Oteryn Platform E2E Coverage Hardening Roadmap

## Purpose

This roadmap extends the existing exact-SHA production-like acceptance baseline without redefining already-closed Functional Acceptance or the separate Production Go-Live Gate.

The goal is to increase confidence where browser/system validation adds unique evidence, while keeping transaction races, database integrity and other lower-level invariants in the deterministic layers best suited to prove them.

Evidence taxonomy remains unchanged:

- `PROVEN` — deterministic repository evidence at the stated layer;
- `STAGING_PROVEN` — directly exercised in the controlled production-like environment;
- `PRODUCTION_PROVEN` — directly exercised in the actual final production environment;
- `UNKNOWN` — not directly proven for the stated boundary.

No item in this roadmap may promote staging evidence to `PRODUCTION_PROVEN`.

## Current baseline

At roadmap creation:

- the production-like Playwright acceptance harness executes the exact tested SHA;
- the primary browser is Chromium;
- the primary viewport is `1440x1000`;
- the suite runs serially with one worker;
- raw traces/screenshots/video are disabled by default for secret-bearing flows;
- the harness uses a real Laravel HTTP runtime with isolated MariaDB, Redis and MailHog dependencies;
- the currently delivered staging-verifiable functional surface is `STAGING_PROVEN`;
- Visual / UX Acceptance is `PASS` for the currently delivered staging-verifiable launch scope;
- final production smoke/E2E remains `UNKNOWN` until issue #91 is executed against the exact deployed production SHA.

## Implemented P0 portability/responsive slice

The first bounded P0 portability/responsive slice is implemented in PR #94 while preserving the full primary Chromium production-like acceptance baseline.

Implemented execution profiles:

- `chromium-primary` — existing primary desktop baseline at `1440x1000`;
- `portability-chromium`, `portability-firefox`, `portability-webkit` — bounded critical portability subset only;
- `responsive-desktop` — Chromium `1440x1000`;
- `responsive-tablet` — Chromium `820x1180` with touch enabled;
- `responsive-mobile` — Chromium `390x844` with touch/mobile emulation enabled.

The pull-request `critical` profile executes primary smoke plus the bounded portability and responsive profiles. It does not execute the complete secret-sensitive functional suite or the full visual collector across the browser/viewport cross-product. The `full` profile remains the sole complete primary Chromium production-like functional acceptance and visual/accessibility collection path.

Implemented portability coverage includes:

- public navigation and representative public game-data flow;
- Identity login/logout;
- authenticated Account Overview;
- MFA-confirmed privileged/admin success;
- MFA-confirmed authorization denial for a non-admin identity;
- deterministic managed CMS page administration/public visibility.

Implemented responsive coverage includes:

- public navigation/home/news entry;
- registration/login/password-recovery entry-form usability;
- authenticated Account Overview;
- MFA challenge;
- privileged administration and managed-pages table entry;
- document-level horizontal-overflow and bounded accessibility smoke assertions on tested surfaces.

Purpose-specific disposable regular/admin identities isolate identity-scoped MFA rate-limit state between browser/viewport projects. Production rate limiters are not cleared, bypassed or weakened. Raw Playwright trace, automatic screenshot and video collection remain disabled for these secret-bearing flows.

Measured exact-SHA evidence from acceptance run `29838591467` on `d6b800da4e212fce7986aabe80d8c461c65cf020`:

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

The bounded portability/responsive specs configure zero retries. The corrected exact-head run completed with no failures or retries. This is one exact-head measurement, not proof of long-term flake-free behavior.

An earlier critical run (`29837186868`) exposed deterministic test-state coupling: a reused regular identity consumed the real 5-per-minute identity-scoped MFA enrollment limiter by the sixth enrollment request, causing the WebKit project to receive HTTP 429. The fix was to isolate disposable identities per project and proactively isolate privileged MFA identities as well; no retry, sleep, cache clear or limiter bypass was introduced. Therefore the failure is classified as harness fixture coupling, not a WebKit product incompatibility.

Deferred after this slice:

- broader browser multiplication of the full secret-sensitive acceptance suite;
- repeated-run flakiness/soak evidence beyond the single corrected run;
- remaining P0 browser-visible session/cookie and security-boundary cases not already uniquely proven by this slice;
- representative existing-data migration/rollback browser smoke integrated with Phase 7;
- P1 resilience, concurrency UX, observability correlation and deeper accessibility interaction work.

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
| Cross-browser | Critical flows break outside Chromium | Playwright browser matrix | Run bounded critical subset on Chromium, Firefox, WebKit | P0 | Required CI for bounded subset |
| Responsive/mobile | Critical UI becomes unusable at smaller viewports | Playwright + visual/accessibility collector | Exercise representative critical journeys and layout states | P0 | Required CI, bounded matrix |
| Authorization abuse | Browser/request manipulation crosses account/admin boundaries | Feature/security + Playwright | Prove composed fail-closed outcome for representative attacks | P0 | Required CI |
| Session/cookie behavior | Browser-observable session state violates security policy | Feature/security + Playwright | Verify observable cookie/session behavior without secret artifacts | P0 | Required CI where deterministic |
| Migration/upgrade | Existing data fails after schema/application upgrade | Migration/integration + deployment validation | Smoke representative user journeys after upgrade | P0 | Required release validation |
| Rollback | Rollback leaves application/data unusable | Deployment/operations validation | Smoke representative journeys after controlled rollback | P0 | Required release validation |
| Dependency interruption | Partial service failure produces false success or unrecoverable UI | Production-like orchestration + focused integration | Prove user-visible fail-closed and recovery behavior | P1 | Required only when deterministic/unique |
| Retry/data integrity | Retry after interruption duplicates or corrupts state | Real DB integration | Confirm user-visible final state after proven lower-level recovery | P1 | Focused CI |
| Concurrency conflicts | Simultaneous actions violate invariants | Real DB integration/concurrency | Verify selected browser-visible conflict/retry outcome only | P1 | Focused CI |
| Observability | Critical request cannot be correlated with audit/log evidence | Feature/integration + production-like runtime | Trigger request and assert sanitized correlation/audit outcome | P1 | Focused CI |
| Accessibility interaction | Keyboard/focus behavior blocks critical journeys | Playwright/accessibility | Keyboard-only and focus behavior for critical surfaces | P1 | Required bounded subset |
| Repeated-run flakiness | Acceptance suite passes once but is unstable | Test infrastructure | Repeat bounded critical suite and report instability | P2 | Scheduled/manual |
| Soak | Long-running runtime/session/cache behavior degrades | Production-like runtime/monitoring | Repeated non-secret critical journeys over time | P2 | Scheduled/manual |
| Volume | Moderate repeated usage exposes leaks or stale state | Integration/performance harness | Browser only for representative journey sampling | P2 | Scheduled/manual |
| Performance budgets | User-visible latency regresses materially | Dedicated performance tooling | Optional navigation timing checks after stable baseline | P2 | Non-blocking until calibrated |
| Authoritative game login | Platform identity cannot reliably enter game | Cross-repository contract/system E2E | Full Platform -> login-server -> Canary journey when authorized | Future launch-dependent | Separate authorized programme |
| Final production | Real deployed topology differs from staging | Production smoke/E2E | Execute only against exact deployed SHA | Production-only | Issue #91 |

## P0 — bounded release-critical hardening

### P0.1 Critical cross-browser portability

Status: **implemented for the first bounded critical slice**. Broader full-suite cross-browser execution remains deferred pending repeated-run cost/flakiness evidence.

Add Playwright projects for:

- Chromium;
- Firefox;
- WebKit.

Do not run the full secret-sensitive suite on every browser by default.

Create a bounded portability tag/profile covering at least:

- public home/navigation and one public game-data flow;
- registration/login/logout or equivalent safe Identity smoke;
- Account Overview authenticated entry;
- one MFA-confirmed privileged/admin authorization success;
- one authorization-denied path;
- one CMS/public visibility path where deterministic fixtures already exist.

Acceptance:

- all selected flows pass on the same exact tested SHA;
- evidence records browser/project identity;
- no test relies on browser-specific timing hacks;
- browser-specific skips require a documented issue and cannot silently count as coverage.

### P0.2 Responsive/mobile critical journeys

Status: **implemented for the first bounded critical slice**. Character-creation-specific responsive coverage and deeper keyboard/focus interaction remain deferred to later bounded slices where they add unique evidence.

Use representative viewport profiles rather than every device preset.

Minimum target profiles:

- desktop: existing primary acceptance viewport;
- tablet: representative medium-width viewport;
- mobile: representative narrow portrait viewport.

Cover critical surfaces:

- public navigation/home/news or equivalent content entry;
- login/registration/recovery form usability;
- MFA challenge;
- Account Overview/provisioning status;
- character creation when ready binding exists;
- administrator navigation and one privileged form/table path.

Assertions should focus on:

- no document-level horizontal overflow;
- critical controls remain reachable and operable;
- no critical content is hidden behind fixed/sticky elements;
- validation/error states remain visible;
- keyboard/focus progression remains usable on selected critical forms.

Avoid multiplying the full 71-screen collector across every browser/viewport unless a measured regression requires it.

### P0.3 Browser-visible security boundary checks

Status: **partially implemented**. The bounded slice includes unauthenticated protected-admin denial in smoke, MFA-confirmed non-admin denial in portability and representative CSRF fail-closed smoke. Remaining unique session/cookie and ownership-manipulation cases require separate bounded assessment against existing feature/security evidence.

Add only scenarios where the browser/system boundary adds evidence beyond existing feature tests.

Candidate assertions:

- authenticated user cannot manipulate a submitted ownership identifier to act on another account;
- unauthenticated/stale session cannot reach protected mutation success;
- missing confirmed MFA cannot complete a privileged mutation;
- missing explicit permission cannot complete a privileged mutation;
- selected session/cookie attributes are observed on the production-like HTTP boundary where configured;
- CSRF rejection remains fail closed on a representative mutation.

Do not persist cookies, reset links, TOTP secrets or recovery codes in artifacts.

### P0.4 Existing-data migration and rollback validation

Build a deterministic representative pre-upgrade dataset using fixtures/seeders, never production data.

Validation sequence:

1. create the representative old-state dataset;
2. apply target migrations/deployment transition;
3. verify schema/data invariants;
4. start the exact target SHA;
5. run bounded critical smoke against migrated data;
6. perform controlled rollback only where the repository's migration/deployment contract supports it safely;
7. verify expected data/application state and bounded smoke after rollback.

This slice should integrate with existing Phase 7 deployment/rollback validation rather than create a competing release mechanism.

## P1 — resilience and evidence correlation

### P1.1 Controlled dependency interruption/recovery

Search existing Phase 7 and Platform DB outage validation first.

Add only scenarios with new assertions. Candidate boundaries:

- Redis interruption during a server/runtime-state read;
- SMTP interruption during password recovery request handling;
- Canary read dependency interruption on a public game-data path;
- interruption around provisioning/character retry where lower-level idempotency is already proven.

Every scenario must prove:

- known pre-state;
- controlled failure injection;
- no false success;
- expected bounded user-facing error;
- relevant persisted state remains valid;
- dependency restoration;
- successful subsequent recovery.

### P1.2 Browser-visible concurrency outcomes

Keep correctness proofs in real MariaDB integration tests.

Browser/system candidates only where UX behavior matters:

- two sessions attempt the same character name and one receives a deterministic conflict without corrupting state;
- two privileged users edit the same managed item and the defined last-write/conflict behavior is visible and auditable, if such a product contract exists;
- repeated user retry after an ambiguous response resolves to the correct final state.

Do not invent conflict semantics that are not part of the product contract.

### P1.3 Observability correlation

For selected critical flows, prove sanitized correlation between:

- the browser-triggered request;
- server-generated request/correlation ID;
- structured request-completion log;
- privileged audit event when applicable;
- expected HTTP/user-visible outcome.

Never copy sensitive log payloads into durable evidence. Assert bounded fields and identifiers only.

### P1.4 Accessibility interaction

Add keyboard/focus interaction checks for a bounded critical set:

- skip/navigation entry if implemented;
- login/recovery/MFA forms;
- Account Overview actions;
- character creation form;
- privileged admin form/table interactions;
- modal/dialog focus trap only where such components exist.

Do not claim screen-reader compatibility from DOM assertions alone. Use semantic/accessibility checks as evidence for the tested boundary only.

## P2 — scheduled/manual confidence profiles

### P2.1 Repeated-run flakiness profile

Run the bounded critical suite repeatedly on the same exact SHA and record:

- iteration count;
- first failing test and iteration;
- browser/project;
- failure classification;
- whether retry masked an initial failure.

A test repeatedly requiring retry is not considered healthy merely because the final job is green.

### P2.2 Soak profile

Run non-secret representative journeys for an extended period in a controlled environment.

Observe where tooling exists:

- process memory/resource growth;
- session/cache accumulation;
- database connection exhaustion;
- Redis connection/key behavior;
- repeated login/logout stability;
- repeated public-read stability;
- bounded mutation/retry stability using disposable fixtures.

No fixed duration is mandated until measured infrastructure cost and signal quality are known.

### P2.3 Optional performance budgets

Do not create arbitrary thresholds.

First establish repeatable baseline distributions in a controlled environment. Promote a performance check into blocking CI only after variance, hardware/runtime constraints and an evidence-backed regression threshold are understood.

## Evidence and artifact requirements

Every new durable acceptance evidence packet should record, where applicable:

- exact tested SHA;
- workflow run/attempt;
- execution profile;
- browser/project;
- viewport class;
- runtime/dependency versions or images when material;
- pass/fail outcome;
- explicit environment classification;
- measured profile duration where CI-cost decisions depend on it;
- retry policy and project-labelled JUnit evidence for bounded matrix measurements.

Secret-bearing flows keep raw trace/screenshot/video disabled unless sanitization is guaranteed by construction.

Failure evidence should prefer:

- test/spec name;
- bounded assertion message;
- HTTP status/route class where non-sensitive;
- sanitized correlation ID;
- deterministic state summary;
- exact SHA/browser/profile.

## CI profile model

Implemented profiles:

- `smoke` — fast primary-browser smoke;
- `full` — full primary-browser production-like functional acceptance plus visual/accessibility collector;
- `portability` — bounded critical subset across Chromium/Firefox/WebKit;
- `responsive` — bounded critical subset across representative viewport classes;
- `critical` — pull-request composition of smoke + portability + responsive without the full visual collector.

Deferred profiles:

- `resilience` — controlled deterministic failure/recovery scenarios;
- `migration` — representative existing-data upgrade/rollback validation;
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

Completing this roadmap is not a prerequisite to preserve the already-closed Functional Acceptance result. Newly discovered regressions must of course be fixed, but the hardening track is additive continuous verification.

The Production Go-Live Gate remains independently pending until issue #91 is executed against the actual final deployed production environment.
