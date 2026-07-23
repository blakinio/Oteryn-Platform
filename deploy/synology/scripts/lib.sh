#!/usr/bin/env bash

load_oteryn_env_file() {
    local env_file="$1"
    local line key value

    if [[ ! -f "$env_file" ]]; then
        echo "Missing staging environment file: $env_file" >&2
        return 1
    fi

    while IFS= read -r line || [[ -n "$line" ]]; do
        line="${line%$'\r'}"
        if [[ -z "$line" || "$line" =~ ^[[:space:]]*# ]]; then
            continue
        fi
        if [[ "$line" != *=* ]]; then
            echo "Invalid staging environment line; expected KEY=VALUE." >&2
            return 1
        fi

        key="${line%%=*}"
        value="${line#*=}"
        if [[ ! "$key" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]]; then
            echo "Invalid staging environment key: $key" >&2
            return 1
        fi

        printf -v "$key" '%s' "$value"
        export "$key"
    done < "$env_file"
}
