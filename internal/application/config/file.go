package config

import (
	"fmt"
	"io/ioutil"
	"strings"

	validation "github.com/go-ozzo/ozzo-validation/v4"
	"gopkg.in/yaml.v3"
)

type fileConfiguration struct {
	OpenAPI     openapiConfiguration     `json:"openapi" yaml:"openapi"`
	HTTP        httpConfiguration        `json:"http" yaml:"http"`
	Application applicationConfiguration `json:"application" yaml:"application"`
	Generation  generationConfiguration  `json:"generation" yaml:"generation"`
}

type openapiConfiguration struct {
	SpecificationURL string `json:"specification_url" yaml:"specification_url"`
	urlFromEnv       bool
}

type httpConfiguration struct {
	Port            *uint16 `json:"port" yaml:"port"`
	CORSEnabled     bool    `json:"cors_enabled" yaml:"cors_enabled"`
	ResponseTimeout float64 `json:"response_timeout" yaml:"response_timeout"`
}

type applicationConfiguration struct {
	Debug     bool   `json:"debug" yaml:"debug"`
	LogFormat string `json:"log_format" yaml:"log_format"`
	LogLevel  string `json:"log_level" yaml:"log_level"`
}

type generationConfiguration struct {
	DefaultMinFloat *float64 `json:"default_min_float" yaml:"default_min_float"`
	DefaultMaxFloat *float64 `json:"default_max_float" yaml:"default_max_float"`
	DefaultMinInt   *int64   `json:"default_min_int" yaml:"default_min_int"`
	DefaultMaxInt   *int64   `json:"default_max_int" yaml:"default_max_int"`
	NullProbability *float64 `json:"null_probability" yaml:"null_probability"`
	SuppressErrors  bool     `json:"suppress_errors" yaml:"suppress_errors"`
	UseExamples     string   `json:"use_examples" yaml:"use_examples"`
}

func loadFileConfiguration(filename string) (*fileConfiguration, error) {
	data, err := ioutil.ReadFile(filename)
	if err != nil {
		return nil, &ErrLoadFailed{Previous: err}
	}

	var fileConfig fileConfiguration
	err = yaml.Unmarshal(data, &fileConfig)
	if err != nil {
		return nil, &ErrLoadFailed{Previous: err}
	}

	return &fileConfig, nil
}

var logFormats = []string{"tty", "json"}
var logLevels = []string{"panic", "fatal", "error", "warn", "warning", "info", "debug", "trace"}
var useExampleOptions = []string{"no", "if_present", "exclusively"}

var invalidLogFormat = fmt.Sprintf("must be one of: %s", strings.Join(logFormats, ", "))
var invalidLogLevel = fmt.Sprintf("must be one of: %s", strings.Join(logLevels, ", "))
var invalidUseExample = fmt.Sprintf("must be one of: %s", strings.Join(useExampleOptions, ", "))

func stringsAsInterfaces(ss []string) []interface{} {
	ii := make([]interface{}, len(ss))
	for i, s := range ss {
		ii[i] = s
	}
	return ii
}

func (config *fileConfiguration) Validate() error {
	return validation.Errors{
		"http.port": validation.Validate(
			config.HTTP.Port,
			validation.Required.When(config.HTTP.Port != nil),
			validation.Min(uint16(1)),
			validation.Max(uint16(65535)),
		),
		"application.log_format": validation.Validate(
			config.Application.LogFormat,
			validation.In(stringsAsInterfaces(logFormats)...).Error(invalidLogFormat),
		),
		"application.log_level": validation.Validate(
			config.Application.LogLevel,
			validation.In(stringsAsInterfaces(logLevels)...).Error(invalidLogLevel),
		),
		"generation.use_examples": validation.Validate(
			config.Generation.UseExamples,
			validation.In(stringsAsInterfaces(useExampleOptions)...).Error(invalidUseExample),
		),
	}.Filter()
}
