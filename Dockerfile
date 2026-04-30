FROM php:8.2-apache

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

# Copy app
COPY . /var/www/html/

# Set proper permissions for uploads
RUN chown -R www-data:www-data /var/www/html/uploads || true

EXPOSE 80
