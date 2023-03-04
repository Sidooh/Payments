FROM composer:2.2 as build

COPY . /app

# TODO: Return --no-dev for production (removed for us to use clockwork in playdooh)
RUN composer install --prefer-dist --optimize-autoloader --no-interaction --ignore-platform-reqs --no-progress

FROM trafex/php-nginx:3.0.0 as production

USER root
RUN apk add --no-cache \
  php81-pdo \
  php81-pdo_mysql \
  php81-tokenizer
USER nobody

# Configure nginx
COPY --from=build /app/docker/nginx/ /etc/nginx/

# Configure supervisord
#COPY --from=build /app/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy project
COPY --chown=nobody --from=build /app /var/www/html

# Cache configs
#RUN php artisan config:cache \
#    && php artisan route:cache \
#    && php artisan event:cache
