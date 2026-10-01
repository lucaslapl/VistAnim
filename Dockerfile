FROM php:8.4-fpm-alpine

# Extensions requises par Laravel 13 et l'application :
# pdo_mysql (BDD), intl, zip, gd (upload images), bcmath (montants Stripe),
# opcache (performance).
RUN apk add --no-cache \
        bash \
        git \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        nodejs \
        npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        intl \
        zip \
        gd \
        bcmath \
        opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
