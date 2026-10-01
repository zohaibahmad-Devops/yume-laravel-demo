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

# Hugging Face Spaces runs the container as uid 1000, so everything the app
# writes at boot — .env, the SQLite file, compiled views — has to be writable
# by that user. /app itself is included because .env is created there.
RUN composer dump-autoload --optimize --no-dev --no-scripts \
 && mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions \
             storage/logs bootstrap/cache database \
 && chmod -R 777 storage bootstrap/cache database \
 && chmod 777 /app

ENV PORT=7860
EXPOSE 7860

CMD ["sh", "/app/docker/start.sh"]
