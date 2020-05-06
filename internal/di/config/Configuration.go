package config

import (
	"github.com/kelseyhightower/envconfig"
	"github.com/sirupsen/logrus"
	"os"
	"swagger-mock/internal/mock/generator"
)

type Configuration struct {
	SpecificationURL string                    `required:"true" split_words:"true"`
	UseExamples      generator.UseExamplesEnum `ignored:"true"`
	Debug            bool
	Port             uint16       `default:"8080"`
	LogLevel         logrus.Level `ignored:"true"`
	LogFormat        string       `envconfig:"log_format"`
}

func LoadFromEnvironment() Configuration {
	var configuration Configuration
	envconfig.MustProcess("SWAGGER_MOCK", &configuration)

	if configuration.Debug {
		configuration.LogLevel = logrus.TraceLevel
	} else {
		configuration.LogLevel = getLogLevel()
	}

	if configuration.LogFormat != "json" {
		configuration.LogFormat = "tty"
	}

	configuration.UseExamples = getUseExamples()

	return configuration
}

func getLogLevel() logrus.Level {
	var logLevel logrus.Level
	var err error

	unparsedLogLevel := os.Getenv("SWAGGER_MOCK_LOG_LEVEL")

	if unparsedLogLevel == "" {
		logLevel = logrus.WarnLevel
	} else {
		logLevel, err = logrus.ParseLevel(unparsedLogLevel)

		if err != nil {
			panic(err)
		}
	}

	return logLevel
}

func getUseExamples() generator.UseExamplesEnum {
	useExamples := os.Getenv("SWAGGER_MOCK_USE_EXAMPLES")

	if useExamples == "if_present" {
		return generator.IfPresent
	}
	if useExamples == "exclusively" {
		return generator.Exclusively
	}

	return generator.No
}
