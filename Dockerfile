FROM php:8.4-fpm

  # Install system packages
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    nginx supervisor curl zip unzip libzip-dev libpq-dev \
    nodejs npm \
    && apt-get clean

  # Install PHP Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

  # PHP extensions
RUN docker-php-ext-install pdo_pgsql zip pcntl

  # Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

  # Set working directory
WORKDIR /var/www/html

  # Copy package info
COPY src/composer.json .
COPY src/composer.lock .
COPY src/package.json .
COPY src/package-lock.json .

  # Install Laravel dependencies
ARG COMPOSER_CACHE_DIR=/dev/null
RUN composer install --no-dev --no-autoloader
RUN npm install --production --no-cache

  # Copy application
COPY src/ .

RUN composer dump-autoload -o
  # Build frontend
RUN npm run build

  # Nginx config
COPY nginx.conf /etc/nginx/nginx.conf

  # Supervisor config
COPY supervisor.conf /etc/supervisor/conf.d/app.conf

  # Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
