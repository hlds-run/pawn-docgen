#!/bin/sh

entrypoint_log() {
    if [ -z "${PHP_ENTRYPOINT_QUIET_LOGS:-}" ]; then
        echo "[composer-install] $@"
    fi
}

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    entrypoint_log "vendor/ not found, running composer install..."

    if [ -f /var/www/html/composer.json ]; then
        composer install --no-dev --no-interaction --working-dir=/var/www/html
        entrypoint_log "done"
    else
        entrypoint_log "composer.json not found, skipping"
    fi
else
    entrypoint_log "vendor/ already exists, skipping"
fi
