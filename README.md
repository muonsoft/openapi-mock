# OpenAPI Mock Server

![CI](https://github.com/muonsoft/openapi-mock/workflows/CI/badge.svg?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/muonsoft/openapi-mock/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/muonsoft/openapi-mock/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/muonsoft/openapi-mock/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/muonsoft/openapi-mock/?branch=master)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/muonsoft/openapi-mock)

OpenAPI API mock server with random data generation by specified schemas.

* OpenAPI 3.x support.
* Load specification from a local file or URL.
* JSON and YAML format supported.
* Generates fake response data by provided schemas or by examples.
* Content negotiation by Accept header.
* Can be used as standalone application (Linux and Windows) or can be run via Docker container.

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

## Quick start

Download latest binary and run a server.

```bash
# runs a local server on port 8080
./openapi-mock serve --specification-url https://raw.githubusercontent.com/OAI/OpenAPI-Specification/master/examples/v3.0/petstore.yaml

# to test that the server successfully ran
curl 'http://localhost:8080/v1/pets'
```

Alternatively, you can use [Docker](https://www.docker.com/) image.

```bash
# downloads an image
docker pull muonsoft/openapi-mock

# runs a docker container with exported port 8080
docker run -p 8080:8080 -e "OPENAPI_MOCK_SPECIFICATION_URL=https://raw.githubusercontent.com/OAI/OpenAPI-Specification/master/examples/v3.0/petstore.yaml" --rm muonsoft/openapi-mock

# to test that the server successfully ran
curl 'http://localhost:8080/v1/pets'
```

Also, you can use [Docker Compose](https://docs.docker.com/compose/). Example of `docker-compose.yml`

```yaml
version: '3.0'

services:
  openapi_mock:
    container_name: openapi_mock
    image: muonsoft/openapi-mock
    environment:
      OPENAPI_MOCK_SPECIFICATION_URL: 'https://raw.githubusercontent.com/OAI/OpenAPI-Specification/master/examples/v3.0/petstore.yaml'
    ports:
      - "8080:8080"
```

To start up a container run command.

```bash
docker-compose up -d
```

## Usage guide

* [Console commands](docs/usage_guide.md#console-commands)
* [Setting up a configuration](docs/usage_guide.md#setting-up-a-configuration)
* [Configuration file example](docs/usage_guide.md#configuration-file-example)
* [Configuration options](docs/usage_guide.md#configuration-options)

## License

This project is licensed under the MIT License - see the LICENSE file for details.
