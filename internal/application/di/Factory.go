package di

import (
	"fmt"
	"io/ioutil"
	"log"
	"net/http"
	"os"

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
)

type Factory struct {
	configuration *config.Configuration
	logger        logrus.FieldLogger
}

func NewFactory(configuration *config.Configuration) *Factory {
	logger := createLogger(configuration)

	return &Factory{
		configuration: configuration,
		logger:        logger,
	}
}

func init() {
	openapi3.DefineStringFormat("uuid", openapi3.FormatOfStringForUUIDOfRFC4122)
	openapi3.DefineStringFormat("html", "<[^>]+>|&[^;]+;")
}

func (factory *Factory) GetLogger() logrus.FieldLogger {
	return factory.logger
}

func (factory *Factory) CreateSpecificationLoader() loader.SpecificationLoader {
	return loader.New()
}

func (factory *Factory) CreateHTTPHandler(router *openapi3filter.Router) http.Handler {
	generatorOptions := data.Options{
		UseExamples:     factory.configuration.UseExamples,
		NullProbability: factory.configuration.NullProbability,
		DefaultMinInt:   factory.configuration.DefaultMinInt,
		DefaultMaxInt:   factory.configuration.DefaultMaxInt,
		DefaultMinFloat: factory.configuration.DefaultMinFloat,
		DefaultMaxFloat: factory.configuration.DefaultMaxFloat,
		SuppressErrors:  factory.configuration.SuppressErrors,
	}

	dataGeneratorInstance := data.New(generatorOptions)
	responseGeneratorInstance := responseGenerator.New(dataGeneratorInstance)
	apiResponder := responder.New()

	var httpHandler http.Handler
	httpHandler = handler.NewResponseGeneratorHandler(router, responseGeneratorInstance, apiResponder)
	if factory.configuration.CORSEnabled {
		httpHandler = middleware.CORSHandler(httpHandler)
	}

	secureMiddleware := secure.New(secure.Options{
		FrameDeny:             true,
		ContentTypeNosniff:    true,
		BrowserXssFilter:      true,
		ContentSecurityPolicy: "default-src 'self'",
	})

	httpHandler = secureMiddleware.Handler(httpHandler)
	httpHandler = middleware.ContextLoggerHandler(factory.logger, httpHandler)
	httpHandler = middleware.TracingHandler(httpHandler)
	httpHandler = handlers.CombinedLoggingHandler(os.Stdout, httpHandler)
	httpHandler = handlers.RecoveryHandler(
		handlers.RecoveryLogger(factory.logger),
		handlers.PrintRecoveryStack(true),
	)(httpHandler)
	httpHandler = http.TimeoutHandler(httpHandler, factory.configuration.ResponseTimeout, "")

	return httpHandler
}

func (factory *Factory) CreateHTTPServer() (server.Server, error) {
	logger := factory.GetLogger()
	loggerWriter := logger.(*logrus.Logger).Writer()

	specificationLoader := factory.CreateSpecificationLoader()
	specification, err := specificationLoader.LoadFromURI(factory.configuration.SpecificationURL)
	if err != nil {
		return nil, fmt.Errorf("failed to load OpenAPI specification from '%s': %w", factory.configuration.SpecificationURL, err)
	}

	logger.Infof("OpenAPI specification was successfully loaded from '%s'", factory.configuration.SpecificationURL)

	router := openapi3filter.NewRouter().WithSwagger(specification)
	httpHandler := factory.CreateHTTPHandler(router)

	serverLogger := log.New(loggerWriter, "[HTTP]: ", log.LstdFlags)
	httpServer := server.New(factory.configuration.Port, httpHandler, serverLogger)

	logger.WithFields(factory.configuration.Dump()).Info("OpenAPI mock server was created")

	return httpServer, nil
}

func createLogger(configuration *config.Configuration) *logrus.Logger {
	logger := logrus.New()
	if configuration.DryRun {
		logger.Out = ioutil.Discard
		return logger
	}

	logger.SetLevel(configuration.LogLevel)

	if configuration.LogFormat == "json" {
		logger.SetFormatter(&logrus.JSONFormatter{})
	} else {
		logger.SetFormatter(&logrus.TextFormatter{})
	}

	return logger
}
