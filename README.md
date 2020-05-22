# Swagger Mock Server

[![Build Status](https://travis-ci.org/swagger-mock/swagger-mock.svg?branch=master)](https://travis-ci.org/swagger-mock/swagger-mock)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/swagger-mock/swagger-mock/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/swagger-mock/swagger-mock/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/swagger-mock/swagger-mock/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/swagger-mock/swagger-mock/?branch=master)
[![StyleCI](https://github.styleci.io/repos/145602302/shield?branch=master)](https://github.styleci.io/repos/145602302)
![Docker Pulls](https://img.shields.io/docker/pulls/swaggermock/swagger-mock)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/swagger-mock/swagger-mock)

Swagger API mock server with fake data generation with main features.

* OpenAPI 3.x support.
* Load specification from a local file or URL.
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
docker pull swaggermock/swagger-mock

# with remote file
docker run -p 8080:8080 -e "SWAGGER_MOCK_SPECIFICATION_URL=https://raw.githubusercontent.com/OAI/OpenAPI-Specification/master/examples/v3.0/petstore.yaml" --rm swaggermock/swagger-mock

# with local file
docker run -p 8080:8080 -v $PWD/examples/petstore.yaml:/openapi/petstore.yaml -e "SWAGGER_MOCK_SPECIFICATION_URL=/openapi/petstore.yaml" --rm swaggermock/swagger-mock
```

Also, you can use [Docker Compose](https://docs.docker.com/compose/). Example of `docker-compose.yml`

```yaml
version: '3.0'

services:
  swagger_mock:
    container_name: swagger_mock
    image: swaggermock/swagger-mock
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
* _Default value_: `info`
* _Possible values_: `error`, `warning`, `info`, `debug`

#### SWAGGER_MOCK_USE_EXAMPLES

* Strategy for generating response data by examples
* _Default value_: `no`
* _Possible values_: `no`, `if_present`, `exclusively`

* `no` - examples will be ignored and all data will be generated randomly
* `if_present` - examples will be used instead of random data if they are present
* `exclusively` - only examples will be used, no random data generation

#### SWAGGER_MOCK_NULL_PROBABILITY

* Probability for generating null values for nullable properties
* _Default value_: `0.5`
* _Possible values_: from `0.0` to `1.0`

#### SWAGGER_MOCK_CORS_ENABLE

 * When enabled, CORS request will automatically be handled
 * _Default value_: `0`
 * _Possible values_: `1` or `0`

#### SWAGGER_MOCK_SUPPRESS_ERRORS

 * When enabled, generation errors will be suppressed and default values used instead. Can be useful when dealing with complex specification, and some bugs occurs in the part of the data. 
 * _Default value_: `0`
 * _Possible values_: `1` or `0`

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
