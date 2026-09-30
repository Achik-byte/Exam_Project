# Gunakan imej asas PHP dengan Apache
FROM php:8.2-apache

# Pasang sambungan (extensions) yang diperlukan untuk MySQL dan aplikasi anda
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Aktifkan mod_rewrite Apache (diperlukan untuk .htaccess)
RUN a2enmod rewrite

# Tetapkan direktori kerja
WORKDIR /var/www/html

# Salin semua fail projek anda ke dalam kontena
COPY . .

# Tetapkan kebenaran yang betul untuk folder
RUN chown -R www-data:www-data /var/www/html

# Dedahkan port 80 (port lalai Apache)
EXPOSE 80