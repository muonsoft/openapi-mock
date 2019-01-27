#!/bin/bash

docker_run=0
for i in $@; do
   if [[ "$i" == "--run" || "$i" == "-r" ]]; then
       docker_run=1
   fi
done

container_name="swagger-mock"
container_tag="strider2038/swagger-mock"

set -xe

docker build --pull --tag "$container_tag" .

if [ ${docker_run} -eq 1 ]; then
    docker stop "$container_name"
    docker rm "$container_name"

    docker run \
        -p 8080:8080 \
        --detach \
        --name "$container_name" \
        "$container_tag"
fi
