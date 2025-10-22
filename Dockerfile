# Use official PHP 8.2 with Apache (slim version for smaller image)
FROM php:8.2-apache-bullseye

# Install required system libraries
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mbstring exif zip \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite (needed for BookStack)
RUN a2enmod rewrite

# Copy application files
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Adjust permissions for Apache
RUN chown -R www-data:www-data /var/www/html

# Expose the port Render expects
EXPOSE 8080

# Start Apache
CMD ["apache2-foreground"]
