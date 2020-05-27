package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/pkg/errors"
)

type uniqueArrayGenerator struct {
	lengthGenerator arrayLengthGenerator
	schemaGenerator schemaGenerator
}

func (generator *uniqueArrayGenerator) SetSchemaGenerator(schemaGenerator schemaGenerator) {
	generator.schemaGenerator = schemaGenerator
}

func (generator *uniqueArrayGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	valueGenerator := newUniqueValueGenerator(generator.schemaGenerator)

	length, minLength := generator.generateLength(schema)
	values := make([]interface{}, length)

	for i := uint64(1); i <= length; i++ {
		data, err := valueGenerator.GenerateDataBySchema(ctx, schema.Items.Value)
		if errors.Is(err, errAttemptsLimitExceeded) && i > minLength {
			return values[0 : i-1], nil
		}
		if err != nil {
			return values[0 : i-1], errors.WithMessage(err, "[uniqueArrayGenerator] failed to generate array with unique values")
		}

		values[i-1] = data
	}

	return values, nil
}

func (generator *uniqueArrayGenerator) generateLength(schema *openapi3.Schema) (uint64, uint64) {
	var maxItems uint64
	if schema.MaxItems != nil {
		maxItems = *schema.MaxItems
	}

	return generator.lengthGenerator.GenerateLength(schema.MinItems, maxItems)
}
