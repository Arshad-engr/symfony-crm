FROM php:8.2-apache

WORKDIR /var/www/symfony

# System dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl \
    libicu-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl pdo_mysql zip gd mbstring \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Install Composer PROPERLY (fix)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer

# Apache config
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# App code
COPY . .

# Install dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-interaction

# Permissions
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data /var/www/symfony \
    && chmod -R 775 var

EXPOSE 80

CMD ["apache2-foreground"]
