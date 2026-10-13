# ---------------------------------------------------------------------------
# Imagen de produccion para Render.
#
# Se diferencia de docker/dockerfile/Dockerfile en tres cosas:
#   - copia el codigo dentro de la imagen en vez de montarlo como volumen,
#   - instala las dependencias sin las de desarrollo y compila el autoload,
#   - Apache escucha en el puerto que Render inyecta como $PORT.
# ---------------------------------------------------------------------------
FROM php:8.2-apache

ENV TZ=America/Lima \
    APP_ENV=production \
    APP_DEBUG=false \
    COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip zip libzip-dev libicu-dev libpq-dev libonig-dev \
        libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql pdo_pgsql pgsql mbstring zip exif bcmath opcache intl gd \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Primero las dependencias: si no cambian, Docker reutiliza esta capa.
COPY public_html/composer.json public_html/composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --no-progress

COPY public_html/ ./
RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

# La raiz publica es public/, nunca la carpeta del proyecto: asi el .env
# jamas queda accesible por HTTP.
RUN printf '%s\n' \
    'ServerTokens Prod' \
    'ServerSignature Off' \
    'ServerName localhost' \
    '<VirtualHost *:${PORT}>' \
    '    DocumentRoot /var/www/html/public' \
    '    <Directory /var/www/html/public>' \
    '        AllowOverride All' \
    '        Require all granted' \
    '        Options -Indexes +FollowSymLinks' \
    '    </Directory>' \
    '    <FilesMatch "^\.">' \
    '        Require all denied' \
    '    </FilesMatch>' \
    '    ErrorLog /dev/stderr' \
    '    CustomLog /dev/stdout combined' \
    '</VirtualHost>' \
    > /etc/apache2/sites-available/000-default.conf

COPY docker/render/arranque.sh /usr/local/bin/arranque.sh
RUN chmod +x /usr/local/bin/arranque.sh

EXPOSE 10000
CMD ["/usr/local/bin/arranque.sh"]
