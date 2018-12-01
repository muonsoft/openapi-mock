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
* [ ] support of additionalProperties for object type
  * [ ] free-form object
  * [ ] hash-map
  * [ ] hash-map fixed keys
* [ ] complex types (oneOf, anyOf, allOf)
* [ ] reference resolving
* [x] caching loader for OpenAPI specification
* [ ] cache invalidation by timestamp/hash
* [ ] logging
* [ ] docker build in ci
* [ ] faker expression extension for numbers
* [ ] faker expression extension for strings
