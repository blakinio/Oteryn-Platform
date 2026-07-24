#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
DEPLOY_DIR="$(cd -- "$SCRIPT_DIR/.." && pwd)"
ENV_FILE="${OTERYN_ENV_FILE:-$DEPLOY_DIR/.env}"
COMPOSE_FILE="$DEPLOY_DIR/compose.yml"

# shellcheck source=deploy/synology/scripts/lib.sh
source "$SCRIPT_DIR/lib.sh"
load_oteryn_env_file "$ENV_FILE"

compose=(docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE")

declare -A container_ids=()
for service in mariadb redis canary platform internal-proxy gateway; do
    container_id="$("${compose[@]}" ps -q "$service")"
    if [[ -z "$container_id" ]]; then
        echo "Service is not created: $service" >&2
        exit 1
    fi
    running="$(docker inspect --format '{{.State.Running}}' "$container_id")"
    if [[ "$running" != "true" ]]; then
        echo "Service is not running: $service" >&2
        exit 1
    fi
    container_ids["$service"]="$container_id"
done

probe_url() {
    local service="$1"
    local port="$2"
    local path="$3"
    local label="$4"
    local container_id="${container_ids[$service]}"

    for _ in $(seq 1 30); do
        if docker run --rm \
            --network "container:$container_id" \
            alpine:3.22 \
            /bin/sh -ec \
            "wget -qO- -T 5 'http://127.0.0.1:${port}${path}' >/dev/null"; then
            return 0
        fi
        sleep 2
    done

    echo "Health probe failed: $label" >&2
    return 1
}

probe_url platform 8000 /health "Platform /health"
probe_url gateway 8080 /health "Gateway /health"
probe_url gateway 8080 /ready "Gateway /ready"
probe_url gateway 8080 /version "Gateway /version"

if ! docker run --rm \
    --network "container:${container_ids[canary]}" \
    alpine:3.22 \
    /bin/sh -ec \
    "nc -z -w 3 127.0.0.1 '${CANARY_GAME_PORT}'"; then
    echo "Canary game TCP port is not reachable inside the Canary network namespace." >&2
    exit 1
fi

echo "Platform, Gateway and Canary staging probes passed."
