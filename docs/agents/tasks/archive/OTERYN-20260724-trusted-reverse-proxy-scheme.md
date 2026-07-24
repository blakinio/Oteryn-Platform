---
task_id: OTERYN-20260724-trusted-reverse-proxy-scheme
status: completed
related_pr: "133"
superseded_pr: "131"
merge_commit: f2f1a4582ada6948ec3fa6a49d871015fa7674e9
completed: 2026-07-24T18:33:00+02:00
evidence_classification: PRODUCTION_LIKE_PROVEN
---

# OTERYN-20260724-trusted-reverse-proxy-scheme

## Outcome

Platform now generates externally correct HTTPS URLs only when forwarded metadata is presented by explicitly configured proxy IPs or CIDRs. Direct and untrusted clients cannot spoof scheme or host.

## Delivered

- Explicit `TRUSTED_PROXIES` parsing with wildcard rejection.
- Request-time trusted-proxy middleware replacing implicit/default trust.
- HTTPS login form and absolute URL generation behind the configured TLS proxy.
- Trusted and untrusted regression coverage independent of the test environment's configured application scheme.
- Focused tests, PHPStan, full Platform CI and Phase 7 passed.
- Production-like rehearsal `30095854266` completed OAuth Authorization Code + PKCE through the real HTTPS reverse-proxy boundary.

## Completion

- Delivered through PR `blakinio/Oteryn-Platform#133`.
- Merge commit: `f2f1a4582ada6948ec3fa6a49d871015fa7674e9`.
- Superseded PR `#131` closed without merge.
- Archived at: 2026-07-24T18:40:00+02:00
