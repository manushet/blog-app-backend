FROM php:8.2.10RC1-fpm

WORKDIR /app

#COPY .bashrc /etc/bash.bashrc
 
# Install linux packages
RUN apt-get update && apt-get install -y --no-install-recommends \
    sudo \
    openssl \
    git \
    zip \
    libzip-dev \
    gnupg2 \
    libicu-dev \
    libpng-dev \
    libc-client-dev \
    libkrb5-dev \
    libjpeg-dev \
    libpq-dev \
    bash-completion \
    nano \
    htop \
    postfix \
    mailutils \
    && rm -r /var/lib/apt/lists/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && composer --version

# Install and configure php extensions
RUN docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-install pdo pdo_pgsql pgsql bcmath gd zip mysqli pdo_mysql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && PHP_OPENSSL=yes docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap \
    && pecl install ast \
    && docker-php-ext-enable ast \
    && docker-php-ext-configure gd --with-jpeg=/usr/include/ --enable-gd \
    && docker-php-ext-install gd \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install sockets

ARG XDEBUG_MODES
ARG REMOTE_HOST="host.docker.internal"
ARG REMOTE_PORT=9003
ARG IDE_KEY="docker"

ENV MODES=$XDEBUG_MODES
ENV CLIENT_HOST=$REMOTE_HOST
ENV CLIENT_PORT=$REMOTE_PORT
ENV IDEKEY=$IDE_KEY
 
RUN git config --global alias.st status
 
# Setup php configs
# RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
# COPY conf.d/*.ini /usr/local/etc/php/conf.d/
# COPY conf.d/dev/* /usr/local/etc/php/conf.d/

COPY ./docker/php/conf.d/*.ini /usr/local/etc/php/conf.d/

ENTRYPOINT ["php-fpm"]