FROM php:8.2-apache-bullseye

# Install required system packages
RUN apt-get update && apt-get install -y \
    pkg-config \
    libonig-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip unzip git curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mbstring pdo_mysql exif zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html
COPY . /var/www/html

# Install PHP dependencies with Composer
RUN composer install --no-dev --optimize-autoloader

# Fix Apache document root and permissions
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf \
 && { \
      echo '<Directory "/var/www/html/public">'; \
      echo 'Options Indexes FollowSymLinks'; \
      echo 'AllowOverride All'; \
      echo 'Require all granted'; \
      echo '</Directory>'; \
      echo '<Directory "/var/www/html">'; \
      echo 'Options Indexes FollowSymLinks'; \
      echo 'AllowOverride All'; \
      echo 'Require all granted'; \
      echo '</Directory>'; \
    } > /etc/apache2/conf-available/bookstack.conf \
 && a2enconf bookstack \
 && a2enmod rewrite \
 && chown -R www-data:www-data /var/www/html

EXPOSE 8080
CMD ["apache2-foreground"]
