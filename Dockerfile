FROM composer:2 AS composer

FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql zip gd \
    && rm -rf /var/lib/apt/lists/*

RUN echo "upload_max_filesize=10M\npost_max_size=12M" > /usr/local/etc/php/conf.d/uploads.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

CMD ["sh", "-c", "composer install --no-interaction --prefer-dist && php -S 0.0.0.0:8000 -t public"]
