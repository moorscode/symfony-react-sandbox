FROM eu.gcr.io/myonlinestore-dev/php-dev-fpm:8.2-alpine-multiarch
ARG TARGETPLATFORM

WORKDIR /srv/app
VOLUME ["/srv/app"]

RUN apk upgrade
RUN apk add --no-cache --virtual .build-deps ${PHPIZE_DEPS}
RUN apk --no-cache add \
    bash \
    linux-headers \
    opendkim opendkim-utils \
    libstdc++
RUN apk del --no-cache --no-network .build-deps

# Copy dependencies to container, second one being optional
COPY --from=node:18-alpine /usr/local/ /opt/node
COPY --from=node:18-alpine /opt/ /opt/

ARG APP_ENV="dev"
ENV APP_ENV="${APP_ENV}" \
    # For NodeJS
    PATH="${PATH}:/opt/node/bin" \
    HOME="/tmp"
