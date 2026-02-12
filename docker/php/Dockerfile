FROM php:8.4-fpm

# Sistem bağımlılıklarını yükle
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libxml2-dev \
    libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

# Composer yükle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Çalışma dizini
WORKDIR /var/www/html

# PHP ayarları
RUN echo "memory_limit=256M" > /usr/local/etc/php/conf.d/memory.ini
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini
RUN echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini
RUN echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini
RUN echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini

# Kullanıcı izinleri
RUN chown -R www-data:www-data /var/www/html

USER www-data
