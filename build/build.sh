#!/bin/bash

image_name="gschafra/swagger-mock"

if [ "$TRAVIS_TAG" != "" ]; then
    version="${TRAVIS_TAG:1}"
fi

echo "Builder docker image ${image_name}..."

docker build --pull --tag "${image_name}" .
if [ "${?}" != "0" ]; then
    exit 1;
fi

docker tag "${image_name}" "${image_name}:latest"
docker tag "${image_name}" "${image_name}:${version}"

docker images
