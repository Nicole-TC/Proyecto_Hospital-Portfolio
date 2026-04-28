# Imagen oficial de PHP con Apache
FROM php:7.4-apache

# 1. Dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libcurl4-openssl-dev \
    curl \
    git \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mysqli pdo_pgsql pgsql curl

# 2. Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 3. Módulo de reescritura de Apache
RUN a2enmod rewrite

# 4. Directorio de trabajo
WORKDIR /var/www/html

# 5. Permisos básicos para Apache
RUN chown -R www-data:www-data /var/www/html