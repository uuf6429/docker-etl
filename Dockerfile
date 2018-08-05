FROM php:7.2-zts-alpine

LABEL maintainer="christian@sciberras.me"

COPY . /app
WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN apk update \
 && apk upgrade \
 && apk add --no-cache git zip \
 && export COMPOSER_ALLOW_SUPERUSER=1 \
 && composer install --no-progress --no-interaction --no-dev \
 && chmod +x /app/docker-etl \
 && composer clear-cache \
 && apk del git zip

ENTRYPOINT ["php", "/app/docker-etl"]
