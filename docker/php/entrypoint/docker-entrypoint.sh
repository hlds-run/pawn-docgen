#!/bin/sh
# vim:sw=4:ts=4:et

set -e

entrypoint_log() {
    if [ -z "${PHP_ENTRYPOINT_QUIET_LOGS:-}" ]; then
        echo "$@"
    fi
}

# выполняем init только при стандартном запуске php-fpm
if [ "$1" = "php-fpm" ] || echo "$1" | grep -q '^php-fpm'; then
    if /usr/bin/find "/docker-entrypoint.d/" -mindepth 1 -maxdepth 1 -type f -print -quit 2>/dev/null | read v; then
        entrypoint_log "$0: /docker-entrypoint.d/ is not empty, will attempt to perform initialization"

        entrypoint_log "$0: Looking for scripts in /docker-entrypoint.d/"
        find "/docker-entrypoint.d/" -follow -type f -print | sort -V | while read -r f; do
            case "$f" in
                *.envsh)
                    if [ -x "$f" ]; then
                        entrypoint_log "$0: Sourcing $f"
                        . "$f"
                    else
                        entrypoint_log "$0: Ignoring $f, not executable"
                    fi
                    ;;
                *.sh)
                    if [ -x "$f" ]; then
                        entrypoint_log "$0: Launching $f"
                        "$f"
                    else
                        entrypoint_log "$0: Ignoring $f, not executable"
                    fi
                    ;;
                *)
                    entrypoint_log "$0: Ignoring $f"
                    ;;
            esac
        done

        entrypoint_log "$0: Initialization complete; ready for start up"
    else
        entrypoint_log "$0: No files found in /docker-entrypoint.d/, skipping initialization"
    fi
fi

exec "$@"
