package application

import (
	"github.com/sirupsen/logrus"
	"log"
	"swagger-mock/internal/application/config"
	diContainer "swagger-mock/internal/infrastructure/di/container"
	"swagger-mock/internal/infrastructure/server"
)

type SwaggerMock interface {
	Run()
}

type application struct {
	server *server.Server
}

func (app *application) Run() {
	err := app.server.Run()

	if err != nil {
		log.Fatal(err)
	}
}

func New(config config.Configuration) SwaggerMock {
	container := diContainer.New(config)
	loggerWriter := container.Logger.(*logrus.Logger).Writer()

	serverLogger := log.New(loggerWriter, "[HTTP]: ", log.LstdFlags)
	httpServer := server.New(config.Port, nil, serverLogger)

	return &application{httpServer}
}
