package main

import (
	"os"
	"swagger-mock/internal/application"
	"swagger-mock/internal/application/config"
)

func main() {
	os.Setenv("SWAGGER_MOCK_PORT", "8080")
	os.Setenv("SWAGGER_MOCK_LOG_LEVEL", "info")
	os.Setenv("SWAGGER_MOCK_LOG_FORMAT", "tty")
	os.Setenv("SWAGGER_MOCK_DEBUG", "0")

	configuration := config.LoadConfigFromEnvironment()
	app := application.New(configuration)
	app.Run()
}
