#!/bin/bash

echo "Deploying image to DockerHub..."

echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
docker push swaggermock/swagger-mock
