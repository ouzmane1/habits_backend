FROM php:8.2-apache

# Installer les dépendances PHP & outils
RUN apt-get update && apt-get install -y \
    git unzip zip curl libicu-dev libzip-dev libonig-dev \
    && docker-php-ext-install intl zip pdo pdo_mysql

# Activer mod_rewrite
RUN a2enmod rewrite

# Copier le code Symfony dans le conteneur
COPY . /var/www/html/

# Définir le répertoire de travail
WORKDIR /var/www/html/

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Installer les dépendances Symfony
RUN composer install

EXPOSE 80