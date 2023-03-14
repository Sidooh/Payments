FROM composer:2.2 as build

COPY . /app

# TODO: Return --no-dev for production (removed for us to use clockwork in playdooh)
RUN composer install --prefer-dist --optimize-autoloader --no-interaction --ignore-platform-reqs --no-progress --no-dev

FROM trafex/php-nginx:3.0.0 as production

USER root
RUN apk add --no-cache \
  php81-pdo \
  php81-pdo_mysql \
  php81-tokenizer
USER nobody

# Configure nginx
COPY --from=build /app/docker/nginx/ /etc/nginx/

# Configure PHP-FPM
COPY --from=build /app/docker/php/fpm-pool.conf /etc/php81/php-fpm.d/www.conf
COPY --from=build /app/docker/php/php.ini /etc/php81/conf.d/custom.ini

# Configure supervisord
COPY --from=build /app/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy project
COPY --chown=nobody --from=build /app /var/www/html

# Cache configs
RUN php artisan route:cache \
    && php artisan event:cache
