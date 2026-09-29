# ========================================================
# Dockerfile Multi-Stage de Producción - SIS-APAFA V11
# Alta Disponibilidad, Concurrencia y Máximo Rendimiento
# ========================================================

FROM php:8.2-fpm-alpine as base

# 1. Instalar dependencias del sistema y extensiones de PHP necesarias
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    supervisor \
    nginx \
    mysql-client \
    oniguruma-dev \
    icu-dev

# 2. Configurar y compilar extensiones PHP nativas optimizadas
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        opcache

# 3. Instalar Redis para soporte de colas y sesiones de alta concurrencia
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# 4. Instalar Composer oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 5. Directorio de trabajo
WORKDIR /var/www/html

# 6. Copiar dependencias de Composer para aprovechar la caché de Docker
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# 7. Copiar código fuente de la aplicación
COPY . .

# 8. Generar autoloader optimizado
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# 9. Configuración optimizada de OPcache para producción
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.save_comments=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# 10. Permisos adecuados para www-data
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
