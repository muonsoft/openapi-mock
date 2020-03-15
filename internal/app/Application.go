package app

import (
	"log"
	"swagger-mock/internal/app/config"
	"swagger-mock/internal/app/server"
)

type Application interface {
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

func NewApplication(config congfig.Config) Application {
	httpServer := server.NewServer(config.Port, nil, nil)

	return &application{httpServer}
}

