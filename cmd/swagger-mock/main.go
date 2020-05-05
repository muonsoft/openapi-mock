package main

import (
	"swagger-mock/internal/infrastructure/di/config"
	"swagger-mock/internal/infrastructure/openapi"
)

func main() {
	configuration := config.LoadConfigFromEnvironment()
	app := openapi.NewMockServer(configuration)
	app.Run()
}
