FROM php:8.4-fpm AS php_base

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    zip \
    unzip \
    netcat-openbsd \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    supervisor \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

COPY --from=composer:2.8.10 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/digisejahtera

FROM node:22-alpine AS vite_build
WORKDIR /var/www/digisejahtera
COPY package*.json ./
RUN npm install
COPY tailwind.config.js postcss.config.js vite.config.js ./
COPY resources ./resources
RUN npm run build


FROM php_base AS app

WORKDIR /var/www/digisejahtera

ARG GIT_HASH

RUN echo ${GIT_HASH} > .version

COPY . .

COPY --from=vite_build /var/www/digisejahtera/public/build ./public/build


RUN composer install --no-scripts --no-dev --prefer-dist --no-interaction --optimize-autoloader

RUN php artisan key:generate --force
RUN php artisan view:clear
RUN php artisan route:clear
RUN php artisan config:clear

RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

COPY entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]

EXPOSE 9000
CMD ["php-fpm"]