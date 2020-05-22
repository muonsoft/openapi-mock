# build stage
FROM golang:alpine AS build-env

ADD . /project

RUN set -e \
    && apk add --no-cache --update \
        git \
        bash \
    && set -x \
    && adduser -D -g '' openapi \
    && go version \
    && cd /project \
    && go mod download \
    && cd /project/cmd/openapi-mock \
    && CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -ldflags="-w -s" -o openapi-mock \
    && ls -la | grep "openapi-mock"

# final stage
FROM alpine

WORKDIR "/app"

COPY --from=build-env /etc/passwd /etc/passwd
COPY --from=build-env /project/cmd/openapi-mock/openapi-mock /app/openapi-mock

USER openapi

ENTRYPOINT [ "/app/openapi-mock" ]
