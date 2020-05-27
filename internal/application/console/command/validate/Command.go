package validate

import (
	"context"
	"github.com/muonsoft/openapi-mock/internal/openapi/loader"
	"log"
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
	specification, err := command.specificationLoader.LoadFromURI(command.specificationURL)
	if err != nil {
		log.Fatalf("failed to load OpenAPI specification from '%s': %s", command.specificationURL, err)
	}

	err = specification.Validate(context.Background())
	if err != nil {
		log.Fatalf("OpenAPI specification '%s' is not valid: %s", command.specificationURL, err)
	}

	log.Printf("OpenAPI specification '%s' is valid", command.specificationURL)

	return nil
}
