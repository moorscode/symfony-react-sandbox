FROM eu.gcr.io/myonlinestore-dev/php-dev-fpm:8.2-alpine-multiarch

ARG TARGETPLATFORM

EXPOSE 8000

WORKDIR /srv/app
VOLUME ["/srv/app"]

RUN apk upgrade
RUN apk add --no-cache --virtual .build-deps ${PHPIZE_DEPS}
RUN apk --no-cache add \
    bash \
    mysql-client \
    linux-headers \
    opendkim opendkim-utils \
    tidyhtml=5.8.0-r2 \
    libstdc++ \
    jpegoptim \
    exiftool \
    optipng \
    librsvg

RUN docker-php-ext-enable \
    bcmath \
    exif \
    gd \
    gmp \
    igbinary \
    intl \
    mysqli \
    opcache \
    pcntl \
    pdo_pgsql \
    pdo_mysql \
    rdkafka \
    redis \
    soap \
    sodium \
    sockets \
    tidy \
    zip
RUN apk del --no-cache --no-network .build-deps

# Copy dependencies to container, second one being optional
# COPY 90-myonlinestore.ini 91-myonlinestore.local.ini* ${PHP_INI_DIR}/conf.d/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY --from=node:18-alpine /usr/local/ /opt/node
COPY --from=node:18-alpine /opt/ /opt/

ARG APP_ENV="dev"

ENV APP_ENV="${APP_ENV}" \
    # For NodeJS
    PATH="${PATH}:/opt/node/bin" \
    HOME="/tmp"

# Configure xdebug if required
#ARG DEBUG="0"
#RUN $(if [ "1" = "${DEBUG}" ]; then echo docker-php-ext-enable xdebug; fi)
