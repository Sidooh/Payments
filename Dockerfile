FROM php:8.1-apache

# Install system libraries
RUN apt-get update -y && apt-get install -y \
    build-essential \
    libicu-dev \
    zlib1g-dev \
    libmemcached-dev \
    zip \
    unzip

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

# Download composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Define working directory
WORKDIR /home/app

# Copy project
COPY . /home/app

# Run composer install && update
RUN composer install
RUN php /home/app/artisan queue:work --verbose --sleep=3 --tries=3 &

# Expose the port
EXPOSE 8080

# Start artisan
CMD php artisan serve --host=0.0.0.0 --port=8080
