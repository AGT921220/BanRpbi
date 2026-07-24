FROM php:8.4-fpm

ARG INSTALL_XDEBUG=false

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    nano \
    default-mysql-client \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libpq-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mysqli \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        soap \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && if [ "$INSTALL_XDEBUG" = "true" ]; then \
        pecl install xdebug && docker-php-ext-enable xdebug ; \
    fi \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["php-fpm"]