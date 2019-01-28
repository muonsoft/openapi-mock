# Swagger Mock Server

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/strider2038/swagger-mock/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/strider2038/swagger-mock/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/strider2038/swagger-mock/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/strider2038/swagger-mock/?branch=master)
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
| generating json response | basic support ([without xml tags](https://swagger.io/docs/specification/data-models/representing-xml/)) |
| generating xml response | supported |
| generation of [basic types](https://swagger.io/docs/specification/data-models/data-types/) | supported |
| generation of [enums](https://swagger.io/docs/specification/data-models/enums/) | supported |
| generation of [associative arrays](https://swagger.io/docs/specification/data-models/dictionaries/) | supported |
| generation of [combined types](https://swagger.io/docs/specification/data-models/oneof-anyof-allof-not/) | supported without tag `not` |
| local reference resolving | supported |
| remote reference resolving | not supported |
| URL reference resolving | not supported |
| validating request data | not supported |
| force using custom response schema | not supported (schema detected automatically) |

## How to use

Recommended way is to use Docker container

```bash
docker pull strider2038/swagger-mock

# with remote file
docker run -p 8080:8080 -e "SWAGGER_MOCK_SPECIFICATION_URL=https://raw.githubusercontent.com/OAI/OpenAPI-Specification/master/examples/v3.0/petstore.yaml" --rm strider2038/swagger-mock
```

## TODO list

* [x] basic array type support
* [x] basic number type support
* [x] boolean type support
* [x] parsing context (path) and better exceptions
* [x] content negotiation
* [x] xml/json encoding in responder
* [x] using number parameters for generator
* [x] using integer parameters for generator
* [x] using string parameters for generator
* [x] using array parameters for generator
* [x] support of additionalProperties for object type
  * [x] free-form object
  * [x] hash-map
  * [x] hash-map fixed keys
* [x] local reference resolving
* [x] default response support
* [x] combined types
  * [x] oneOf
  * [x] anyOf
  * [x] allOf
* [x] support of readOnly, writeOnly fields
* [x] caching loader for OpenAPI specification
* [x] cache invalidation by timestamp/hash
* [x] logging
* [x] try spiral/roadrunner server instead of nginx
* [x] use of easy coding standard
* [x] make supported features table in README
* [ ] type assertions
* [ ] docker build in ci
* [ ] container acceptance test in ci 

## TODO for v0.2

* [ ] discriminator in combined types
* [ ] response cache
* [ ] faker expression extension for numbers
* [ ] faker expression extension for strings
* [ ] remote reference support
* [ ] url reference support
