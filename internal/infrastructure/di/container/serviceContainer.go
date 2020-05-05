package container

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"github.com/sirupsen/logrus"
	"net/http"
	dataGenerator "swagger-mock/internal/application/mock/generator"
	"swagger-mock/internal/infrastructure/di/config"
	responseGenerator "swagger-mock/internal/infrastructure/openapi/generator"
	"swagger-mock/internal/infrastructure/openapi/handler"
	"swagger-mock/internal/infrastructure/openapi/loader"
	"swagger-mock/internal/infrastructure/openapi/responder"
)

type serviceContainer struct {
	logger logrus.FieldLogger
}

func New(config config.Configuration) Container {
	logger := createLogger(config)

	return &serviceContainer{logger}
}

func (container *serviceContainer) GetLogger() logrus.FieldLogger {
	return container.logger
}

func (container *serviceContainer) CreateSpecificationLoader() loader.SpecificationLoader {
	return loader.New()
}

func (container *serviceContainer) CreateOpenApiHandler(router *openapi3filter.Router) http.Handler {
	dataGenerator := dataGenerator.New()
	responseGenerator := responseGenerator.New(dataGenerator)
	webResponder := responder.New()

	httpHandler := handler.NewResponseGeneratorHandler(router, responseGenerator, webResponder)
	return httpHandler
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
