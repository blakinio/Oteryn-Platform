# Oteryn Synology staging deployment

This package targets early local/staging testing on Synology Container Manager. It is intentionally not a production deployment topology and does not satisfy the Production Go-Live Gate.

The NAS is a runtime target only. Platform, Game Gateway and the dedicated deployment-runner images are built on GitHub-hosted Actions runners. Canary must also be supplied as a compatible prebuilt image. The Synology deployment path performs image pulls, database/runtime initialization, migrations, health checks and runtime-image rollback; it does not compile C++ or Go source on the NAS.

## Runtime topology

```text
workstation/browser/OTClient
        |
        | SSH loopback forwarding for early tests
        v
Synology host loopback
  8000 -> Platform
  8080 -> Game Gateway
  7171 -> Canary legacy login rollback path
  7172 -> Canary game protocol

Docker private bridge
  Platform <-> MariaDB
  Platform <-> Redis
  Gateway -> HTTPS internal proxy -> Platform private API
  Gateway -> HTTPS internal proxy -> Canary Game Session issuer
```

The Gateway never receives database credentials. Its non-loopback dependencies use generated staging-only TLS certificates and hostname verification. MariaDB and Redis are not published on host ports.

## Repository workflows

- `Build Synology Staging Images` runs on GitHub-hosted runners. Pull requests build without publishing. Trusted `main` pushes and explicit manual runs publish GHCR images tagged with `sha-<full-sha>`; `main` also receives the moving `main` tag.
- `Deploy Synology Staging` is manual only, refuses non-`main` workflow dispatches and targets only the custom runner label `oteryn-staging`.
- The deployment runner is registered with `--no-default-labels`, so generic repository jobs targeting ordinary `self-hosted` runners do not match it.

## First-time setup order

1. Merge a reviewed version of this package so GitHub-hosted Actions can publish:
   - `ghcr.io/blakinio/oteryn-platform`;
   - `ghcr.io/blakinio/oteryn-game-gateway`;
   - `ghcr.io/blakinio/oteryn-deploy-runner`.
2. Provide a compatible prebuilt Canary image that includes the required Game Session issuer. Do not silently substitute the generic upstream image when native-auth testing is expected.
3. Register the dedicated runner on Synology using the procedure below.
4. Create the GitHub Environment `synology-staging` and configure its staging-only secrets/variables.
5. Run `Deploy Synology Staging` manually from `main`, preferably with an exact `sha-<full-sha>` Platform/Gateway image tag.

## Register the dedicated Synology runner

The one remaining non-repository bootstrap action is obtaining a short-lived repository runner registration token from:

```text
Oteryn-Platform -> Settings -> Actions -> Runners -> New self-hosted runner
```

Do not commit or paste that token into issues, PRs, logs or chat.

The supplied runner project is `deploy/synology/runner/compose.yml`. It mounts the Docker socket because its only responsibility is deploying the Oteryn staging stack. Docker-socket access is effectively host-level container control, so keep this runner repository-scoped, private and dedicated to the `oteryn-staging` label.

Before creating the Container Manager project, create a persistent state directory on Synology, for example:

```text
/volume1/docker/oteryn/state
```

Copy `deploy/synology/runner/.env.example` to a local `.env` and set the one-time `RUNNER_TOKEN`. If GHCR requires authentication to pull the private runner package, configure Container Manager with a GitHub credential that has only the package-read access needed for this pull. Do not store that credential in Git.

Create/start the runner project. When GitHub shows `oteryn-synology-staging` online with label `oteryn-staging`:

1. remove `RUNNER_TOKEN` from the Container Manager project environment;
2. restart the runner container;
3. confirm the runner returns online without the registration token.

The persistent `runner_config` volume retains the registered runner credentials. The short-lived registration token is not needed after first registration.

## GitHub Environment configuration

Create an Environment named `synology-staging`.

Required secrets:

```text
OTERYN_STAGING_APP_KEY
OTERYN_STAGING_MARIADB_ROOT_PASSWORD
OTERYN_STAGING_PLATFORM_DB_PASSWORD
OTERYN_STAGING_CANARY_DB_PASSWORD
OTERYN_STAGING_CANARY_READONLY_DB_PASSWORD
OTERYN_STAGING_CANARY_PROVISIONING_DB_PASSWORD
OTERYN_STAGING_CANARY_CHARACTER_CREATE_DB_PASSWORD
OTERYN_STAGING_REDIS_PASSWORD
OTERYN_STAGING_CANARY_RUNTIME_REDIS_PASSWORD
OTERYN_STAGING_PLATFORM_SERVICE_TOKEN
OTERYN_STAGING_PLATFORM_SERVICE_TOKEN_SHA256
OTERYN_STAGING_GAME_SESSION_SERVICE_TOKEN
OTERYN_STAGING_GAME_SESSION_SERVICE_TOKEN_SHA256
```

Optional rotation secrets:

```text
OTERYN_STAGING_PLATFORM_PREVIOUS_SERVICE_TOKEN_SHA256
OTERYN_STAGING_GAME_SESSION_PREVIOUS_SERVICE_TOKEN_SHA256
```

Recommended variables:

```text
OTERYN_STAGING_BIND_ADDRESS=127.0.0.1
OTERYN_STAGING_STATE_DIR=/var/lib/oteryn-staging-state
OTERYN_STAGING_APP_URL=http://127.0.0.1:8000
```

Generate independent high-entropy staging service tokens and store their exact SHA-256 digests in the matching hash secrets. The deploy script verifies both plaintext/hash pairs before changing the stack.

For database passwords used by the staging grant renderer, use random hexadecimal/alphanumeric values. The deploy script rejects unsupported characters rather than attempting unsafe SQL interpolation.

## Deployment behavior

A `deploy` run:

1. checks out trusted `main` on the self-hosted runner;
2. logs in to GHCR with the job-scoped token;
3. writes a permission-restricted ephemeral `.env` from GitHub Environment secrets;
4. validates required values and service-token hashes;
5. pulls prebuilt images;
6. starts MariaDB, Redis and internal TLS bootstrap;
7. starts Canary and waits for the required schema tables;
8. applies the repository-owned least-privilege Canary SQL grant templates without emitting `SHOW GRANTS` output;
9. configures the read-only runtime Redis ACL;
10. starts Platform, runs migrations and ensures the native OAuth client exists;
11. runs the three Canary database privilege verifiers;
12. starts the internal TLS proxy and Game Gateway;
13. verifies Platform/Gateway health and Canary TCP reachability;
14. removes the ephemeral `.env` after the job.

The script snapshots currently running Platform/Gateway/Canary image references before an update. `rollback` restores those runtime images and re-runs health checks. It intentionally does **not** reverse database migrations automatically.

## Early workstation testing through loopback

The safe default binds Platform, Gateway and Canary ports only to Synology loopback. From a workstation with SSH access to the NAS, forward them locally:

```bash
ssh \
  -L 8000:127.0.0.1:8000 \
  -L 8080:127.0.0.1:8080 \
  -L 7171:127.0.0.1:7171 \
  -L 7172:127.0.0.1:7172 \
  <synology-user>@<synology-host>
```

For this early loopback path, configure OTClient's Oteryn endpoints as literal `http://127.0.0.1` URLs and enable its development-only insecure-loopback option. Do not use this HTTP exception for a LAN hostname, public hostname or production environment.

## Manual local execution

The same deployment package can be executed from a trusted Linux deployment host with Docker Compose v2:

```bash
cp deploy/synology/.env.example deploy/synology/.env
# Fill staging-only values outside Git.
bash deploy/synology/scripts/deploy.sh
```

Health check:

```bash
bash deploy/synology/scripts/health-check.sh
```

Runtime-image rollback:

```bash
bash deploy/synology/scripts/rollback.sh
```

## Non-goals and blockers

- This package does not make staging evidence `PRODUCTION_PROVEN`.
- It does not expose DSM, Docker Engine TCP, MariaDB, Redis or the Canary Game Session issuer publicly.
- It does not build Canary on Synology.
- Native-auth activation remains blocked until `CANARY_IMAGE` points to a compatible prebuilt image containing the required Oteryn Game Session issuer.
- The self-hosted runner cannot be registered purely by repository contents; GitHub requires a one-time registration token obtained by the repository owner.
