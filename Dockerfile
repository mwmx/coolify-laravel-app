# This docker image is defined in docker/Dockerfile and is pushed to docker
# hub and its tag incremented whenever it's changed.
FROM mwmx/devriglaravelbase:003

WORKDIR /var/www/html

RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache

RUN chown -R unit:unit /var/www/html/storage bootstrap/cache && chmod -R 775 /var/www/html/storage

RUN chown -R unit:unit storage bootstrap/cache && chmod -R 775 storage bootstrap/cache

# By using a step to copy composer.json and composer.lock followed by a step
# to run composer install, we can effectively save the time it takes to
# download and install dependencies on each rebuild.  If either composer.json
# or composer.lock changes then this cache is thrown away.
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --optimize-autoloader --no-interaction --no-scripts

# Then we copy the rest of the app in.
COPY . .
RUN composer dump-autoload --optimize

COPY unit.json /docker-entrypoint.d/unit.json

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

CMD ["/entrypoint.sh"]
