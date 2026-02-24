ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-fpm-alpine AS base

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN docker-php-ext-install \
  pdo \
  pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /tmp/pawn-docgen
COPY www/composer.json .
RUN composer install --no-dev --optimize-autoloader && mv vendor ../www/

COPY docker/php/entrypoint/docker-entrypoint.sh /docker-entrypoint.sh
COPY docker/php/entrypoint/docker-entrypoint.d /docker-entrypoint.d

RUN chmod +x /docker-entrypoint.sh \
  && chmod +x /docker-entrypoint.d/*.sh

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["php-fpm"]