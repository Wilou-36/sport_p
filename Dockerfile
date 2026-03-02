# ---------- Base PHP ----------
FROM php:8.2-cli-alpine

# ---------- Install system deps ----------
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    postgresql-dev

# ---------- Install PHP extensions ----------
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    zip

# ---------- Install Composer ----------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------- Working directory ----------
WORKDIR /app

# ---------- Copy project ----------
COPY . .

# ---------- Install Symfony dependencies (production) ----------
RUN rm -rf var/cache/*

RUN composer install --no-dev --optimize-autoloader --no-scripts

# Clear & warmup cache en production
RUN php bin/console cache:clear --env=prod
RUN php bin/console cache:warmup --env=prod
# ---------- Ensure var directory exists ----------
RUN mkdir -p var && chmod -R 777 var

# ---------- Force production mode inside container ----------
ENV APP_ENV=prod
ENV APP_DEBUG=0

# ---------- Prevent .env crash ----------
RUN touch .env

# ---------- Expose Render port ----------
EXPOSE 10000

# ---------- Start PHP server ----------
CMD php -d variables_order=EGPCS -S 0.0.0.0:$PORT -t public