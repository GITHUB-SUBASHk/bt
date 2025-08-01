FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# ✅ Copy only composer files first
COPY composer.json composer.lock ./

# ✅ Install PHP dependencies early
RUN composer install --no-dev --optimize-autoloader

# ✅ Now copy the full Laravel app
COPY . .

# Generate Laravel key and config (will now succeed)
RUN php artisan key:generate
RUN php artisan config:cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
