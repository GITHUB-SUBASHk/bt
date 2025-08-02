FROM php:8.2-fpm

# Install system dependencies and SQLite
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql mbstring zip exif pcntl

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy app source code
COPY . .

# Create required folders and database file
RUN mkdir -p storage bootstrap/cache database \
    && touch database/database.sqlite

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 storage bootstrap/cache database/database.sqlite

# Copy .env if not already present
RUN cp .env.example .env || true

# Laravel setup
RUN composer install --no-dev --optimize-autoloader
RUN php artisan migrate --force
RUN php artisan config:cache || true
RUN php artisan route:cache || true

# Expose Laravel port
EXPOSE 8000

# Start Laravel development server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
