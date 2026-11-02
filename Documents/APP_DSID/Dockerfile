FROM php:8.3-fpm

# Dépendances système
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl unzip libpq-dev libzip-dev libpng-dev libonig-dev \
    libxml2-dev libfreetype6-dev libjpeg62-turbo-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql mbstring zip exif bcmath gd && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
