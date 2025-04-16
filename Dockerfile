FROM php:8.1-cli-alpine

# Install necessary extensions
RUN docker-php-ext-install pcntl

# Install Git
RUN apk add --no-cache git

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /app

# Copy application code
COPY . /app

# Install dependencies
RUN composer install
