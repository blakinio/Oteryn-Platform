# Cloudflare edge audit design

This report records the protected read-only audit design created after the canonical Cloudflare Tunnel and DNS contract converged.

## Scope

The audit reads only fixed Cloudflare API surfaces for:

- exact Gateway certificate coverage;
- selected zone TLS/security settings;
- redirect, WAF, configuration and response-header Rulesets;
- Bot Management;
- Access applications matching the two canonical public hostnames.

It performs no mutation and emits only a sanitized artifact. Live execution is isolated behind `pull_request_target`, checks out trusted base code from `main`, and requires a trigger PR that changes only the inert marker file.

## Validation

Local deterministic mock validation passed before repository submission. The test proves:

- active account-token verification;
- exact deeper-host certificate detection;
- selected zone-setting capture;
- Oteryn-only Ruleset match extraction;
- Bot and Access summaries;
- `mutation: none`;
- absence of mutating HTTP method literals.

## Next gate

After exact-head CI and governance pass, merge the implementation. Then open a marker-only trigger PR, review the live sanitized audit, and build only the narrow repair supported by that evidence.
