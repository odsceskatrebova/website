# Base: PHP + extensions, no app code, no composer
FROM php:8.4-fpm AS base

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libfreetype-dev \
        libwebp-dev \
        libjpeg62-turbo-dev \
        libicu-dev \
        libpng-dev \
        libzip-dev \
        librabbitmq-dev \
        libssh-dev \
        zip \
        unzip \
        imagemagick \
        libmagickwand-dev \
    && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN pecl install apcu amqp redis imagick && \
    docker-php-ext-enable apcu amqp redis imagick opcache && \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install \
        intl \
        pdo \
        pdo_mysql \
        zip \
        bcmath \
        exif \
        gd

WORKDIR /srv



# Build: adds composer, copies app code, installs PHP dependencies
# Also serves as the local dev image — volume mounts in compose.yaml override app code
FROM base AS build

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        git \
        ssh \
    && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_CACHE_DIR=/tmp/composer
ENV APP_ENV=prod APP_DEBUG=0

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

COPY ./docker/php/dev.ini /usr/local/etc/php/conf.d/symfony.ini

ARG USER_UID=1000
ARG USER_GID=1000

RUN groupmod --non-unique --gid ${USER_GID} www-data && \
    usermod --non-unique --uid ${USER_UID} --gid ${USER_GID} www-data && \
    usermod -d /home/www-data www-data && \
    mkdir -p /home/www-data && \
    chown www-data:www-data /home/www-data && \
    chown www-data:www-data /srv

ARG COMPOSER_CONFIG=""
ARG COMPOSER_KEY=""

RUN if [ -n "$COMPOSER_KEY" ]; then \
    mkdir -p /root/.ssh && \
    echo "$COMPOSER_CONFIG" > /root/.ssh/config && \
    echo "$COMPOSER_KEY" > /root/.ssh/id_rsa && \
    chmod 0600 /root/.ssh/id_rsa; \
    fi

COPY bin bin/
COPY config config/
COPY migrations migrations/
COPY public public/
COPY src src/
COPY templates templates/
COPY translations translations/
COPY .env ./
COPY composer.json composer.lock symfony.lock ./

RUN set -eux && \
    composer install --no-dev --prefer-dist --classmap-authoritative --no-interaction --no-ansi && \
    composer clear-cache && \
    chmod +x bin/console



# Node: builds frontend assets
# vendor from build is required by npm pre/postinstall scripts and file: dependencies in package.json
# Only odsct is built here (for production); admin is local-dev only and built via compose.yaml
FROM node:20-bookworm AS node

ARG USER_UID=1000
ARG USER_GID=1000

RUN apt-get update && \
    apt-get install -y --no-install-recommends bash make && \
    rm -rf /var/lib/apt/lists/*

RUN sed -i -e "s/^node:x:[^:]*:/node:x:${USER_UID}:/g" /etc/passwd && \
    sed -i -e "s/^node:x:[^:]*:/node:x:${USER_GID}:/g" /etc/group

WORKDIR /app

COPY --chown=node:node --from=build /srv/vendor vendor/
COPY --chown=node:node assets/odsct assets/odsct/
COPY --chown=node:node assets/common assets/common/
COPY --chown=node:node package.json package-lock.json tsconfig.json tsconfig.node.json vite.config.ts eslint.config.mjs ./
RUN mkdir -p public/build && chown -R node:node /app /home/node

USER node

RUN npm ci && npm run build

# Default: one-shot production build of odsct assets
# In compose.yaml both admin and odsct are watched instead
CMD ["npm", "run", "build"]



# Prod: minimal runtime — only what's needed to serve requests
FROM base AS prod

ENV APP_ENV=prod APP_DEBUG=0

COPY ./docker/php/prod.ini /usr/local/etc/php/conf.d/symfony.ini
COPY ./docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf

COPY --chown=www-data:www-data --from=build /srv /srv
COPY --chown=www-data:www-data --from=node /app/public/build /srv/public/build

USER www-data

RUN set -eux && \
    php bin/console cache:clear --no-warmup && \
    php bin/console cache:warmup

USER root

RUN sync

CMD ["php-fpm"]

