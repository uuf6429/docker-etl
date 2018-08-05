FROM php:7.2-cli-alpine

COPY . /app
WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER 1
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN apk update \
 && apk upgrade \
 && apk add --no-cache git zip \
 && composer install --no-progress --no-interaction --no-dev \
 && chmod +x /app/docker-etl

ENTRYPOINT ["php", "/app/docker-etl"]
