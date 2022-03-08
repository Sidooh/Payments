FROM php:8.1-apache

# Install system libraries
RUN apt-get update -y && apt-get install -y \
    build-essential \
    libicu-dev

# Install docker dependencies
RUN apt-get install -y libc-client-dev libkrb5-dev \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install intl \
    && docker-php-ext-install sockets

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

# Expose the port
EXPOSE 8080

# Start artisan
CMD php artisan serve --host=0.0.0.0 --port=8080
