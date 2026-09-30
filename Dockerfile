FROM php:8.2-apache

# Fix MPM conflict
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* \
          /etc/apache2/mods-enabled/mpm_worker.* \
          /etc/apache2/mods-enabled/mpm_itk.* && \
    ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load && \
    ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

# Pasang PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable rewrite untuk .htaccess
RUN a2enmod rewrite

# Tukar Apache listen port - guna PORT env variable atau default 80
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf && \
    sed -i 's/:80/:${PORT}/g' /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy fail projek
COPY . .

# Set kebenaran
RUN chown -R www-data:www-data /var/www/html

# Start Apache
CMD ["apache2-foreground"]