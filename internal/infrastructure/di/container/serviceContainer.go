package container

import (
	"github.com/sirupsen/logrus"
	"swagger-mock/internal/infrastructure/di/config"
)

type serviceContainer struct {
	logger logrus.FieldLogger
}

func New(config config.Configuration) Container {
	logger := createLogger(config)

	return &serviceContainer{logger}
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

func (container *serviceContainer) GetLogger() logrus.FieldLogger {
	return container.logger
}
