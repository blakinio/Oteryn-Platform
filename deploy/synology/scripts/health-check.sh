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
done

probe_url() {
    local url="$1"
    local label="$2"

    for _ in $(seq 1 30); do
        if curl --fail --silent --show-error --max-time 5 "$url" >/dev/null; then
            return 0
        fi
        sleep 2
    done

    echo "Health probe failed: $label" >&2
    return 1
}

probe_url "http://${OTERYN_BIND_ADDRESS}:${PLATFORM_PORT}/health" "Platform /health"
probe_url "http://${OTERYN_BIND_ADDRESS}:${GATEWAY_PORT}/health" "Gateway /health"
probe_url "http://${OTERYN_BIND_ADDRESS}:${GATEWAY_PORT}/ready" "Gateway /ready"
probe_url "http://${OTERYN_BIND_ADDRESS}:${GATEWAY_PORT}/version" "Gateway /version"

if ! timeout 3 bash -c "exec 3<>/dev/tcp/${OTERYN_BIND_ADDRESS}/${CANARY_GAME_PORT}" 2>/dev/null; then
    echo "Canary game TCP port is not reachable on the configured staging bind address." >&2
    exit 1
fi

echo "Platform, Gateway and Canary staging probes passed."
