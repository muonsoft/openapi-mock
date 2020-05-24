package check

import (
	"log"
	"swagger-mock/internal/openapi/loader"
)

type Command struct {
	specificationURL    string
	specificationLoader loader.SpecificationLoader
}

func NewCommand(specificationURL string, specificationLoader loader.SpecificationLoader) *Command {
	return &Command{
		specificationURL:    specificationURL,
		specificationLoader: specificationLoader,
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
