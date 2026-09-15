# Imagen de la aplicación VetPet Connect (PHP 8.2 + FPM).
# El contrato del proyecto fija PHP 8.2: no cambiar esta etiqueta.
FROM php:8.2-fpm

# --- Dependencias del sistema -------------------------------------------------
# Solo lo necesario para compilar las extensiones y para que Composer descomprima.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# --- Extensiones de PHP que necesitan Laravel 12 y este proyecto ---------------
# gd se configura antes de compilarla para incluir freetype y jpeg.
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# mbstring viene compilada dentro de la imagen oficial de PHP (--enable-mbstring),
# así que se comprueba su presencia en lugar de reinstalarla (reinstalarla provoca
# avisos de "Module already loaded" en cada ejecución de php).
RUN php -m | grep -qx mbstring

# --- Ajustes de PHP ------------------------------------------------------------
# El mismo archivo que compose.yaml monta en esta ruta, para que la imagen
# funcione igual con y sin montaje.
COPY docker/php/php.ini /usr/local/etc/php/conf.d/vetpet.ini

# --- Composer 2 desde la imagen oficial ---------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- Usuario no root para la aplicación ---------------------------------------
# uid/gid 1000 para que los archivos creados coincidan con el usuario habitual
# del host cuando se monta el proyecto como volumen.
RUN groupadd --gid 1000 vetpet \
    && useradd --uid 1000 --gid vetpet --shell /bin/bash --create-home vetpet \
    && sed -i 's/^user = www-data/user = vetpet/; s/^group = www-data/group = vetpet/' \
        /usr/local/etc/php-fpm.d/www.conf \
    && mkdir -p /var/www/html \
    && chown -R vetpet:vetpet /var/www/html

WORKDIR /var/www/html

# --- Caché de dependencias -----------------------------------------------------
# Copiar primero composer.json/composer.lock permite reutilizar la capa de
# vendor mientras esas dos definiciones no cambien.
COPY --chown=vetpet:vetpet composer.json composer.lock ./

USER vetpet

RUN composer install \
        --no-interaction \
        --prefer-dist \
        --no-scripts \
        --no-autoloader

# --- Código de la aplicación ---------------------------------------------------
COPY --chown=vetpet:vetpet . .

RUN composer dump-autoload --optimize --no-interaction

# --- Arranque ------------------------------------------------------------------
# entrypoint.sh espera a MySQL, aplica las migraciones y cede el control a CMD.
COPY --chown=vetpet:vetpet docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
