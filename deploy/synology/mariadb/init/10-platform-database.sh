#!/bin/sh
set -eu

required_vars="PLATFORM_DB_NAME PLATFORM_DB_USER PLATFORM_DB_PASSWORD"
for name in $required_vars; do
    eval "value=\${$name:-}"
    if [ -z "$value" ]; then
        echo "Missing required MariaDB init variable: $name" >&2
        exit 1
    fi
done

case "$PLATFORM_DB_NAME$PLATFORM_DB_USER$PLATFORM_DB_PASSWORD" in
    *[!A-Za-z0-9_.-]*)
        echo "Platform database bootstrap values must use only A-Z, a-z, 0-9, dot, underscore or dash." >&2
        exit 1
        ;;
esac

mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" <<SQL
CREATE DATABASE IF NOT EXISTS \`$PLATFORM_DB_NAME\`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$PLATFORM_DB_USER'@'%'
    IDENTIFIED BY '$PLATFORM_DB_PASSWORD';
ALTER USER '$PLATFORM_DB_USER'@'%'
    IDENTIFIED BY '$PLATFORM_DB_PASSWORD';
GRANT ALL PRIVILEGES ON \`$PLATFORM_DB_NAME\`.*
    TO '$PLATFORM_DB_USER'@'%';
FLUSH PRIVILEGES;
SQL
