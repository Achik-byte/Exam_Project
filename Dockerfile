FROM webdevops/php-apache:8.2

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

# Copy semua fail
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Set kebenaran
RUN chown -R application:application /app

EXPOSE 80