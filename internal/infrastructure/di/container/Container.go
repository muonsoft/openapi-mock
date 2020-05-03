package container

import (
	"github.com/sirupsen/logrus"
	"swagger-mock/internal/application/config"
)

type Container struct {
	Logger logrus.FieldLogger
}

func New(config config.Configuration) *Container {
	logger := createLogger(config)

	return &Container{logger}
}

func createLogger(config config.Configuration) *logrus.Logger {
	logger := logrus.New()
	logger.SetLevel(config.LogLevel)

	if config.LogFormat == "json" {
		logger.SetFormatter(&logrus.JSONFormatter{})
	} else {
		logger.SetFormatter(&logrus.TextFormatter{})
	}

	return logger
}
