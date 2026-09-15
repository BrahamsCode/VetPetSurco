FROM php:8.2-apache

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    build-essential \
    openssl libssl-dev \
    libicu-dev \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    default-mysql-client \
    postgresql-client

# Extensiones de PHP. Se dejan los dos controladores (MySQL y PostgreSQL)
# para que la imagen sirva igual en otros proyectos de la empresa; este
# proyecto usa MySQL.
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql mbstring zip exif pcntl bcmath opcache

# intl (necesita libicu-dev)
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl

# gd
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Imagemagick + extension imagick
RUN apt-get install -y imagemagick libmagickwand-dev --no-install-recommends
RUN git clone https://github.com/Imagick/imagick.git /usr/src/php/ext/imagick
RUN docker-php-ext-install imagick
RUN docker-php-ext-enable imagick

# Limpieza de cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# node y npm
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash
RUN apt-get install -y nodejs

# Zona horaria del proyecto: Lima, Peru
ENV TZ America/Lima
RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime && echo ${TZ} > /etc/timezone

# msmtp
RUN apt-get update && apt-get install -y msmtp

# Usuario de apache
ENV APACHE_RUN_USER apache
ENV APACHE_RUN_GROUP apache

RUN groupadd -g 1000 ${APACHE_RUN_USER}
RUN useradd -u 1000 -ms /bin/bash -g ${APACHE_RUN_USER} ${APACHE_RUN_GROUP}

RUN a2enmod rewrite
RUN a2enmod ssl

# Directorio de trabajo: aqui se monta public_html
WORKDIR /var/www

RUN curl -sSL https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 -o mhsendmail \
    && chmod +x mhsendmail \
    && mv mhsendmail /usr/local/bin/mhsendmail
