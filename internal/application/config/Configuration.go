package config

import (
	"github.com/kelseyhightower/envconfig"
	"github.com/sirupsen/logrus"
	"os"
	"swagger-mock/internal/openapi/generator/data"
)

type Configuration struct {
	SpecificationURL string               `split_words:"true"`
	UseExamples      data.UseExamplesEnum `ignored:"true"`
	NullProbability  float64              `split_words:"true" default:"0.5"`
	DefaultMinInt    int64                `split_words:"true"`
	DefaultMaxInt    int64                `split_words:"true" default:"2147483647"`
	DefaultMinFloat  float64              `split_words:"true" default:"-1.073741823e+09"`
	DefaultMaxFloat  float64              `split_words:"true" default:"1.073741823e+09"`
	CORSEnabled      bool                 `split_words:"true"`
	SuppressErrors   bool                 `split_words:"true"`
	Debug            bool
	Port             uint16       `default:"8080"`
	LogLevel         logrus.Level `ignored:"true"`
	LogFormat        string       `split_words:"true"`
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
		logLevel = logrus.InfoLevel
	} else {
		logLevel, err = logrus.ParseLevel(unparsedLogLevel)

		if err != nil {
			panic(err)
		}
	}

	return logLevel
}

func getUseExamples() data.UseExamplesEnum {
	useExamples := os.Getenv("SWAGGER_MOCK_USE_EXAMPLES")

	if useExamples == "if_present" {
		return data.IfPresent
	}
	if useExamples == "exclusively" {
		return data.Exclusively
	}

	return data.No
}
