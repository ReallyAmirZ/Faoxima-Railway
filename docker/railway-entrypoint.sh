#!/bin/sh
set -eu

APP_DIR="/var/www/faoxima"
cd "$APP_DIR"

export FAOXIMA_DOCKER_ENV=1

PORT_VALUE="${PORT:-8080}"
case "$PORT_VALUE" in
    ''|*[!0-9]*)
        echo "[railway] PORT must be numeric." >&2
        exit 1
        ;;
esac

sed -ri "s/^Listen [0-9]+/Listen ${PORT_VALUE}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT_VALUE}>/" /etc/apache2/sites-available/000-default.conf

# PHP's Apache image must use prefork. Some package combinations can leave
# event/worker links enabled as well, which makes Apache exit with AH00534.
rm -f \
    /etc/apache2/mods-enabled/mpm_event.conf \
    /etc/apache2/mods-enabled/mpm_event.load \
    /etc/apache2/mods-enabled/mpm_worker.conf \
    /etc/apache2/mods-enabled/mpm_worker.load
if [ ! -e /etc/apache2/mods-enabled/mpm_prefork.load ]; then
    a2enmod mpm_prefork >/dev/null
fi

if [ -z "${DOMAIN:-}" ] && [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ]; then
    export DOMAIN="$RAILWAY_PUBLIC_DOMAIN"
fi

missing=""
for variable in DB_HOST DB_NAME DB_USER DB_PASS TELEGRAM_BOT_TOKEN TELEGRAM_ADMIN_ID DOMAIN; do
    value="$(printenv "$variable" 2>/dev/null || true)"
    if [ -z "$value" ]; then
        missing="$missing $variable"
    fi
done
if [ -n "$missing" ]; then
    echo "[railway] Missing required variables:$missing" >&2
    exit 1
fi

mkdir -p logs storage/cache storage/private cronbot/.runtime

echo "[railway] Building runtime config from environment variables..."
php "$APP_DIR/docker/php-entrypoint-configure.php"

if [ "${FAOXIMA_AUTO_MIGRATE:-1}" = "1" ]; then
    attempt=1
    max_attempts="${FAOXIMA_DB_BOOT_ATTEMPTS:-45}"
    while ! php -r 'define("FAOXIMA_LAZY_MYSQLI", true); require "config.php"; exit($pdo instanceof PDO ? 0 : 1);'; do
        if [ "$attempt" -ge "$max_attempts" ]; then
            echo "[railway] Database was not ready after ${max_attempts} attempts." >&2
            exit 1
        fi
        echo "[railway] Database not ready (attempt ${attempt}/${max_attempts}); retrying in 2 seconds..."
        attempt=$((attempt + 1))
        sleep 2
    done

    echo "[railway] Applying database migrations and configuring Telegram webhook..."
    php "$APP_DIR/table.php"
fi

chown -R www-data:www-data "$APP_DIR/logs" "$APP_DIR/storage" "$APP_DIR/cronbot/.runtime" 2>/dev/null || true
chmod 600 "$APP_DIR/config.php" 2>/dev/null || true

cron

echo "[railway] Faoxima is ready on port ${PORT_VALUE}."
exec "$@"
