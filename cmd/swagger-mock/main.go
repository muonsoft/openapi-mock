package main

import (
	"swagger-mock/internal/application"
	"swagger-mock/internal/di/config"
)

func main() {
	configuration := config.LoadConfigFromEnvironment()
	app := application.NewMockServer(configuration)
	app.Run()
}
