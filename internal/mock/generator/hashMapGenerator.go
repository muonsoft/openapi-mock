package generator

import (
	"context"
	"errors"
	"fmt"
	"github.com/getkin/kin-openapi/openapi3"
)

type hashMapGenerator struct {
	lengthGenerator arrayLengthGenerator
	keyGenerator    keyGenerator
	schemaGenerator schemaGenerator
}

func (generator *hashMapGenerator) SetSchemaGenerator(schemaGenerator schemaGenerator) {
	generator.schemaGenerator = schemaGenerator
}

func (generator *hashMapGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	keyGenerator := newUniqueKeyGenerator(generator.keyGenerator)

	length, minLength := generator.generateLength(schema)
	values := make(map[string]interface{})

	for _, defaultPropertyName := range schema.Required {
		value, err := generator.schemaGenerator.GenerateDataBySchema(ctx, schema.Properties[defaultPropertyName].Value)
		if err != nil {
			return values, fmt.Errorf("[hashMapGenerator] failed to generate default value '%s': %w", defaultPropertyName, err)
		}

		values[defaultPropertyName] = value
		keyGenerator.AddKey(defaultPropertyName)
	}

	for i := uint64(len(values) + 1); i <= length; i++ {
		key, err := keyGenerator.GenerateKey()
		if err != nil {
			if errors.Is(err, errAttemptsLimitExceeded) {
				if i > minLength {
					return values, nil
				}
			}

			return values, fmt.Errorf("[hashMapGenerator] failed to generate hash map key: %w", err)
		}

		value, err := generator.schemaGenerator.GenerateDataBySchema(ctx, schema.AdditionalProperties.Value)
		if err != nil {
			return values, fmt.Errorf("[hashMapGenerator] failed to generate hash map value: %w", err)
		}

		values[key] = value
	}

	return values, nil
}

func (generator *hashMapGenerator) generateLength(schema *openapi3.Schema) (uint64, uint64) {
	var maxProps uint64
	if schema.MaxProps != nil {
		maxProps = *schema.MaxProps
	}

	return generator.lengthGenerator.GenerateLength(schema.MinProps, maxProps)
}
