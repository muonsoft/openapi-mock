# Usage guide

## Console commands 

### Help command

To show help you can use `--help` (short version `-h`) option.

```bash
./openapi-mock --help
```

### Starting a web server

To run a web server use `serve` command. Required option to run a web server is a URL to your OpenAPI specification. You can set it via console option `--specification-url` (short version `-u`), via environment variable `OPENAPI_MOCK_SPECIFICATION_URL` or by option in a configuration file (`openapi-mock.yaml`/`openapi-mock.yml`/`openapi-mock.json` by default).

Runs a local server with remote specification file.

```bash
./openapi-mock serve --specification-url http://your.domain/openapi-specification.yaml
```

Runs a local server with local specification file.

```bash
./openapi-mock serve --specification-url path/to/your/openapi-specification.yaml
```

Set URL to specification file via environment variable.

```bash
export OPENAPI_MOCK_SPECIFICATION_FILE=path/to/your/openapi-specification.yaml
./openapi-mock serve
```

Set URL to specification file via configuration file `openapi-mock.yaml`.

```yaml
openapi:
  specification_url: 'path/to/your/openapi-specification.yaml'
```

For that case to start a web server simply use.

```bash
./openapi-mock serve
```

### Validating a specification

Before starting a web server it is useful to check that your specification file is correct and can be handled by the application. Especially, it can be useful for Continuous Integration. To do that you can use `validate` command.

```bash
./openapi-mock validate --specification-url http://your.domain/openapi-specification.yaml
```

Be aware, that at the moment this command shows only critical errors without details. So, this command is only suitable to check that server can be successfully ran. For better user experience it is recommended to use alternative tools for specification validation (for example, [Redocly/openapi-cli](https://github.com/Redocly/openapi-cli)).

## Setting up a configuration

There are three ways to set up a configuration options of the application. 

By default, all options loaded from a local file, if one exist: `openapi-mock.yaml`, `openapi-mock.yml` or `openapi-mock.json`. To load configuration from a specific file you can use `--configuration` (short version `-c`) option.

```bash
./openapi-mock serve --configuration path/to/your/openapi-mock-configuration.yaml
```

Options loaded from a file can be overridden by environment variables.

```bash
# this will override any options loaded from `openapi-mock.yaml`
export OPENAPI_MOCK_SPECIFICATION_FILE=path/to/your/openapi-specification.yaml
./openapi-mock serve --configuration openapi-mock.yaml
``` 

Also, if you specify a path to an OpenAPI specification via console argument `--specification-url` it will override the same option loaded from a file or an environment variable. 

```bash
# OpenAPI specification will be loaded from `http://your.domain/openapi-specification.yaml`
export OPENAPI_MOCK_SPECIFICATION_FILE=path/to/your/openapi-specification.yaml
./openapi-mock serve --configuration openapi-mock.yaml --specification-url http://your.domain/openapi-specification.yaml
``` 

## Configuration file example

```yaml
# OpenAPI specification options
openapi:
  specification_url: 'path/to/your/openapi-specification.yaml'

# web server options
http:
  cors_enabled: false
  port: 8080
  response_timeout: 1.0

# application specific options
application:
  debug: false
  log_format: tty
  log_level: info

# options to control generation process
generation:
  default_min_float: -1.073741823e+09
  default_max_float: 1.073741823e+09
  default_min_int: 0
  default_max_int: 2147483647
  null_probability: 0.5
  suppress_errors: false
  use_examples: 'no'
```

## Configuration options

### OpenAPI specification options

#### specification_url

* **type**: `string` 
* **key**: `openapi.specification_url` 
* **environment variable**: `OPENAPI_MOCK_SPECIFICATION_URL` 
* **possible values**: any valid URL or path to a file

Path to a local OpenAPI specification file or URL to a remote file. This option is _required_ for any command.

Examples

* `https://raw.githubusercontent.com/OAI/OpenAPI-Specification/master/examples/v3.0/petstore.yaml`
* `path/to/your/openapi-specification.yaml`

### Web server options

#### cors_enabled

* **type**: `boolean` 
* **key**: `http.cors_enabled` 
* **environment variable**: `OPENAPI_MOCK_CORS_ENABLED` 
* **default value**: `false`
* **possible values**: `true` or `false` (using `0` or `1` is recommended for an environment variable)

When enabled, CORS request will automatically be handled.

#### port

* **type**: `integer` 
* **key**: `http.port` 
* **environment variable**: `OPENAPI_MOCK_PORT` 
* **default value**: `8080`
* **possible values**: any valid port

Server port for listening HTTP connections.

#### response_timeout

* **type**: `float` 
* **key**: `http.response_timeout` 
* **environment variable**: `SWAGGER_MOCK_RESPONSE_TIMEOUT` 
* **default value**: `1.0`
* **possible values**: any float value more than `0`

Timeout in seconds for generating a mock response. If it is exceeded then HTTP service will return 503 error.

### Application specific options

#### debug

* **type**: `boolean` 
* **key**: `application.debug` 
* **environment variable**: `SWAGGER_MOCK_DEBUG` 
* **default value**: `false`
* **possible values**: `true` or `false` (using `0` or `1` is recommended for an environment variable)

Enabling debug mode with more details about errors. If it is set to `true`, then log level forced to `trace`.

#### log_format

* **type**: `string` 
* **key**: `application.log_format` 
* **environment variable**: `OPENAPI_MOCK_LOG_FORMAT` 
* **default value**: `tty`
* **possible values**: `tty` or `json`

If option is `tty` then logs will be printed in color-coded style (when TTY is attached) or a plain text. If options is `json` then logs will be printed in JSON format. 

#### log_level

* **type**: `string` 
* **key**: `application.log_format` 
* **environment variable**: `OPENAPI_MOCK_` 
* **default value**: `info`
* **possible values**: `fatal`, `error`, `warning`, `info`, `debug`, `trace`

Error log level.

### Options to control generation process

#### default_min_float

* **type**: `float` 
* **key**: `generation.default_min_float` 
* **environment variable**: `SWAGGER_MOCK_DEFAULT_MIN_FLOAT` 
* **default value**: `-1.073741823e+09`
* **possible values**: any 64-bit float

Default minimum value for float type.

#### default_max_float

* **type**: `float` 
* **key**: `generation.default_max_float` 
* **environment variable**: `SWAGGER_MOCK_DEFAULT_MAX_FLOAT` 
* **default value**: `1.073741823e+09`
* **possible values**: any 64-bit float

Default maximum value for float type.

#### default_min_int

* **type**: `integer` 
* **key**: `generation.default_min_int` 
* **environment variable**: `SWAGGER_MOCK_DEFAULT_MIN_INT` 
* **default value**: `0`
* **possible values**: any 64-bit integer

Default minimum value for integer type.

#### default_max_int

* **type**: `integer` 
* **key**: `generation.default_max_int` 
* **environment variable**: `SWAGGER_MOCK_DEFAULT_MAX_INT` 
* **default value**: `2147483647`
* **possible values**: any 64-bit integer

Default maximum value for integer type.

#### null_probability

* **type**: `float` 
* **key**: `generation.null_probability` 
* **environment variable**: `SWAGGER_MOCK_NULL_PROBABILITY` 
* **default value**: `0.5`
* **possible values**: from `0.0` to `1.0`

Probability for generating null values for nullable properties.

#### suppress_errors

* **type**: `boolean` 
* **key**: `generation.suppress_errors` 
* **environment variable**: `SWAGGER_MOCK_SUPPRESS_ERRORS` 
* **default value**: `false`
* **possible values**: `true` or `false` (using `0` or `1` is recommended for an environment variable)

When enabled, generation errors will be suppressed and default values used instead. Can be useful when dealing with complex specification, and some bugs occurs in the part of the data.

#### use_examples

* **type**: `string` 
* **key**: `generation.use_examples` 
* **environment variable**: `SWAGGER_MOCK_USE_EXAMPLES` 
* **default value**: `no`
* **possible values**: `no`, `if_present`, `exclusively`

Strategy for generating response data by examples.

* `no` - examples will be ignored and all data will be generated randomly
* `if_present` - examples will be used instead of random data if they are present
* `exclusively` - only examples will be used, no random data generation
