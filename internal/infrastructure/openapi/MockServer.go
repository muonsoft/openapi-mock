package openapi

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"github.com/sirupsen/logrus"
	"log"
	"swagger-mock/internal/infrastructure/di/config"
	diContainer "swagger-mock/internal/infrastructure/di/container"
	"swagger-mock/internal/infrastructure/server"
)

type MockServer interface {
	Run()
}

type mockServer struct {
	server server.Server
}

func (app *mockServer) Run() {
	err := app.server.Run()

	if err != nil {
		log.Fatal(err)
	}
}

func NewMockServer(config config.Configuration) MockServer {
	container := diContainer.New(config)
	loggerWriter := container.GetLogger().(*logrus.Logger).Writer()

	specificationLoader := container.CreateSpecificationLoader()
	specification, err := specificationLoader.LoadFromURI(config.SpecificationUrl)
	if err != nil {
		log.Fatalf("failed to load OpenAPI specification from '%s': %s", config.SpecificationUrl, err)
	}

	router := openapi3filter.NewRouter().WithSwagger(specification)
	httpHandler := container.CreateOpenApiHandler(router)

	serverLogger := log.New(loggerWriter, "[HTTP]: ", log.LstdFlags)
	httpServer := server.New(config.Port, httpHandler, serverLogger)

	return &mockServer{httpServer}
}
