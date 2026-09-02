FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    curl \
    oniguruma-dev \
    postgresql-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

CMD ["php", "-S", "0.0.0.0:8000", "public/index.php"]
