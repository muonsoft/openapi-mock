package config

import "github.com/kelseyhightower/envconfig"

type environmentConfiguration struct {
	SpecificationURL *string `split_words:"true"`

	CORSEnabled     *bool `split_words:"true"`
	Port            *uint16
	ResponseTimeout *float64 `split_words:"true"`

	Debug     *bool
	LogFormat *string `split_words:"true"`
	LogLevel  *string `split_words:"true"`

	DefaultMinFloat *float64 `split_words:"true"`
	DefaultMaxFloat *float64 `split_words:"true"`
	DefaultMinInt   *int64   `split_words:"true"`
	DefaultMaxInt   *int64   `split_words:"true"`
	NullProbability *float64 `split_words:"true"`
	SuppressErrors  *bool    `split_words:"true"`
	UseExamples     *string  `split_words:"true"`
}

func updateConfigFromEnvironment(fileConfig *fileConfiguration) {
	var envConfig environmentConfiguration
	envconfig.MustProcess("OPENAPI_MOCK", &envConfig)

	if envConfig.SpecificationURL != nil {
		fileConfig.OpenAPI.SpecificationURL = *envConfig.SpecificationURL
		fileConfig.OpenAPI.urlFromEnv = true
	}

	fileConfig.HTTP.CORSEnabled = coalesceBool(fileConfig.HTTP.CORSEnabled, envConfig.CORSEnabled)
	fileConfig.HTTP.Port = coalesceUint16(fileConfig.HTTP.Port, envConfig.Port)
	fileConfig.HTTP.ResponseTimeout = *coalesceFloat(&fileConfig.HTTP.ResponseTimeout, envConfig.ResponseTimeout)

	fileConfig.Application.Debug = coalesceBool(fileConfig.Application.Debug, envConfig.Debug)
	fileConfig.Application.LogFormat = coalesceString(fileConfig.Application.LogFormat, envConfig.LogFormat)
	fileConfig.Application.LogLevel = coalesceString(fileConfig.Application.LogLevel, envConfig.LogLevel)

	fileConfig.Generation.DefaultMinFloat = coalesceFloat(fileConfig.Generation.DefaultMinFloat, envConfig.DefaultMinFloat)
	fileConfig.Generation.DefaultMaxFloat = coalesceFloat(fileConfig.Generation.DefaultMaxFloat, envConfig.DefaultMaxFloat)
	fileConfig.Generation.DefaultMinInt = coalesceInt64(fileConfig.Generation.DefaultMinInt, envConfig.DefaultMinInt)
	fileConfig.Generation.DefaultMaxInt = coalesceInt64(fileConfig.Generation.DefaultMaxInt, envConfig.DefaultMaxInt)
	fileConfig.Generation.NullProbability = coalesceFloat(fileConfig.Generation.NullProbability, envConfig.NullProbability)
	fileConfig.Generation.SuppressErrors = coalesceBool(fileConfig.Generation.SuppressErrors, envConfig.SuppressErrors)
	fileConfig.Generation.UseExamples = coalesceString(fileConfig.Generation.UseExamples, envConfig.UseExamples)
}

func coalesceString(v1 string, v2 *string) string {
	if v2 != nil {
		return *v2
	}

	return v1
}

func coalesceBool(v1 bool, v2 *bool) bool {
	if v2 != nil {
		return *v2
	}

	return v1
}

func coalesceUint16(v1 *uint16, v2 *uint16) *uint16 {
	if v2 != nil {
		return v2
	}

	return v1
}

func coalesceInt64(v1 *int64, v2 *int64) *int64 {
	if v2 != nil {
		return v2
	}

	return v1
}

func coalesceFloat(v1 *float64, v2 *float64) *float64 {
	if v2 != nil {
		return v2
	}

	return v1
}
