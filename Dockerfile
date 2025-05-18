# Use the official PHP image with Apache server
FROM php:8.2-apache

# Copy project files into the Apache server root
COPY . /var/www/html/

# Set file permissions (optional but recommended)
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 (default web port)
EXPOSE 80
