#!/bin/bash

docker_run=0
for i in $@; do
   if [[ "$i" == "--run" || "$i" == "-r" ]]; then
       docker_run=1
   fi
done

container_name="swagger-mock"
container_tag="strider2038/swagger-mock"

echo "Starting to build image $container_tag..."
echo "========================================================================="

docker build --pull --tag "$container_tag" .

echo "========================================================================="
echo "Image $container_tag created"


if [ ${docker_run} -eq 1 ]; then
    echo "Starting container $container_name for image $container_tag..."
    docker stop "$container_name"
    docker rm "$container_name"

    docker run \
        -p 80:80 \
        --detach \
        --name "$container_name" \
        "$container_tag"
fi
