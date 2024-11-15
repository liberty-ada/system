FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --allow-downgrades --allow-remove-essential --allow-change-held-packages \
        wget \
        curl \
        git \
        libxml2-dev \
        libssl-dev \
        libzip-dev \
    && docker-php-ext-install phar \
    && docker-php-ext-install simplexml \
    && docker-php-ext-install zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /vat/tmp/*

COPY ./conf/custom.ini /usr/local/etc/php/conf.d/custom.ini
