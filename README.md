# Swagger Mock Server

**This is a fork of [swagger-mock/swagger-mock](https://github.com/swagger-mock/swagger-mock) with personal modifications. Use with care!**

[![Build Status](https://travis-ci.org/gschafra/swagger-mock.svg?branch=master)](https://travis-ci.org/gschafra/swagger-mock)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gschafra/swagger-mock/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gschafra/swagger-mock/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/gschafra/swagger-mock/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/gschafra/swagger-mock/?branch=master)
[![StyleCI](https://github.styleci.io/repos/145602302/shield?branch=master)](https://github.styleci.io/repos/145602302)

Swagger API mock server with fake data generation with main features.

* OpenAPI 3.x support.
* Load specification from local file or URL.
* JSON and YAML format supported.
* Generates fake response data by provided schemas.
* Content negotiation by Accept header.
* Runs in Docker container.

## Supported features

| Feature | Support status |
| --- | --- |
| generating xml response | basic support ([without xml tags](https://swagger.io/docs/specification/data-models/representing-xml/)) |
| generating json response | supported |
| generation of [basic types](https://swagger.io/docs/specification/data-models/data-types/) | supported |
| generation of [enums](https://swagger.io/docs/specification/data-models/enums/) | supported |
| generation of [associative arrays](https://swagger.io/docs/specification/data-models/dictionaries/) | supported |
| generation of [combined types](https://swagger.io/docs/specification/data-models/oneof-anyof-allof-not/) | supported (without tag `not` and discriminator) |
| local reference resolving | supported |
| remote reference resolving | not supported |
| URL reference resolving | not supported |
| validating request data | not supported |
| force using custom response schema | not supported (schema detected automatically) |

## How to use

Recommended way is to use [Docker](https://www.docker.com/) container.

```bash
docker pull gschafra/swagger-mock

# with remote file
docker run -p 8080:8080 -e "SWAGGER_MOCK_SPECIFICATION_URL=https://raw.githubusercontent.com/OAI/OpenAPI-Specification/master/examples/v3.0/petstore.yaml" --rm gschafra/swagger-mock

# with local file
docker run -p 8080:8080 -v $PWD/examples/petstore.yaml:/openapi/petstore.yaml -e "SWAGGER_MOCK_SPECIFICATION_URL=/openapi/petstore.yaml" --rm gschafra/swagger-mock
```

Also, you can use [Docker Compose](https://docs.docker.com/compose/). Example of `docker-compose.yml`

```yaml
version: '3.0'

services:
  swagger_mock:
    container_name: swagger_mock
    image: gschafra/swagger-mock
    environment:
      SWAGGER_MOCK_SPECIFICATION_URL: 'https://raw.githubusercontent.com/OAI/OpenAPI-Specification/master/examples/v3.0/petstore.yaml'
    ports:
      - "8080:8080"
```

To start up container run command

```bash
docker-compose up -d
```

## Configuration

### Environment variables

Mock server options can be set via environment variables.

#### SWAGGER_MOCK_SPECIFICATION_URL

* Path to file with OpenAPI v3 specification (_required_)
* _Possible values_: any valid URL or path to file

#### SWAGGER_MOCK_LOG_LEVEL

* Error log level
* _Default value_: `warning`
* _Possible values_: `error`, `warning`, `info`, `debug`

#### SWAGGER_MOCK_CACHE_DIRECTORY

* Directory for OpenAPI specification cache
* _Default value_: `/dev/shm/openapi-cache`
* _Possible values_: any valid path

#### SWAGGER_MOCK_CACHE_TTL

* Time to live for OpenAPI specification cache in seconds
* _Default value_: 0
* _Possible values_: positive integer

#### SWAGGER_MOCK_CACHE_STRATEGY

* Caching strategy for OpenAPI specification cache
* _Default value_: `disabled`
* _Possible values_: `disabled`, `url_md5`, `url_and_timestamp_md5`

### Specification cache

To speed up server response time you can use caching mechanism for OpenAPI. There are several caching strategies. Specific strategy can be set by environment variable `SWAGGER_MOCK_CACHE_STRATEGY`.

* `url_md5` calculates hash from specification URL and if specification URL was not changed uses parsed objects from cache.
* `url_and_timestamp_md5` calculates hash from specification URL and timestamp (file timestamp or value of `Last-Modified` header). If you are using file from remote server make sure that valid `Last-Modified` header is present. 

Recommended options for use with remote file (accessible by URL).

* `SWAGGER_MOCK_CACHE_STRATEGY='url_md5'`
* `SWAGGER_MOCK_CACHE_TTL=3600`

Recommended options for use with local file (at local server).

* `SWAGGER_MOCK_CACHE_STRATEGY='url_and_timestamp_md5'`
* `SWAGGER_MOCK_CACHE_TTL=3600`

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Features roadmap for next versions

* [ ] extra response negotiation (return of 405 code)
  * [ ] path parser
  * [ ] route matcher in path object
  * [ ] routing by path and endpoints
* [ ] response cache
* [ ] faker expression extension for numbers
* [ ] faker expression extension for strings
* [ ] request body validation
* [ ] remote reference support
* [ ] url reference support
* [ ] discriminator in combined types
