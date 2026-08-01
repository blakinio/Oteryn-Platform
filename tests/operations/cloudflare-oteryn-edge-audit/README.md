# Cloudflare edge audit tests

`run.sh` starts a local mock Cloudflare API, executes the read-only audit, validates the sanitized evidence contract, and rejects mutating HTTP method literals.
