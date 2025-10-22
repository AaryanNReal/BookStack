FROM php:8.2-apache-bullseye

# ---- System packages ----
RUN apt-get update && apt-get install -y \
    pkg-config \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip unzip git curl \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) gd mbstring pdo_mysql exif zip \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

# ---- Copy application ----
WORKDIR /var/www/html
COPY . /var/www/html

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080
CMD ["apache2-foreground"]
