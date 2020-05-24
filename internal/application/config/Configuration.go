package config

import (
	"github.com/sirupsen/logrus"
	"math"
	"swagger-mock/internal/openapi/generator/data"
)

type Configuration struct {
	// OpenAPI options
	SpecificationURL string

	// HTTP server options
	CORSEnabled bool
	Port        uint16

	// Application options
	Debug     bool
	LogFormat string
	LogLevel  logrus.Level

	// Generation options
	UseExamples     data.UseExamplesEnum
	NullProbability float64
	DefaultMinInt   int64
	DefaultMaxInt   int64
	DefaultMinFloat float64
	DefaultMaxFloat float64
	SuppressErrors  bool
}

const (
	DefaultPort            = uint16(8080)
	DefaultLogLevel        = logrus.InfoLevel
	DefaultNullProbability = 0.5
	DefaultMaxInt          = int64(math.MaxInt32)
	DefaultMinFloat        = -float64(math.MaxInt32 / 2)
	DefaultMaxFloat        = float64(math.MaxInt32 / 2)
)
