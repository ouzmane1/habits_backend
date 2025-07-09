FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    libpq-dev \         
    && docker-php-ext-install intl zip pdo pdo_mysql \
    && docker-php-ext-install pdo_pgsql \  
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

RUN a2enmod rewrite

COPY composer.json composer.lock /var/www/html/

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html/

COPY . /var/www/html/

RUN composer install --optimize-autoloader

EXPOSE 80
