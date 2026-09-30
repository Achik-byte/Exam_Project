FROM php:8.2-apache

# Fix MPM - disable event, use prefork
RUN a2dismod mpm_event || true
RUN a2dismod mpm_worker || true
RUN a2dismod mpm_itk || true
RUN a2enmod mpm_prefork

# Pasang PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable rewrite untuk .htaccess
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy fail projek
COPY . .

# Set kebenaran
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80