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

func New(options Options) MediaGenerator {
	randomSource := rand.NewSource(time.Now().UnixNano())
	random := rand.New(randomSource)

	generatorsByType := map[string]schemaGenerator{
		"object": &objectGenerator{},
		"string": &stringGenerator{random},
	}

	var schemaGenerator schemaGenerator

	schemaGenerator = &coordinatingSchemaGenerator{
		generatorsByType: generatorsByType,
	}

	if options.UseExamples != No {
		schemaGenerator = &exampleSchemaGenerator{
			useExamples:     options.UseExamples,
			schemaGenerator: schemaGenerator,
		}
	}

	if options.NullProbability > 0 {
		schemaGenerator = &nullGenerator{
			nullProbability: options.NullProbability,
			random:          random,
			schemaGenerator: schemaGenerator,
		}
	}

	for i := range generatorsByType {
		if generator, ok := generatorsByType[i].(recursiveGenerator); ok {
			generator.SetSchemaGenerator(schemaGenerator)
		}
	}

	return &coordinatingMediaGenerator{
		useExamples:     options.UseExamples,
		schemaGenerator: schemaGenerator,
	}
}
