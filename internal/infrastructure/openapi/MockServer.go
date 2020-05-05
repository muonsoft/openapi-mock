package openapi

import (
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

	serverLogger := log.New(loggerWriter, "[HTTP]: ", log.LstdFlags)
	httpServer := server.New(config.Port, nil, serverLogger)

	return &mockServer{httpServer}
}
