# Cloudflare Oteryn edge audit

## Purpose

This audit reads the Cloudflare configuration that remains relevant after the canonical Tunnel and DNS reconciliation completed successfully.

It is intentionally separate from `.github/workflows/cloudflare-oteryn-endpoints.yml`. The existing endpoint workflow owns only:

- the two canonical proxied DNS records;
- the two canonical Cloudflare Tunnel ingress rules.

This audit inspects, but does not mutate:

- edge certificate packs and exact coverage for `login.oteryn.molehill.cloud`;
- selected zone TLS and security settings;
- zone Rulesets for redirects, custom WAF, response-header transforms and configuration rules;
- Bot Management/Bot Fight Mode state;
- Cloudflare Access applications matching the two canonical hosts.

## Canonical hosts

```text
oteryn.molehill.cloud
login.oteryn.molehill.cloud
```

The deeper Gateway hostname requires direct certificate coverage. A zone wildcard such as `*.molehill.cloud` does not cover it.

## Trust boundary

The live audit executes only from the trusted implementation already present on `main`.

A trigger pull request may change only:

```text
ops/triggers/cloudflare-edge-audit.md
```

The `pull_request_target` job checks out the base SHA, not the trigger branch. Therefore the trigger cannot alter the code that receives the protected `production-cloudflare` environment token.

The Python implementation issues `GET` requests only. Its deterministic test rejects mutating method literals and validates the sanitized output against a mock Cloudflare API.

## API surfaces and likely permissions

The audit calls these fixed Cloudflare API families:

- certificate packs: `SSL and Certificates Read`;
- zone settings: `Zone Settings Read`;
- Rulesets: product-specific Rulesets read permissions, including Transform Rules, Dynamic URL Redirects or WAF as applicable;
- Bot Management: `Bot Management Read`;
- Access applications: `Access: Apps and Policies Read`.

A `401` or `403` is retained only as `permission_denied` with bounded Cloudflare error code/message. No token value, request authorization header or raw API response is written to the artifact.

## Output

The artifact contains:

```text
cloudflare-edge-audit/evidence.json
cloudflare-edge-audit/summary.md
```

It records only:

- API readability state;
- selected non-secret setting values;
- certificate coverage status;
- IDs and actions of Ruleset entries that explicitly match the two public Oteryn hostnames;
- matching Access application IDs/domains;
- bounded Bot Management settings;
- `mutation: none`.

## Trigger procedure

After this implementation is merged:

1. create a branch from current `main`;
2. update only `ops/triggers/cloudflare-edge-audit.md` with a new timestamp and reason;
3. open a pull request to `main`;
4. inspect the `Cloudflare Oteryn Edge Audit` result and sanitized artifact;
5. close the trigger PR without merge.

The resulting evidence determines the narrow next repair. No certificate, WAF, redirect, Bot, Access or HSTS mutation should be added until the audit proves the current state and available permissions.
