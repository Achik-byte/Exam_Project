# Gunakan imej asas PHP dengan Apache
FROM php:8.2-apache

# Pasang extensions yang diperlukan untuk MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Fix MPM conflict - disable mpm_event, keep mpm_prefork
RUN a2dismod mpm_event || true
RUN a2enmod mpm_prefork || true

# Aktifkan mod_rewrite untuk .htaccess
RUN a2enmod rewrite

# Tetapkan direktori kerja
WORKDIR /var/www/html

# Salin semua fail projek
COPY . .

# Set kebenaran untuk folder
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80