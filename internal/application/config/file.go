package config

import (
	"context"
	"fmt"
	"os"
	"strings"

	"github.com/muonsoft/validation"
	"github.com/muonsoft/validation/it"
	"github.com/muonsoft/validation/validator"
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
	data, err := os.ReadFile(filename)
	if err != nil {
		return nil, &LoadingFailedError{Previous: err}
	}

	var fileConfig fileConfiguration
	err = yaml.Unmarshal(data, &fileConfig)
	if err != nil {
		return nil, &LoadingFailedError{Previous: err}
	}

	return &fileConfig, nil
}

var logFormats = []string{"tty", "json"}
var logLevels = []string{"panic", "fatal", "error", "warn", "warning", "info", "debug", "trace"}
var useExampleOptions = []string{"no", "if_present", "exclusively"}

var invalidLogFormat = fmt.Sprintf("must be one of: %s", strings.Join(logFormats, ", "))
var invalidLogLevel = fmt.Sprintf("must be one of: %s", strings.Join(logLevels, ", "))
var invalidUseExample = fmt.Sprintf("must be one of: %s", strings.Join(useExampleOptions, ", "))

func (config *fileConfiguration) Validate() error {
	return validator.Validate(
		context.Background(),
		validation.String(config.Application.LogFormat, it.IsOneOf(logFormats...).WithMessage(invalidLogFormat)).
			At(validation.PropertyName("application"), validation.PropertyName("log_format")),
		validation.String(config.Application.LogLevel, it.IsOneOf(logLevels...).WithMessage(invalidLogLevel)).
			At(validation.PropertyName("application"), validation.PropertyName("log_level")),
		validation.String(config.Generation.UseExamples, it.IsOneOf(useExampleOptions...).WithMessage(invalidUseExample)).
			At(validation.PropertyName("generation"), validation.PropertyName("use_examples")),
		validation.NilNumber[uint16](
			config.HTTP.Port,
			it.IsBetween[uint16](1, 65535).
				When(config.HTTP.Port != nil).
				WithMessage("value should be between {{ min }} and {{ max }} if present"),
		).At(validation.PropertyName("http"), validation.PropertyName("port")),
	)
}
