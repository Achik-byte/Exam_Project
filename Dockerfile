FROM php:8.2-apache

# Fix MPM conflict - cara paksa (delete semua MPM, then set prefork)
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* \
          /etc/apache2/mods-enabled/mpm_worker.* \
          /etc/apache2/mods-enabled/mpm_itk.* && \
    ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load && \
    ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

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