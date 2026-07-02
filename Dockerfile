# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1 — compile the front-end assets with Vite.
#
# Dokploy builds from a clean git checkout, where public/build does not exist
# (it is git-ignored). The welcome view uses @vite(...), so the manifest must
# be generated here rather than relying on a locally committed build.
# ---------------------------------------------------------------------------
FROM node:22-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---------------------------------------------------------------------------
# Stage 2 — the application image.
#
# The base image is defined in docker/Dockerfile and pushed to Docker Hub;
# its tag is incremented whenever it changes. It bundles Nginx Unit, PHP 8.3
# and all required extensions (pdo_mysql, redis, gd, intl, ...).
# ---------------------------------------------------------------------------
FROM mwmx/devriglaravelbase:005

WORKDIR /var/www/html

RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R unit:unit /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Install PHP dependencies first so this layer is cached until composer.json
# or composer.lock changes. --no-dev keeps development tooling out of the
# production image.
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --optimize-autoloader --no-interaction --no-scripts --no-dev

# Then we copy the rest of the app in.
COPY . .

# Bring in the assets compiled in stage 1 (public/build is git-ignored and
# excluded via .dockerignore, so this copy is authoritative).
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev

COPY unit.json /docker-entrypoint.d/unit.json

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

# The entrypoint takes a role argument (web | queue | scheduler). The compose
# file overrides the command for the worker and scheduler services.
ENTRYPOINT ["/entrypoint.sh"]
CMD ["web"]
