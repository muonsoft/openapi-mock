package container

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"github.com/sirupsen/logrus"
	"net/http"
	"swagger-mock/internal/di/config"
	dataGenerator "swagger-mock/internal/mock/generator"
	responseGenerator "swagger-mock/internal/openapi/generator"
	"swagger-mock/internal/openapi/handler"
	"swagger-mock/internal/openapi/loader"
	"swagger-mock/internal/openapi/responder"
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
