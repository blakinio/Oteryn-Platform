---
task_id: OTERYN-20260727-tibia-linux-runner-analysis
required_reads:
  - AGENTS.md
  - docs/agents/REPOSITORY_MAP.md
  - docs/agents/CONTEXT_ROUTING.md
  - docs/agents/tasks/TASK_TEMPLATE.md
  - .github/workflows/deploy-synology-staging.yml
search_first:
  - oteryn-staging
  - synology staging workflow
optional_reads: []
---

# OTERYN-20260727-tibia-linux-runner-analysis

## Goal

Use the existing Synology self-hosted runner and persistent Docker volume to perform a bounded, text-only static analysis of the official Tibia Linux client, reconstruct its protected/unprotected game-server route flow, compare the current protocol with OTClient, and document the responsible-disclosure boundary without modifying staging services or redistributing proprietary binaries.

## Acceptance criteria

- [x] Workflow and report-recovery steps execute on runner label `oteryn-staging` without using staging secrets or services.
- [x] Downloaded client bytes remain only in Docker volume `oteryn-tibia-linux-analysis` on Synology.
- [x] The exact analyzed ELF version, size, SHA-256 and Build ID are recorded.
- [x] Loginservice world fields are mapped to protected and unprotected endpoints.
- [x] `TGameserverDualConnection` route scheduling, primary/secondary behavior, fallback and backoff are reconstructed.
- [x] The final `QTcpSocket`/`connectToHost` path is identified.
- [x] The challenge-driven protobuf login structure is reconstructed to the field-number and wire-type level, with unresolved names explicitly marked unknown.
- [x] The semantic roles and immediate application effects of all three callbacks supplied to `BEClient.so::Init` are recovered.
- [x] Current OTClient compatibility is assessed against the analyzed 15.30 protocol.
- [x] Confirmed client behavior, strong inferences, unknown server controls and rejected hypotheses are separated.
- [x] A minimal safe validation test and responsible-disclosure threshold are documented.
- [x] A durable final text report and callback addendum are committed.
- [x] Temporary automatic analysis workflows are removed from the branch.
- [x] No CipSoft binary, package, asset, credential, cookie or session secret is committed or uploaded as a GitHub artifact.

## Ownership

```yaml
owned_paths:
  - .github/workflows/tibia-linux-runner-analysis.yml
  - .github/workflows/tibia-linux-report-artifact.yml
  - docs/agents/tasks/active/OTERYN-20260727-tibia-linux-runner-analysis.md
  - docs/agents/reports/OTERYN-20260727-tibia-linux-protected-route-analysis.md
  - docs/agents/reports/OTERYN-20260727-tibia-linux-battleye-callback-addendum.md
modules:
  - GitHub Actions infrastructure
  - static protocol and transport analysis
dependencies:
  - self-hosted runner oteryn-synology-staging
  - host Docker socket
  - persistent Docker volume oteryn-tibia-linux-analysis
blockers:
  - official-server acceptance and BattlEye enforcement require an own-account live test with a fresh legitimate session key and explicit risk acceptance
cross_repository_tasks:
  - current blakinio/otclient source was inspected read-only for protocol compatibility
```

## Context checkpoint

```yaml
checkpoint_version: 1
updated_at: 2026-07-30T08:18:00Z
head: d3873f3d5b1fcd5ec721dfcdfc4ce225973209ee
branch: ci/OTERYN-20260727-tibia-linux-runner-analysis
pr: 218
status: blocked
context_routes:
  - testing
  - security
  - cross-repository
owned_paths:
  - .github/workflows/tibia-linux-runner-analysis.yml
  - .github/workflows/tibia-linux-report-artifact.yml
  - docs/agents/tasks/active/OTERYN-20260727-tibia-linux-runner-analysis.md
  - docs/agents/reports/OTERYN-20260727-tibia-linux-protected-route-analysis.md
  - docs/agents/reports/OTERYN-20260727-tibia-linux-battleye-callback-addendum.md
proven:
  - The persistent analysis run is 30246435256-1 and the analyzed client SHA-256 is 8b25d65ece158723dbb50a1b592c1ec8a3247a650fcd2d299bebdfd133cb5752.
  - The loginservice parser requires protected and unprotected host and port fields plus anticheatprotection.
  - externaladdressprotected and externalportprotected form the protected route; externaladdressunprotected and externalportunprotected form the unprotected route.
  - Protected route eligibility is scheduled at time T and unprotected route eligibility at T plus 1 millisecond.
  - With optimizeConnectionStability disabled only one connection is attempted or active at a time; with it enabled both routes may be in flight.
  - The first route to connect is primary and a later connected route is secondary; protected is normally first because it is scheduled first.
  - EConnectionsUsed values are none 0, unprotected 1, protected 2 and both 3.
  - Route failure backoff is 1, 2, 4, 8 and then 16 seconds.
  - The selected QUrl host and port reach QTcpSocket connectToHost.
  - The official 15.30 login path is challenge-driven protobuf, not the current OTClient legacy RSA and XTEA login message.
  - BEClient.so is loaded through QLibrary, Init is resolved, the protected endpoint is supplied and three callbacks are registered.
  - Callback 0x7418f0 is a BattlEye diagnostic message sink using the literal prefix BattlEye and has no direct network effect.
  - Callback 0x741de0 records or requests a mandatory client restart for service failure or update-required state and has no direct network effect.
  - Callback 0x741b50 copies an opaque BattlEye-produced byte buffer and emits an application Qt signal; it has no direct socket write.
  - The final report is docs/agents/reports/OTERYN-20260727-tibia-linux-protected-route-analysis.md.
  - The callback addendum is docs/agents/reports/OTERYN-20260727-tibia-linux-battleye-callback-addendum.md.
derived:
  - Protected is normally the primary route and unprotected is normally fallback or secondary, but primary and secondary are connection-order states rather than permanent route identities.
  - TCP reachability of the unprotected endpoint does not demonstrate game-session acceptance or a security defect.
  - Current OTClient requires substantial protobuf, framing, challenge and secondary-connection implementation before it can interoperate with this client generation.
  - Static client analysis cannot prove which login fields the server requires or whether protected-world policy fails closed.
  - Only the 0x741b50 callback can contribute opaque BattlEye bytes toward application transport, and it does so indirectly through Qt signaling.
unknown:
  - The exact downstream Tibia protocol envelope and message type used for bytes emitted by callback 0x741b50.
  - Whether BEClient.so contributes bytes to the first game-server authentication request.
  - Exact generated protobuf names for all nested login fields and the numeric outer message type for GameclientMessageLogin.
  - Which fields are mandatory and independently validated by the server.
  - Whether a protected or optional world accepts a session without valid BattlEye state or data.
conflicts: []
first_failure:
  marker: official-server acceptance test unavailable to autonomous repository analysis
  evidence: no account credential or fresh legitimate loginservice session key was supplied, and live anti-cheat enforcement testing on a third-party service was kept outside the autonomous task
rejected_hypotheses:
  - Address 0xc49630 is the protected or unprotected route selector.
  - The branch on world-record offset 0x69 is a BattlEye route selector.
  - Functions near 0x722200 and 0x7226e0 are route selectors.
  - Arbitrary dlopen or dlsym references prove BEClient.so loading.
  - The official 15.30 first login packet is the historical OTClient RSA and XTEA block.
  - Primary and secondary are permanently synonymous with protected and unprotected.
  - Callback 0x7418f0 sends network data.
  - Callback 0x741de0 sends network data.
changed_paths:
  - docs/agents/reports/OTERYN-20260727-tibia-linux-protected-route-analysis.md
  - docs/agents/reports/OTERYN-20260727-tibia-linux-battleye-callback-addendum.md
  - docs/agents/tasks/active/OTERYN-20260727-tibia-linux-runner-analysis.md
  - .github/workflows/tibia-linux-report-artifact.yml (temporary workflow removed)
  - .github/workflows/tibia-linux-protobuf-battleye-bounded.yml (temporary workflow removed)
  - .github/workflows/tibia-linux-runner-analysis.yml (temporary workflow removed)
validation:
  - command: workflow run 30493514149
    result: PASS
    evidence: persistent volume inventory completed and verified the exact client hash plus text-report corpus
  - command: workflow run 30493947069
    result: PASS
    evidence: focused text-only report recovery completed and uploaded artifact 8740881984 with one-day retention
  - command: focused static evidence review
    result: PASS
    evidence: route scheduler, socket path, protobuf field layout and OTClient comparison are documented in the final report
  - command: workflow run 30495579436 rerun job 90815528899
    result: PASS
    evidence: bounded callback and protobuf extraction completed successfully and uploaded text-only artifact 8752558221
  - command: workflow run 30526055084 job 90817099201
    result: PASS
    evidence: exact callback literals and bounded callback context were recovered in text-only artifact 8752725268
  - command: localhost synthetic login-and-one-turn dry-run
    result: PASS
    evidence: challenge, login, login success, one turn-in-place action, state update and disconnect completed against a local mock only
  - command: minimal own-account official-server acceptance test
    result: NOT_RUN
    evidence: no credentials or fresh legitimate session key were available and the live third-party anti-cheat enforcement test was not performed autonomously
  - command: temporary workflow cleanup
    result: PASS
    evidence: all temporary callback extraction and report-recovery workflow files were deleted after use
blockers:
  - Server-side acceptance and BattlEye enforcement cannot be established by static analysis or the localhost mock.
  - A live test requires the user's own disposable account, fresh legitimate session key and explicit acceptance of account-sanction risk.
next_action: Under a separately controlled own-account procedure, capture only the first decisive official-server rejection or session acceptance without patching, hooking, disabling or impersonating BattlEye; otherwise keep the result NOT_RUN.
```

## Notes

The durable route and protocol analysis is in `docs/agents/reports/OTERYN-20260727-tibia-linux-protected-route-analysis.md`. The successful callback rerun and safe localhost dry-run are documented in `docs/agents/reports/OTERYN-20260727-tibia-linux-battleye-callback-addendum.md`. The named volume and existing reports were preserved. No client binary or active bypass material was added to GitHub.
