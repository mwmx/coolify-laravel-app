#!/bin/bash
mkdir -p /storage/app/public /storage/framework/{cache,sessions,views} /storage/logs
chown -R unit:unit /storage /data

unitd --no-daemon &

while [ ! -S /var/run/control.unit.sock ]; do
    sleep 0.1
done

curl -X PUT --unix-socket /var/run/control.unit.sock \
    -d @/docker-entrypoint.d/unit.json \
    http://localhost/config

wait

