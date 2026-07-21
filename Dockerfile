# =========================
# Stage 0: PHP base
# =========================
FROM php:8.4-fpm AS php_base

RUN set -eux; \
  apt-get update; \
  apt-get install -y --no-install-recommends \
  libpng-dev \
  libjpeg-dev \
  libzip-dev \
  zlib1g-dev \
  libfreetype6-dev \
  libpq-dev \
  libonig-dev \
  pkg-config \
  zip \
  unzip \
  netcat-openbsd \
  git \
  curl \
  libxml2-dev \
  supervisor \
  $PHPIZE_DEPS \
  ; \
  pecl install redis; \
  docker-php-ext-enable redis; \
  docker-php-ext-configure gd --with-freetype --with-jpeg; \
  docker-php-ext-install -j"$(nproc)" \
  mbstring \
  exif \
  pcntl \
  bcmath \
  gd \
  zip \
  pdo_mysql \
  pdo_pgsql \
  pgsql \
  ; \
  rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.8.10 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/digisejahtera-main


# =========================
# Stage 1: Composer dependencies
# =========================
FROM php_base AS composer_deps

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
  --no-dev \
  --no-interaction \
  --no-scripts \
  --prefer-dist \
  --no-progress


# =========================
# Stage 2: Vite production assets
# =========================
FROM node:22-alpine AS vite_build

WORKDIR /var/www/digisejahtera-main

COPY package*.json ./
RUN npm install
COPY --from=composer_deps /app/vendor ./vendor
COPY tailwind.config.js postcss.config.js vite.config.js ./
COPY resources ./resources
COPY .env ./.env

RUN npm run build


# =========================
# Stage 3: Laravel PHP-FPM app
# =========================
FROM php_base AS app

WORKDIR /var/www/digisejahtera-main

ARG GIT_HASH=unknown
RUN echo "${GIT_HASH}" > .version

COPY . .
COPY --from=composer_deps /app/vendor ./vendor
COPY --from=vite_build /var/www/digisejahtera-main/public/build ./public/build
COPY --chown=www-data:www-data .docker-secrets/templates/xlsx/template.xlsx ./storage/app/templates/template.xlsx
COPY --chown=www-data:www-data .docker-secrets/templates/xlsx/template-kitir.xlsx ./storage/app/templates/template-kitir.xlsx
COPY --chown=www-data:www-data .docker-secrets/templates/xlsx/shr-template.xlsx ./storage/app/templates/shr-template.xlsx
COPY --chown=www-data:www-data .docker-secrets/templates/xlsx/tagihan-template.xlsx ./storage/app/templates/tagihan-template.xlsx
COPY --chown=www-data:www-data .docker-secrets/templates/xlsx/laporan-jasa-pinjaman-template.xls ./storage/app/templates/laporan-jasa-pinjaman-template.xls
COPY --chown=www-data:www-data .docker-secrets/templates/xlsx/laporan-shu-template.xls ./storage/app/templates/laporan-shu-template.xls

RUN composer dump-autoload \
  --no-dev \
  --optimize \
  --classmap-authoritative
RUN php artisan key:generate --force || true \
  && php artisan view:clear || true \
  && php artisan route:clear || true \
  && php artisan config:clear || true
RUN php artisan config:cache || true \
  && php artisan route:cache || true \
  && php artisan view:cache || true
RUN chown -R www-data:www-data storage bootstrap/cache \
  && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]


# =========================
# Stage 4: Nginx web server
# =========================
FROM nginx:alpine AS web

COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/digisejahtera-main/public /var/www/digisejahtera-main/public

EXPOSE 80