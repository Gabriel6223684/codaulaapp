# PHP estável e suportado
FROM php:8.5.2-fpm-alpine3.23

ENV TZ=America/Sao_Paulo \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1

# Dependências de sistema
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    tzdata \
    icu-dev \
    postgresql-dev \
    oniguruma-dev \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev

# Extensões PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql \
        intl \
        mbstring \
        gd \
        zip

# PHP config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Cache inteligente do Docker
COPY composer.json ./

# Diagnóstico explícito (mantém visibilidade)
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader

# Código da aplicação
COPY . .

EXPOSE 9000
CMD ["php-fpm"]
