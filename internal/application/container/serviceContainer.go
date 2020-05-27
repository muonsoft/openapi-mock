package container

import (
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/getkin/kin-openapi/openapi3filter"
	"github.com/gorilla/handlers"
	"github.com/muonsoft/openapi-mock/internal/application/config"
	responseGenerator "github.com/muonsoft/openapi-mock/internal/openapi/generator"
	"github.com/muonsoft/openapi-mock/internal/openapi/generator/data"
	"github.com/muonsoft/openapi-mock/internal/openapi/handler"
	"github.com/muonsoft/openapi-mock/internal/openapi/loader"
	"github.com/muonsoft/openapi-mock/internal/openapi/responder"
	"github.com/muonsoft/openapi-mock/internal/server"
	"github.com/muonsoft/openapi-mock/internal/server/middleware"
	"github.com/sirupsen/logrus"
	"github.com/unrolled/secure"
	"log"
	"net/http"
	"os"
)

type serviceContainer struct {
	configuration *config.Configuration
	logger        logrus.FieldLogger
}

func New(configuration *config.Configuration) Container {
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
	generatorOptions := data.Options{
		UseExamples:     container.configuration.UseExamples,
		NullProbability: container.configuration.NullProbability,
		DefaultMinInt:   container.configuration.DefaultMinInt,
		DefaultMaxInt:   container.configuration.DefaultMaxInt,
		DefaultMinFloat: container.configuration.DefaultMinFloat,
		DefaultMaxFloat: container.configuration.DefaultMaxFloat,
		SuppressErrors:  container.configuration.SuppressErrors,
	}

	dataGeneratorInstance := data.New(generatorOptions)
	responseGeneratorInstance := responseGenerator.New(dataGeneratorInstance)
	apiResponder := responder.New()

	var httpHandler http.Handler
	httpHandler = handler.NewResponseGeneratorHandler(router, responseGeneratorInstance, apiResponder)
	if container.configuration.CORSEnabled {
		httpHandler = middleware.CORSHandler(httpHandler)
	}

	secureMiddleware := secure.New(secure.Options{
		FrameDeny:             true,
		ContentTypeNosniff:    true,
		BrowserXssFilter:      true,
		ContentSecurityPolicy: "default-src 'self'",
	})

	httpHandler = secureMiddleware.Handler(httpHandler)
	httpHandler = middleware.ContextLoggerHandler(container.logger, httpHandler)
	httpHandler = middleware.TracingHandler(httpHandler)
	httpHandler = handlers.CombinedLoggingHandler(os.Stdout, httpHandler)
	httpHandler = handlers.RecoveryHandler(
		handlers.RecoveryLogger(container.logger),
		handlers.PrintRecoveryStack(true),
	)(httpHandler)
	httpHandler = http.TimeoutHandler(httpHandler, container.configuration.ResponseTimeout, "")

	return httpHandler
}

func (container *serviceContainer) CreateHTTPServer() server.Server {
	logger := container.GetLogger()
	loggerWriter := logger.(*logrus.Logger).Writer()

	specificationLoader := container.CreateSpecificationLoader()
	specification, err := specificationLoader.LoadFromURI(container.configuration.SpecificationURL)
	if err != nil {
		log.Fatalf("failed to load OpenAPI specification from '%s': %s", container.configuration.SpecificationURL, err)
	} else {
		logger.Infof("OpenAPI specification was successfully loaded from '%s'", container.configuration.SpecificationURL)
	}

	router := openapi3filter.NewRouter().WithSwagger(specification)
	httpHandler := container.CreateHTTPHandler(router)

	serverLogger := log.New(loggerWriter, "[HTTP]: ", log.LstdFlags)
	httpServer := server.New(container.configuration.Port, httpHandler, serverLogger)

	logger.WithFields(container.configuration.Dump()).Info("OpenAPI mock server was created")

	return httpServer
}

func createLogger(configuration *config.Configuration) *logrus.Logger {
	logger := logrus.New()
	logger.SetLevel(configuration.LogLevel)

	if configuration.LogFormat == "json" {
		logger.SetFormatter(&logrus.JSONFormatter{})
	} else {
		logger.SetFormatter(&logrus.TextFormatter{})
	}

	return logger
}
