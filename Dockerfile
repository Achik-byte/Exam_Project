FROM webdevops/php-apache:8.2

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files dulu
COPY composer.json composer.lock* ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy semua fail projek
COPY . .

# Set kebenaran
RUN chown -R application:application /app

EXPOSE 80