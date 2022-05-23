FROM php:8.1-apache

# Define working directory
WORKDIR /home/app

# Install system libraries
RUN apt-get update -y && apt-get install -y \
    build-essential \
    libicu-dev \
    zlib1g-dev \
    libmemcached-dev \
    zip \
    unzip \
    nginx

# Install supervisor
RUN apt-get install -y supervisor

# Install docker dependencies
RUN apt-get install -y libc-client-dev libkrb5-dev \
    && pecl install memcached-3.1.5 \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install intl \
    && docker-php-ext-install sockets \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-enable memcached

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Download & Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy project
COPY . /home/app

# Copy nginx/php/supervisor configs
RUN cp docker/supervisor.conf /etc/supervisord.conf
RUN cp docker/php.ini /usr/local/etc/php/conf.d/app.ini
RUN cp docker/nginx.conf /etc/nginx/sites-enabled/default

# PHP Error Log Files
RUN mkdir /var/log/php
RUN touch /var/log/php/errors.log && chmod 777 /var/log/php/errors.log

# Run composer install && update
RUN composer install

# Run laravel queue worker
RUN /home/app/artisan queue:work --tries=3

# Expose the port
EXPOSE 8080

# Start artisan
CMD php artisan serve --host=0.0.0.0 --port=8080

# Expose the port
#EXPOSE 80
#ENTRYPOINT ["/home/app/docker/run.sh"]
