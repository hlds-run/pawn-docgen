#!/bin/sh
set -e

BASE_DIR="/srv/pawn-docgen"
CACHE_DIR="/var/www/html/cache/og_images"

MARKER="$BASE_DIR/.og-cache-generated"

if [ -f "$MARKER" ]; then
    echo "[og-cache] cache already generated, skipping"
    exit 0
fi

echo "[og-cache] generating OG image cache"


# Запуск PHP-скрипта для генерации кеша
php "$BASE_DIR/generate/generate_og_cache.php"

touch "$MARKER"

echo "[og-cache] done"