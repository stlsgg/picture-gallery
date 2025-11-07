FROM php:8.4-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
  zip \
  unzip \
  libzip-dev \
  libpng-dev \
  libjpeg-dev \
  libwebp-dev \
  libfreetype6-dev \
  libonig-dev \
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-webp --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) gd mbstring zip

COPY --from=composer:2.6 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

RUN usermod -u 1000 www-data || true


