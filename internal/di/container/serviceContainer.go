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
	configuration config.Configuration
	logger        logrus.FieldLogger
}

func New(configuration config.Configuration) Container {
	logger := createLogger(configuration)

	return &serviceContainer{
		configuration: configuration,
		logger:        logger,
	}
}

func (container *serviceContainer) GetLogger() logrus.FieldLogger {
	return container.logger
}

func (container *serviceContainer) CreateSpecificationLoader() loader.SpecificationLoader {
	return loader.New()
}

func (container *serviceContainer) CreateOpenApiHandler(router *openapi3filter.Router) http.Handler {
	generatorOptions := dataGenerator.Options{
		UseExamples: container.configuration.UseExamples,
	}

	dataGeneratorInstance := dataGenerator.New(generatorOptions)
	responseGeneratorInstance := responseGenerator.New(dataGeneratorInstance)
	apiResponder := responder.New()

	httpHandler := handler.NewResponseGeneratorHandler(router, responseGeneratorInstance, apiResponder)
	return httpHandler
}

func createLogger(configuration config.Configuration) *logrus.Logger {
	logger := logrus.New()
	logger.SetLevel(configuration.LogLevel)

	if configuration.LogFormat == "json" {
		logger.SetFormatter(&logrus.JSONFormatter{})
	} else {
		logger.SetFormatter(&logrus.TextFormatter{})
	}

	return logger
}
