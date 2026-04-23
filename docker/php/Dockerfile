FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libssl-dev \
    pkg-config \
    build-essential \
    autoconf \
    zlib1g-dev \
    libbrotli-dev

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    sockets \
    pcntl

# 👇 FORÇANDO CONFIGURAÇÃO ESTÁVEL
RUN pecl install swoole-5.1.3 \
    && docker-php-ext-enable swoole

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

ENV COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 9501