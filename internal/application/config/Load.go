package config

import (
	"github.com/asaskevich/govalidator"
	"github.com/muonsoft/openapi-mock/internal/openapi/generator/data"
	"github.com/sirupsen/logrus"
	"os"
	"path/filepath"
	"strings"
	"time"
)

func Load(filename string) (*Configuration, error) {
	fileConfig, err := loadConfigurationFromFileOrCreateEmptyConfig(filename)
	if err != nil {
		return nil, err
	}

	updateConfigFromEnvironment(fileConfig)
	autocorrectValues(filename, fileConfig)

	_, err = govalidator.ValidateStruct(fileConfig)
	if err != nil {
		return nil, &ErrInvalidConfiguration{ValidationError: err}
	}

	return createApplicationConfiguration(fileConfig), nil
}

func loadConfigurationFromFileOrCreateEmptyConfig(filename string) (*fileConfiguration, error) {
	fileConfig := &fileConfiguration{}
	filename = detectDefaultConfigurationFile(filename)

	if filename != "" {
		var err error
		fileConfig, err = loadFileConfiguration(filename)
		if err != nil {
			return nil, err
		}
	}

	return fileConfig, nil
}

func detectDefaultConfigurationFile(filename string) string {
	if filename == "" {
		defaultFilenames := []string{"openapi-mock.yaml", "openapi-mock.yml", "openapi-mock.json"}
		for _, defaultFilename := range defaultFilenames {
			if _, err := os.Stat(defaultFilename); err == nil {
				filename = defaultFilename
				break
			}
		}
	}

	return filename
}

func autocorrectValues(filename string, fileConfig *fileConfiguration) {
	fileConfig.Application.LogLevel = defaultOnEmptyString(fileConfig.Application.LogLevel, DefaultLogLevel.String())
	fileConfig.Application.LogFormat = defaultOnEmptyString(fileConfig.Application.LogFormat, "tty")
	fileConfig.Generation.UseExamples = defaultOnEmptyString(fileConfig.Generation.UseExamples, "no")

	if fileConfig.Application.Debug {
		fileConfig.Application.LogLevel = logrus.TraceLevel.String()
	}

	if fileConfig.HTTP.ResponseTimeout <= 0 {
		fileConfig.HTTP.ResponseTimeout = DefaultResponseTimeout.Seconds()
	}

	if specificationURLIsRelativeFilename(filename, fileConfig) {
		fileConfig.OpenAPI.SpecificationURL = filepath.Dir(filename) + "/" + fileConfig.OpenAPI.SpecificationURL
	}
}

func specificationURLIsRelativeFilename(filename string, fileConfig *fileConfiguration) bool {
	return filename != "" &&
		fileConfig.OpenAPI.SpecificationURL != "" &&
		!fileConfig.OpenAPI.urlFromEnv &&
		!strings.HasPrefix(fileConfig.OpenAPI.SpecificationURL, "http")
}

func createApplicationConfiguration(fileConfig *fileConfiguration) *Configuration {
	return &Configuration{
		SpecificationURL: fileConfig.OpenAPI.SpecificationURL,

		CORSEnabled:     fileConfig.HTTP.CORSEnabled,
		Port:            defaultOnNilUint16(fileConfig.HTTP.Port, DefaultPort),
		ResponseTimeout: time.Duration(fileConfig.HTTP.ResponseTimeout * float64(time.Second)),

		Debug:     fileConfig.Application.Debug,
		LogFormat: fileConfig.Application.LogFormat,
		LogLevel:  parseLogLevel(fileConfig.Application.LogLevel),

		UseExamples:     parseUseExamples(fileConfig.Generation.UseExamples),
		NullProbability: defaultOnNilFloat(fileConfig.Generation.NullProbability, DefaultNullProbability),
		DefaultMinInt:   defaultOnNilInt64(fileConfig.Generation.DefaultMinInt, 0),
		DefaultMaxInt:   defaultOnNilInt64(fileConfig.Generation.DefaultMaxInt, DefaultMaxInt),
		DefaultMinFloat: defaultOnNilFloat(fileConfig.Generation.DefaultMinFloat, DefaultMinFloat),
		DefaultMaxFloat: defaultOnNilFloat(fileConfig.Generation.DefaultMaxFloat, DefaultMaxFloat),
		SuppressErrors:  fileConfig.Generation.SuppressErrors,
	}
}

func parseLogLevel(rawLogLevel string) logrus.Level {
	var logLevel logrus.Level
	var err error

	if rawLogLevel == "" {
		logLevel = DefaultLogLevel
	} else {
		logLevel, err = logrus.ParseLevel(rawLogLevel)

		if err != nil {
			panic(err)
		}
	}

	return logLevel
}

func parseUseExamples(useExamples string) data.UseExamplesEnum {
	if useExamples == "if_present" {
		return data.IfPresent
	}
	if useExamples == "exclusively" {
		return data.Exclusively
	}

	return data.No
}
