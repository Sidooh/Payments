FROM composer:2.6 as build

COPY . /app

# TODO: Return --no-dev for production (removed for us to use clockwork in playdooh)
RUN composer install --prefer-dist --optimize-autoloader --no-interaction --ignore-platform-reqs --no-progress --no-dev

FROM trafex/php-nginx:3.5.0 as production

ARG PHP_VERSION=php83

USER root
RUN apk add --no-cache \
  ${PHP_VERSION}-pdo \
  ${PHP_VERSION}-pdo_mysql \
  ${PHP_VERSION}-tokenizer
USER nobody

# Configure nginx
COPY --from=build /app/docker/nginx/ /etc/nginx/

# Configure PHP-FPM
COPY --from=build /app/docker/php/fpm-pool.conf /etc/${PHP_VERSION}/php-fpm.d/www.conf
COPY --from=build /app/docker/php/php.ini /etc/${PHP_VERSION}/conf.d/custom.ini

# Configure supervisord
COPY --from=build /app/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy project
COPY --chown=nobody --from=build /app /var/www/html

# Cache configs
RUN php artisan route:cache \
    && php artisan event:cache
