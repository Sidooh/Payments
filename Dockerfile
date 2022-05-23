FROM php:8.1-fpm

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
    nginx \
    git

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

# Download composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy code to /home/app
COPY --chown=www:www-data . /home/app

# add root to app group
RUN chmod -R ug+w /home/app/storage

# Copy nginx/php/supervisor configs
RUN cp docker/supervisor.conf /etc/supervisord.conf
RUN cp docker/php.ini /usr/local/etc/php/conf.d/app.ini
RUN cp docker/nginx.conf /etc/nginx/sites-enabled/default

# PHP Error Log Files
RUN mkdir /var/log/php
RUN touch /var/log/php/errors.log && chmod 777 /var/log/php/errors.log

# Deployment steps
RUN composer install --optimize-autoloader --no-dev
RUN chmod +x /home/app/docker/run.sh

# Expose the port
EXPOSE 80
ENTRYPOINT ["/home/app/docker/run.sh"]
