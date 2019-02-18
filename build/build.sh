#!/bin/bash

image_name="swaggermock/swagger-mock"

if [ "$TRAVIS_TAG" != "" ]; then
    version="${TRAVIS_TAG:1}"
    image_name="$image_name:$version"
fi

echo "Builder docker image $image_name..."

docker build --pull --tag "$image_name" .
if [ "${?}" != "0" ]; then
    exit 1;
fi

docker images
