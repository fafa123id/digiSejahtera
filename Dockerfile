FROM php:8.4-fpm

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        zip \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        libpq-dev \
        libonig-dev \
        pkg-config \
        netcat-openbsd \
        postgresql-client \
        $PHPIZE_DEPS \
    ; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install \
        bcmath \
        exif \
        gd \
        mbstring \
        pcntl \
        zip \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
    ; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/digisejahtera

CMD ["php-fpm"]