package container

import (
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/getkin/kin-openapi/openapi3filter"
	"github.com/sirupsen/logrus"
	"net/http"
	"swagger-mock/internal/di/config"
	dataGenerator "swagger-mock/internal/mock/generator"
	responseGenerator "swagger-mock/internal/openapi/generator"
	"swagger-mock/internal/openapi/handler"
	"swagger-mock/internal/openapi/loader"
	"swagger-mock/internal/openapi/responder"
	"swagger-mock/internal/server/middleware"
)

type serviceContainer struct {
	configuration config.Configuration
	logger        logrus.FieldLogger
}

func New(configuration config.Configuration) Container {
	logger := createLogger(configuration)

	container := &serviceContainer{
		configuration: configuration,
		logger:        logger,
	}

	container.init()
	return container
}

func (container *serviceContainer) init() {
	openapi3.DefineStringFormat("uuid", openapi3.FormatOfStringForUUIDOfRFC4122)
	openapi3.DefineStringFormat("html", "<[^>]+>|&[^;]+;")
}

func (container *serviceContainer) GetLogger() logrus.FieldLogger {
	return container.logger
}

func (container *serviceContainer) CreateSpecificationLoader() loader.SpecificationLoader {
	return loader.New()
}

func (container *serviceContainer) CreateHTTPHandler(router *openapi3filter.Router) http.Handler {
	generatorOptions := dataGenerator.Options{
		UseExamples:     container.configuration.UseExamples,
		NullProbability: container.configuration.NullProbability,
	}

	dataGeneratorInstance := dataGenerator.New(generatorOptions)
	responseGeneratorInstance := responseGenerator.New(dataGeneratorInstance)
	apiResponder := responder.New()

	var httpHandler http.Handler
	httpHandler = handler.NewResponseGeneratorHandler(router, responseGeneratorInstance, apiResponder)
	httpHandler = middleware.ContextLoggerHandler(container.logger, httpHandler)
	httpHandler = middleware.TracingHandler(httpHandler)

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
