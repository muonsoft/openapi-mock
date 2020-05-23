package run

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"github.com/sirupsen/logrus"
	"log"
	"swagger-mock/internal/application/config"
	diContainer "swagger-mock/internal/application/container"
	"swagger-mock/internal/server"
)

type Command struct {
	server server.Server
}

func NewCommand(configuration config.Configuration) *Command {
	container := diContainer.New(configuration)
	loggerWriter := container.GetLogger().(*logrus.Logger).Writer()

	specificationLoader := container.CreateSpecificationLoader()
	specification, err := specificationLoader.LoadFromURI(configuration.SpecificationURL)
	if err != nil {
		log.Fatalf("failed to load OpenAPI specification from '%s': %s", configuration.SpecificationURL, err)
	}

	router := openapi3filter.NewRouter().WithSwagger(specification)
	httpHandler := container.CreateHTTPHandler(router)

	serverLogger := log.New(loggerWriter, "[HTTP]: ", log.LstdFlags)
	httpServer := server.New(configuration.Port, httpHandler, serverLogger)

	return &Command{server: httpServer}
}

func (command *Command) Execute() error {
	return command.server.Run()
}
