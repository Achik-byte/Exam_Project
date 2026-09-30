FROM webdevops/php-apache:8.2

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Allow Composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Set working directory
WORKDIR /app

# Copy SEMUA fail projek dulu
COPY . .

# Baru run composer install (selepas semua fail ada)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Set kebenaran
RUN chown -R application:application /app

EXPOSE 80