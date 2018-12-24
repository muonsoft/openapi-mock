# Swagger Mock Server

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/strider2038/swagger-mock/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/strider2038/swagger-mock/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/strider2038/swagger-mock/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/strider2038/swagger-mock/?branch=master)

Swagger mock server with fake data generation support

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
* [ ] combined types
  * [x] oneOf
  * [ ] anyOf
  * [x] allOf
* [ ] support of readOnly, writeOnly fields
* [x] caching loader for OpenAPI specification
* [ ] cache invalidation by timestamp/hash
* [ ] logging
* [ ] docker build in ci
* [ ] try spiral/roadrunner server instead of nginx
* [ ] make supported features table in README

## TODO for v0.2

* [ ] discriminator in combined types
* [ ] response cache
* [ ] faker expression extension for numbers
* [ ] faker expression extension for strings
* [ ] remote reference support
* [ ] url reference support
