FROM php:fpm-alpine
LABEL maintainer="Igor Lazarev <strider2038@yandex.ru>"

ENV APP_ENV=prod \
    SPECIFICATION_URL=''

RUN set -xe \
    && apk --no-cache add --update \
        nginx \
        supervisor \
    && rm -rf /usr/local/etc/php-fpm.d/* \
    && mkdir -p /var/run

WORKDIR /app

COPY .docker/ /
COPY . /app

RUN php /app/bin/console cache:clear \
    && php /app/bin/console cache:warmup

EXPOSE 80

CMD ["/usr/bin/supervisord", "--configuration", "/etc/supervisord.conf"]
