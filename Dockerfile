FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Create missing Laravel required folders
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache

# Copy app source code
COPY . .

# Set folder permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy .env if not already present
RUN cp .env.example .env || true

# Laravel commands
RUN php artisan key:generate
RUN php artisan config:cache || true
RUN php artisan route:cache || true

# Expose Laravel development server port
EXPOSE 8000

# Start Laravel dev server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
