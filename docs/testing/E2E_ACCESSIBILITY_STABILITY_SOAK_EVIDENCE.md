# E2E Accessibility, Stability and Soak Evidence

## Scope

This record covers issue #110 / PR #111 and the bounded hardening slices added after the existing exact-SHA production-like acceptance baseline:

- P1 keyboard/focus accessibility interaction;
- P2 repeated-run flakiness measurement;
- P2 read-only public soak measurement.

All evidence in this document is repository or controlled production-like staging evidence. It does not establish `PRODUCTION_PROVEN` behavior and does not change the independent Production Go-Live Gate in issue #91.

## Accessibility interaction profile

Project: `accessibility-chromium`.

Execution policy:

- Chromium desktop only;
- zero retries inside the spec;
- included in direct pull-request `critical` acceptance and in `full` acceptance;
- raw trace, automatic screenshot and video remain disabled;
- diagnostics contain exact tested SHA plus bounded console/page/request/server-error metadata and do not persist form values, cookies, MFA secrets or recovery codes.

Representative interaction evidence:

- login fields and submit are reached by repeated keyboard `Tab`, expose `:focus-visible` plus a visible computed focus indicator, and submit with `Enter`;
- password-recovery email and submit are reached and activated by keyboard;
- Account Overview `Create a character` link is reached with `Tab` and activated with `Enter`;
- character name/vocation/sex/submit controls are reached in keyboard traversal, including reverse `Shift+Tab` verification;
- MFA challenge input and submit are reached and completed with keyboard input/`Enter`;
- managed-page table `Edit` link is reached and activated with keyboard, followed by keyboard traversal through the edit form to `Save` and reverse focus verification.

The profile does not claim screen-reader compatibility from DOM/focus assertions alone.

First successful exact-head implementation evidence:

- exact tested SHA: `3bd1e4901a71841bc4593ec7e4efb98866c8c30f`;
- Acceptance E2E and Visual UX run: `29853941922`, attempt 1;
- profile: `critical`;
- aggregate result: `AUTOMATED_E2E_CRITICAL_PASS`;
- smoke: PASS, 10 s wall-clock;
- portability: PASS, 32 s wall-clock;
- responsive: PASS, 9 s wall-clock;
- resilience: PASS, 3 s wall-clock;
- accessibility: PASS, 6 s wall-clock;
- accessibility JUnit: 3 tests, 0 failures, 0 skipped, 5.392079 s total test time;
- artifact: `acceptance-e2e-critical-29853941922-1-direct`, digest `sha256:df7776df2c3ecb6e3199baab24469e351b3bbef4d099d916095a99f68f183b9a`.

During implementation an earlier WebKit portability run exposed an existing navigation race: the portability scenario began privileged navigation before the MFA-completion redirect had settled. The fix added an explicit post-MFA URL synchronization and the next exact-head portability run passed. The first accessibility run then exposed the same class of race in the new keyboard login helper; `keyboardLogin` now waits until navigation leaves `/login` before the caller begins the next route. Neither failure was masked with retries.

## Repeated-run stability profile

Workflow: `.github/workflows/acceptance-stability.yml`.

Policy:

- scheduled weekly and manually dispatchable;
- three fresh isolated jobs per run;
- each job calls the exact-SHA reusable acceptance workflow with profile `critical`;
- each iteration has a distinct `run_suffix` and artifact identity;
- `ACCEPTANCE_ZERO_RETRIES=1` forces Playwright global retries to zero for stability measurement;
- the caller uses `fail-fast: false`, so later iterations still run after an earlier iteration failure and preserve evidence for instability classification;
- MariaDB, Redis, MailHog, Laravel runtime and file-backed cache/session state are fresh per matrix job rather than reused across iterations.

This profile measures whether the bounded required critical acceptance remains stable across independent executions. A failed iteration is not masked by Playwright retry success.

First scheduled/manual three-iteration evidence remains `PENDING_FIRST_RUN`; the current connector exposes workflow definitions and run inspection but not a workflow-dispatch action. The workflow definition is validated through repository governance/CI and will produce iteration-labelled exact-SHA artifacts when first scheduled or manually dispatched.

## Read-only public soak profile

Project: `soak-chromium`.

Workflow: `.github/workflows/acceptance-soak.yml`.

Policy:

- scheduled weekly and manually dispatchable;
- default bounded duration: 300 seconds;
- zero retries;
- public read-only routes only: home, online, highscores and servers;
- no authentication, MFA, password recovery, account mutation, character mutation or privileged mutation in the soak loop;
- every navigation requires HTTP 200 plus a representative expected UI assertion.

Collected non-secret metrics:

- exact tested SHA;
- target and measured soak duration;
- iteration/request count;
- overall min/p50/p95/max navigation time;
- per-route request count and p50/p95/max navigation time;
- Laravel serve process-tree RSS start/end/max samples;
- Redis key count before and after the soak.

No latency, memory or Redis-key budget is enforced in the initial profile. Metrics are calibration evidence only until repeated runs establish normal variance and a defensible regression threshold.

First scheduled/manual soak evidence remains `PENDING_FIRST_RUN`; the current connector does not expose workflow dispatch. The soak implementation is therefore not claimed as runtime-proven until its first scheduled/manual execution completes.

## Current implementation classification

- accessibility interaction mechanism and bounded required profile: `STAGING_PROVEN` on the exact SHA/run above;
- repeated-run workflow mechanism: `REPO_PROVEN`, first multi-iteration runtime evidence pending;
- soak workflow/profile mechanism: `REPO_PROVEN`, first scheduled/manual runtime evidence pending;
- production behavior: `UNKNOWN` until directly verified where applicable.

## Production boundary

This work does not prove:

- final production keyboard behavior under the deployed edge/runtime stack;
- production browser/device assistive-technology compatibility;
- production long-duration memory stability;
- production latency distributions or performance budgets;
- production Redis/session/cache accumulation;
- production HA/failover behavior.

Those remain direct-production concerns where applicable and cannot be promoted from controlled staging evidence.
