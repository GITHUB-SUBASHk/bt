FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql mbstring zip exif pcntl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN mkdir -p storage bootstrap/cache database \
    && touch database/database.sqlite

RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 storage bootstrap/cache database/database.sqlite

RUN cp .env.example .env || true

RUN composer install --no-dev --optimize-autoloader

RUN php artisan migrate --force
# Optional — only if APP_KEY is not injected via env
RUN php artisan key:generate
RUN php artisan config:cache || true
RUN php artisan route:cache || true

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
