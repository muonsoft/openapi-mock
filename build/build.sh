#!/bin/bash

image_name="swaggermock/swagger-mock"

if [ "$TRAVIS_TAG" != "" ]; then
    image_name="$image_name:$TRAVIS_TAG"
fi

docker build --pull --tag "swaggermock/swagger-mock" .
if [ "${?}" != "0" ]; then
    exit 1;
fi

docker images
