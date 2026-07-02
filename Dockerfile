FROM php:8.1-fpm as build

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    unzip curl git \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN rm -rf vendor && COMPOSER_POLICY_ADVISORIES_BLOCK=false composer install --no-dev --optimize-autoloader --no-interaction

RUN cp .env.example .env \
    && php artisan storage:link

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && npm install --ignore-scripts \
    && npm run build \
    && rm -rf node_modules

FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    nginx supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

COPY --from=build /var/www /var/www
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/laravel.conf
COPY docker/start.sh /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh \
    && mkdir -p /var/log/supervisor

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]
