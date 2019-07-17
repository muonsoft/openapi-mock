#!/bin/bash

image_name="gschafra/swagger-mock"

if [ "$TRAVIS_TAG" != "" ]; then
    version="${TRAVIS_TAG:1}"
fi

echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
echo "Deploying image ${image_name} to DockerHub..."

docker push "${image_name}"
if [ "${?}" != "0" ]; then
    exit 1;
fi

docker push "${image_name}:$version"
if [ "${?}" != "0" ]; then
    exit 1;
fi
