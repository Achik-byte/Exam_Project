# Tukar base image untuk paksa Railway rebuild (bukan guna cache lama)
FROM webdevops/php-apache:8.2

# Copy fail projek
COPY . /app

# Set working directory
WORKDIR /app

# Set kebenaran
RUN chown -R application:application /app

EXPOSE 80