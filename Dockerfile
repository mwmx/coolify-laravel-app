FROM mwmx/devriglaravelbase:003

WORKDIR /var/www/html

RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache

RUN chown -R unit:unit /var/www/html/storage bootstrap/cache && chmod -R 775 /var/www/html/storage

RUN chown -R unit:unit storage bootstrap/cache && chmod -R 775 storage bootstrap/cache

COPY composer.json composer.lock ./
RUN composer install --prefer-dist --optimize-autoloader --no-interaction --no-scripts

COPY . .
RUN composer dump-autoload --optimize



COPY unit.json /docker-entrypoint.d/unit.json



COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

CMD ["/entrypoint.sh"]
