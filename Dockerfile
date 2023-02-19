FROM composer:2.2 as build

COPY . /app

# TODO: Return --no-dev for production (removed for us to use clockwork in playdooh)
RUN composer install --prefer-dist --optimize-autoloader --no-interaction --ignore-platform-reqs

FROM php:8.2-buster as production

# Install system libraries
RUN apt-get update -y && apt-get install -y \
    libicu-dev

# Install docker dependencies
RUN docker-php-ext-install mysqli \
    && docker-php-ext-install intl \
    && docker-php-ext-install pdo_mysql

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Define working directory
WORKDIR /home/app

# Copy project
COPY --from=build /app /home/app

# Cache configs
#RUN php artisan config:cache \
#    && php artisan route:cache

# Expose the port
EXPOSE 8080

# Start artisan
CMD php artisan serve --host=0.0.0.0 --port=8080
