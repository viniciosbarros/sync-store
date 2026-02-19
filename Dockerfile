# builder
FROM composer:latest AS builder

WORKDIR /app

COPY composer.json composer.lock* ./

ARG INSTALL_DEV=true
RUN if [ "$INSTALL_DEV" = "true" ]; then \
        composer install --no-scripts --no-autoloader --prefer-dist; \
    else \
        composer install --no-dev --no-scripts --no-autoloader --prefer-dist; \
    fi

COPY . .

RUN composer dump-autoload --optimize

# imagem app
FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libzip-dev \
    && docker-php-ext-install pdo_sqlite zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=builder /app /var/www/html

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
