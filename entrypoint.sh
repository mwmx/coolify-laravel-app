#!/bin/bash
mkdir -p /storage/app/public /storage/framework/{cache,sessions,views} /storage/logs
chown -R unit:unit /storage /data
exec unitd --no-daemon
