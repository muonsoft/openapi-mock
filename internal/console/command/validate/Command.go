package validate

import (
	"log"
	"swagger-mock/internal/application/config"
	diContainer "swagger-mock/internal/application/container"
	"swagger-mock/internal/openapi/loader"
)

type Command struct {
	specificationURL    string
	specificationLoader loader.SpecificationLoader
}

func NewCommand(configuration config.Configuration) *Command {
	container := diContainer.New(configuration)

	return &Command{
		specificationURL:    configuration.SpecificationURL,
		specificationLoader: container.CreateSpecificationLoader(),
	}
}

func (command *Command) Execute() error {
	_, err := command.specificationLoader.LoadFromURI(command.specificationURL)

	if err != nil {
		log.Fatalf("failed to load OpenAPI specification from '%s': %s", command.specificationURL, err)
	} else {
		log.Printf("OpenAPI specification '%s' is valid", command.specificationURL)
	}

	return nil
}
