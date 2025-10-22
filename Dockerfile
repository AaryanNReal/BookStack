# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# Install system packages that PHP extensions need
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zip unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif gd zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Give Apache permission to write
RUN chown -R www-data:www-data /var/www/html

# Expose the web port
EXPOSE 8080

# Start Apache
CMD ["apache2-foreground"]
