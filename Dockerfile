FROM php:8.3-alpine
# Install dependencies
RUN apk update \
 && apk add --no-cache unzip libzip-dev zip \
 && apk add --no-cache --virtual .build-deps \
    autoconf \
    g++ \
    make \
 && apk del .build-deps
RUN docker-php-ext-install -j$(nproc)  \
    bcmath \
    exif \
    opcache \
    pcntl \
    pdo_mysql \
    zip
# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
# 複製專案
COPY . .
COPY .env.example .env

RUN composer install --no-dev --prefer-dist --optimize-autoloader \
 && php artisan key:generate \
 && php artisan event:cache \
 && php artisan route:cache \
 && php artisan view:cache

CMD ["php", "artisan", "serve", "--host", "0.0.0.0"]
