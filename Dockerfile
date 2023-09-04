FROM php:8-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
        libjpeg-turbo-dev \
        libpng-dev \
        icu-dev \
        freetype-dev

RUN docker-php-ext-install pdo pdo_mysql

ENV MUSL_LOCPATH=/usr/local/share/i18n/locales/musl
RUN apk add --update git cmake make musl-dev gcc gettext-dev libintl
RUN cd /tmp && git clone https://gitlab.com/rilian-la-te/musl-locales.git
RUN cd /tmp/musl-locales && cmake . && make && make install

RUN docker-php-ext-install gd
RUN docker-php-ext-install intl
