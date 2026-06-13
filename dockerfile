FROM php:8.2-apache

WORKDIR /var/www/symfony

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        intl \
        pdo_mysql \
        zip \
        gd \
        mbstring \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

COPY . .

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

RUN mkdir -p var/cache var/log

RUN chown -R www-data:www-data /var/www/symfony
RUN chmod -R 775 var

EXPOSE 80

CMD ["apache2-foreground"]
