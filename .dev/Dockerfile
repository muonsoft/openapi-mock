FROM swaggermock/swagger-mock

ENV APP_ENV=dev \
    COMPOSER_HOME=/var/run/composer \
    XDEBUG_CONFIG="remote_enable=1 remote_mode=req remote_port=9000 remote_host=172.17.0.1" \
    PHP_IDE_CONFIG="serverName=default"

RUN set -xe \
    && apk add --update \
        $PHPIZE_DEPS \
        nano \
        iputils \
        bash \
        curl \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && chmod -R 0777 $COMPOSER_HOME

COPY ./files /

EXPOSE 8080 9000
