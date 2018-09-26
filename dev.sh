#!/bin/bash

build=0
acceptance_test=0
for i in $@; do
    if [[ "$i" == "--build" || "$i" == "-b" ]]; then
        build=1
    fi
done

container_name="swagger-mock"
container_tag="strider2038/swagger-mock"
container_dev_name="$container_name-dev"
container_dev_tag="$container_tag:dev"

if [ ${build} -eq 1 ]; then
    ./build.sh

    echo "Building development image $container_dev_tag..."
    echo "========================================================================="

    docker build \
        --file ./.dev/Dockerfile \
        --tag "$container_dev_tag" \
        .
fi

echo "Cleaning old images $container_dev_name..."
echo "========================================================================="
docker stop "$container_dev_name"
docker rm "$container_dev_name"

echo "Starting development container $container_dev_name..."
echo "========================================================================="

docker run \
    --publish 80:80 --publish 9002:9001 \
    --detach \
    --name "$container_dev_name" \
    --volume $PWD:/app \
    "$container_dev_tag"
