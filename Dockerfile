FROM php:8.2-cli-alpine

RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    postgresql-dev

RUN docker-php-ext-install pdo pdo_pgsql intl zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-scripts

RUN chmod -R 777 var

EXPOSE 10000

CMD php -S 0.0.0.0:$PORT -t public