package config

import (
	"github.com/kelseyhightower/envconfig"
	"github.com/sirupsen/logrus"
	"os"
)

type Configuration struct {
	SpecificationUrl string       `required:"true" split_words:"true"`
	Port             uint16       `default:"8080"`
	LogLevel         logrus.Level `ignored:"true"`
	LogFormat        string       `envconfig:"log_format"`
	Debug            bool
}

func LoadConfigFromEnvironment() Configuration {
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

	return configuration
}

func getLogLevel() logrus.Level {
	var logLevel logrus.Level
	var err error

	unparsedLogLevel := os.Getenv("SWAGGER_MOCK_LOG_LEVEL")

	if unparsedLogLevel == "" {
		logLevel = logrus.InfoLevel
	} else {
		logLevel, err = logrus.ParseLevel(unparsedLogLevel)

		if err != nil {
			panic(err)
		}
	}

	return logLevel
}
