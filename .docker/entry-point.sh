#!/bin/sh

set -e

php /app/bin/console cache:clear --no-warmup && php /app/bin/console cache:warmup

if [ "${?}" != "0" ]; then
    exit 1;
fi

exec "$@"
