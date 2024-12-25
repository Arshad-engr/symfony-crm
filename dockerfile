# Use an official PHP image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/symfony

# Enable Apache modules
RUN a2enmod rewrite

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    pkg-config \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    intl \
    pdo_mysql \
    zip \
    gd \
    mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy Apache configuration
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy application source
COPY . .

# Add a new user
RUN useradd -ms /bin/bash symfony

# Set permissions for the Symfony project
RUN chown -R www-data:www-data /var/www/symfony
RUN chmod -R 775 /var/www/symfony
# Ensure necessary directories are created and permissions are set
RUN mkdir -p /var/www/symfony/var/cache /var/www/symfony/var/log 
RUN chown -R www-data:www-data /var/www/symfony/var
RUN chmod -R 775 /var/www/symfony/var



# Install Symfony dependencies
RUN composer install --optimize-autoloader --no-scripts

# Expose port 80
EXPOSE 80

# Start the application
CMD ["bash", "-c", "until php bin/console doctrine:query:sql 'SELECT 1' > /dev/null 2>&1; do echo 'Waiting for database...'; sleep 5; done; php bin/console doctrine:migrations:migrate --no-interaction && apache2-foreground"]
