package application

import (
	"log"
	"swagger-mock/internal/application/config"
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

func NewApplication(config config.Config) SwaggerMock {
	httpServer := server.New(config.Port, nil, nil)

	return &application{httpServer}
}
