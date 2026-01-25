ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-fpm-alpine AS base

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install zlib-dev and any other required dependencies
RUN apk update && apk add --no-cache \
    zlib-dev \
    libpng libpng-dev \
    freetype-dev \
    jpeg-dev \
    libjpeg-turbo-dev

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

RUN docker-php-ext-install \
  pdo \
  pdo_mysql \
  gd


COPY php/entrypoint/docker-entrypoint.sh /docker-entrypoint.sh
COPY php/entrypoint/docker-entrypoint.d /docker-entrypoint.d

RUN chmod +x /docker-entrypoint.sh \
  && chmod +x /docker-entrypoint.d/*.sh

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["php-fpm"]