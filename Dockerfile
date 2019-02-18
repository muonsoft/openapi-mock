# Composer dependencies
FROM composer as vendor

COPY composer.json composer.json
COPY composer.lock composer.lock
COPY symfony.lock symfony.lock
COPY src/ src/

RUN set -xe \
    && composer install \
        --ignore-platform-reqs \
        --no-dev \
        --no-interaction \
        --no-plugins \
        --no-scripts \
        --prefer-dist \
    && composer dump-autoload \
        --optimize \
        --no-dev \
        --classmap-authoritative

# Main image
FROM php:7.2-alpine
LABEL maintainer="Igor Lazarev <strider2038@yandex.ru>"

ARG ROADRUNNER_VERSION=1.3.5

ENV APP_ENV=prod \
    SWAGGER_MOCK_SPECIFICATION_URL='' \
    SWAGGER_MOCK_LOG_LEVEL='warning' \
    SWAGGER_MOCK_CACHE_DIRECTORY='/dev/shm/openapi-cache' \
    SWAGGER_MOCK_CACHE_TTL='0' \
    SWAGGER_MOCK_CACHE_STRATEGY='disabled'

WORKDIR /app

COPY ./.docker /
COPY . /app
COPY --from=vendor /app/vendor/ /app/vendor/

RUN set -xe \
    && wget -O /tmp/rr.tar.gz "https://github.com/spiral/roadrunner/releases/download/v$ROADRUNNER_VERSION/roadrunner-$ROADRUNNER_VERSION-linux-amd64.tar.gz" \
    && tar -xzvf /tmp/rr.tar.gz -C /tmp \
    && rm -rf /tmp/rr.tar.gz \
    && cp "/tmp/roadrunner-$ROADRUNNER_VERSION-linux-amd64/rr" /usr/local/bin/rr \
    && rm -rf "/tmp/roadrunner-$ROADRUNNER_VERSION-linux-amd" \
    && docker-php-ext-install \
        sockets \
        opcache \
    && docker-php-ext-enable \
       sockets \
       opcache \
   && chmod +x /entry-point.sh

EXPOSE 8080

ENTRYPOINT [ "/entry-point.sh" ]
CMD ["/usr/local/bin/rr", "serve", "-d", "-c", "/app/road-runner.dist.yaml"]
