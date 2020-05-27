package data

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

	lengthGenerator := &randomArrayLengthGenerator{random: random}
	keyGenerator := &camelCaseKeyGenerator{random: random}

	combinedGenerator := &combinedSchemaGenerator{
		merger: &combinedSchemaMerger{random: random},
	}

	generatorsByType := map[string]schemaGenerator{
		"string":  newStringGenerator(random),
		"boolean": &booleanGenerator{random: random},
		"integer": &integerGenerator{
			random:         random,
			defaultMinimum: options.DefaultMinInt,
			defaultMaximum: options.DefaultMaxInt,
		},
		"number": &numberGenerator{
			random:         random,
			defaultMinimum: options.DefaultMinFloat,
			defaultMaximum: options.DefaultMaxFloat,
		},
		"array":  newArrayGenerator(lengthGenerator),
		"object": newObjectGenerator(lengthGenerator, keyGenerator),
		"oneOf":  &oneOfGenerator{random: random},
		"allOf":  combinedGenerator,
		"anyOf":  combinedGenerator,
	}

	schemaGenerator := createCoordinatingSchemaGenerator(options, generatorsByType, random)

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

func createCoordinatingSchemaGenerator(options Options, generatorsByType map[string]schemaGenerator, random *rand.Rand) schemaGenerator {
	var schemaGenerator schemaGenerator

	schemaGenerator = &coordinatingSchemaGenerator{
		generatorsByType: generatorsByType,
	}

	if options.SuppressErrors {
		schemaGenerator = &errorSuppressor{schemaGenerator: schemaGenerator}
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

	schemaGenerator = &recursionBreaker{schemaGenerator: schemaGenerator}

	return schemaGenerator
}
