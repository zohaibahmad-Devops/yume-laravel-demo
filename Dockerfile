FROM php:8.3-cli-alpine

RUN apk add --no-cache sqlite-libs git unzip \
 && apk add --no-cache --virtual .build-deps sqlite-dev \
 && docker-php-ext-install pdo pdo_sqlite \
 && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . .

RUN composer dump-autoload --optimize --no-dev --no-scripts \
 && mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions \
             storage/logs bootstrap/cache database \
 && chmod -R 777 storage bootstrap/cache database

ENV PORT=8080
EXPOSE 8080

CMD ["sh", "/app/docker/start.sh"]
