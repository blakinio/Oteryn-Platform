# E2E Existing-Data Upgrade and Rollback Evidence

## Purpose

This record captures the P0 representative existing-data upgrade/migration and controlled rollback/redeploy validation added through PR #99.

The validation extends the existing `Phase 7 Production-Like Validation` release harness. It does not create a second deployment mechanism, does not use production data and does not change the independent Production Go-Live Gate under issue #91.

## Validation model

The Phase 7 workflow now prepares both:

- the exact candidate release at `VALIDATION_SHA`;
- the previous known-good release at `BASE_SHA`.

A dedicated isolated MariaDB schema, `oteryn_upgrade`, is created for this slice so the representative-data test does not modify the primary Phase 7 validation schema.

The synthetic pre-upgrade dataset contains:

- one disposable Platform Identity with a generated non-user Argon2id credential hash;
- one published Platform-owned news post used for public HTTP smoke.

No production dump, production credential, personal data or reusable user credential is used.

A non-secret fingerprint derived from the representative Identity and news fields is compared in memory throughout the run. The fingerprint and credential hash are not written to the durable evidence artifact.

## Sequence proven

The integrated release-validation sequence is:

1. create the isolated upgrade database;
2. run all `BASE_SHA` Platform migrations against that database;
3. insert the deterministic synthetic representative dataset;
4. record the baseline data fingerprint;
5. run the exact `VALIDATION_SHA` migrations against the existing dataset;
6. verify migration count monotonicity and unchanged representative-data fingerprint;
7. switch the existing Phase 7 release symlink to the candidate and run bounded `/health` plus published-news HTTP smoke;
8. switch the same release symlink to `BASE_SHA` while keeping the post-upgrade database and run the same bounded smoke;
9. verify the representative dataset remains intact after rollback-code smoke;
10. switch/redeploy `VALIDATION_SHA`, rerun migrations idempotently and rerun the bounded smoke;
11. verify the representative dataset fingerprint remains unchanged;
12. restore the Phase 7 current-release symlink to the candidate and continue the pre-existing interrupted-release/redeploy validation.

Any migration error, data-integrity mismatch, old-code incompatibility, HTTP smoke failure or redeploy failure fails the Phase 7 job closed.

## First exact-SHA evidence

Implementation head:

`45ce658f54cbbe78652b7e8710e0cd25c7e85a2a`

Rollback/base SHA:

`26ff602696c597aac0833415b0a47af5d427a52d`

Workflow:

- `Phase 7 Production-Like Validation` run `29844031564`;
- job `88679862151`;
- result: **PASS**.

Artifact:

- `phase7-production-like-evidence-29844031564`;
- artifact id `8500578323`;
- digest `sha256:d28fc7fc5511bcbcc30eae21842133c30a88a9fdfcd1136893472a88affdfadb`.

The artifact contains the existing Phase 7 evidence plus `phase7-existing-data-upgrade-evidence.json`.

Measured representative upgrade evidence:

- classification: `STAGING_PROVEN`;
- synthetic dataset: `synthetic_identity_and_published_news`;
- base migration count: `11`;
- candidate migration count: `11`;
- existing-data upgrade: `PASS`;
- candidate application smoke: `PASS`;
- rollback code with post-upgrade database: `PASS`;
- rollback application smoke: `PASS`;
- candidate redeploy application smoke: `PASS`;
- representative dataset fingerprint preserved: `PASS`.

The same run also preserved the existing Phase 7 controls, including clean deployment, configuration guardrails, database privilege checks, Redis/SMTP failure semantics, critical regression suite, live security-header/cookie/correlation checks, backup/restore integrity, interrupted-release isolation and redeploy.

## Migration-delta interpretation

PR #99 introduces validation infrastructure and does not itself add a Platform schema migration. Therefore the first evidence run correctly reports `11` base migrations and `11` candidate migrations.

This proves that the release harness can construct data on the previous known-good schema, carry that existing data through the candidate migration command, execute candidate/rollback/redeploy code against the same persisted state and fail closed on incompatibility. It does **not** fabricate evidence of a schema delta that did not exist in this PR.

When a future candidate actually introduces new migrations, the same required Phase 7 path will apply those candidate migrations to the `BASE_SHA` synthetic existing-data state before candidate and rollback smoke. A future destructive or backward-incompatible migration must still satisfy the repository's migration safety and rollout/rollback policy; this harness does not make unsafe rollback semantics acceptable.

## Evidence classification boundary

This result is `STAGING_PROVEN` only for the controlled production-like release-validation environment.

It does not prove:

- final production data shape or volume;
- final production migration duration or lock behavior;
- production provider deployment/rollback mechanics;
- production backup/RTO/RPO;
- final production TLS, network or dependency topology;
- that every future schema change is backward compatible.

Those facts remain direct production or change-specific evidence. Issue #91 remains the independent final production execution tracker.
