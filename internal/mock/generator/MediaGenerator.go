package generator

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"math/rand"
	"time"
)

type MediaGenerator interface {
	GenerateData(ctx context.Context, mediaType *openapi3.MediaType) (Data, error)
}

func New() MediaGenerator {
	randomSource := rand.NewSource(time.Now().UnixNano())

	generatorsByType := map[string]schemaGenerator{
		"object": &objectGenerator{},
		"string": &stringGenerator{rand.New(randomSource)},
	}

	schemaGenerator := &coordinatingSchemaGenerator{
		generatorsByType: generatorsByType,
	}

	for i := range generatorsByType {
		if generator, ok := generatorsByType[i].(recursiveGenerator); ok == true {
			generator.SetSchemaGenerator(schemaGenerator)
		}
	}

	return &coordinatingMediaGenerator{
		schemaGenerator: schemaGenerator,
	}
}
