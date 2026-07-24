# Liquid20 Synology control

This directory contains the bounded control path for the data-only Freqtrade `liquid20-v1` collector running on the existing Synology self-hosted GitHub Actions runner.

## Runtime boundary

The control workflow:

- builds the exact Freqtrade collector commit declared in `.github/workflows/liquid20-synology-control.yml`;
- publishes the immutable image to `ghcr.io/blakinio/liquid20-collector:<full-commit-sha>`;
- deploys one container named `liquid20-collector` in fixed `acceptance` mode;
- bind-mounts only `/volume1/docker/freqtrade-liquidations/data` into the collector;
- exposes no inbound ports;
- provides no exchange keys or Freqtrade trading credentials;
- does not mount the Docker socket into the collector;
- uses `restart=no`, so an interrupted 24-hour attempt remains failed evidence rather than silently continuing.

The Oteryn runner has Docker access because it is the deployment control plane. The Liquid20 collector does not.

## Workflow operations

`Liquid20 Synology Control` supports:

- `bootstrap` — build and publish the immutable image, then deploy acceptance mode unless a collector is already running;
- `status` — inspect the container, show a bounded log tail and list the newest run directory;
- `collect` — copy the newest run directory and container diagnostics into a GitHub Actions artifact;
- scheduled `monitor` — run hourly, report status, and upload a completed run once.

A push to `main` that changes the workflow or this directory performs the initial `bootstrap`. An already running collector is preserved and is never replaced by bootstrap.

## Evidence retention

Completed evidence is uploaded with seven-day GitHub Actions retention to limit storage consumption. After a successful upload, the run receives a `.github-uploaded` marker in its Synology run directory so scheduled monitoring does not upload the same package repeatedly.

The durable source data remains under:

```text
/volume1/docker/freqtrade-liquidations/data/runs/
```

## Failure behavior

The workflow fails closed when:

- Docker is unavailable on the runner;
- the immutable collector image cannot be built or published;
- a new container exits immediately after deployment;
- no run directory exists when collection is requested;
- artifact SHA-256 verification fails.

It does not restart or replace a running collector, weaken the Freqtrade acceptance policy, or classify a failed report as successful.
