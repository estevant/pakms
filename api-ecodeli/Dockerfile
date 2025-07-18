FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/api

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . .

RUN if [ ! -f .env ]; then cp .env.example .env; fi

RUN mkdir -p /var/www/api/storage/app/public \
    && mkdir -p /var/www/api/storage/framework/{cache,sessions,views} \
    && mkdir -p /var/www/api/bootstrap/cache \
    && ln -sf /var/www/api/storage/app/public /var/www/api/public/storage

RUN chown -R www-data:www-data /var/www/api \
    && chmod -R 755 /var/www/api/storage \
    && chmod -R 755 /var/www/api/bootstrap/cache

RUN php artisan key:generate \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 9000

CMD ["php-fpm"] 