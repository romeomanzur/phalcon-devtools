ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-fpm-bookworm

ARG PHP_VERSION

ENV PATH=/root/.composer/vendor/bin:/app/vendor/bin:/app/bin:/app:${PATH} \
    PHP_VERSION=${PHP_VERSION}

LABEL description="Docker image for Phalcon DevTools 6 development" \
    maintainer="Phalcon Team team@phalcon.io" \
    vendor="Phalcon" \
    name="com.phalconphp.devtools.php-${PHP_VERSION}"

# System packages and PHP extensions required by DevTools/tests.
RUN set -eux; \
    apt-get update; \
    apt-get install --no-install-recommends -y \
    ca-certificates \
    curl \
    git \
    htop \
    libicu-dev \
    libonig-dev \
    libpq-dev \
    libxml2-dev \
    libzip-dev \
    mc \
    nano \
    unzip \
    zip; \
    \
    docker-php-ext-install -j"$(nproc)" \
    gettext \
    intl \
    mbstring \
    mysqli \
    opcache \
    pdo_mysql \
    pdo_pgsql \
    shmop \
    xml \
    zip; \
    \
    rm -rf /var/lib/apt/lists/*

# Keep the port expected by the existing Nginx configuration.
RUN set -eux; \
    sed -ri 's!^listen = .*!listen = 8000!' /usr/local/etc/php-fpm.d/www.conf; \
    sed -ri 's!^;?clear_env = .*!clear_env = no!' /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

EXPOSE 8000

CMD ["php-fpm"]