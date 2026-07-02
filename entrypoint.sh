#!/bin/bash
set -euo pipefail

# The persistent storage volume is mounted at /storage (see useStoragePath in
# bootstrap/app.php). Make sure the directory layout Laravel expects exists and
# is writable by the unprivileged "unit" user that the workers run as.
mkdir -p /storage/app/public \
         /storage/framework/cache/data \
         /storage/framework/sessions \
         /storage/framework/views \
         /storage/logs
chown -R unit:unit /storage /var/www/html/bootstrap/cache
chmod -R 775 /storage /var/www/html/bootstrap/cache

# Drop privileges to the unprivileged "unit" user for artisan/worker processes
# so files written to the shared /storage volume keep ownership consistent with
# the Unit PHP workers. Fall back to running as root if no helper is available.
if command -v runuser >/dev/null 2>&1; then
    priv=(runuser -u unit --)
elif command -v gosu >/dev/null 2>&1; then
    priv=(gosu unit)
else
    priv=()
fi

role="${1:-web}"

case "${role}" in
    web)
        # The web container owns schema migrations and warms the framework
        # caches. Workers wait for this container to become healthy first.
        "${priv[@]}" php artisan migrate --force
        "${priv[@]}" php artisan config:cache
        "${priv[@]}" php artisan route:cache
        "${priv[@]}" php artisan view:cache

        unitd --no-daemon &

        while [ ! -S /var/run/control.unit.sock ]; do
            sleep 0.1
        done

        curl -X PUT --unix-socket /var/run/control.unit.sock \
            -d @/docker-entrypoint.d/unit.json \
            http://localhost/config

        wait
        ;;
    queue)
        exec "${priv[@]}" php artisan queue:work --sleep=3 --tries=3 --timeout=90
        ;;
    scheduler)
        exec "${priv[@]}" php artisan schedule:work
        ;;
    *)
        # Fall back to running whatever command was passed in.
        exec "$@"
        ;;
esac
