package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/pkg/errors"
)

type regularArrayGenerator struct {
	lengthGenerator arrayLengthGenerator
	schemaGenerator schemaGenerator
}

func (generator *regularArrayGenerator) SetSchemaGenerator(schemaGenerator schemaGenerator) {
	generator.schemaGenerator = schemaGenerator
}

func (generator *regularArrayGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	var err error

	length := generator.generateRandomLength(schema)
	values := make([]interface{}, length)

	for i := uint64(0); i < length; i++ {
		values[i], err = generator.schemaGenerator.GenerateDataBySchema(ctx, schema.Items.Value)
		if err != nil {
			return values[0:i], errors.WithMessage(err, "[regularArrayGenerator] error occurred while generating array value")
		}
	}

	return values, err
}

func (generator *regularArrayGenerator) generateRandomLength(schema *openapi3.Schema) uint64 {
	var maxItems uint64
	if schema.MaxItems != nil {
		maxItems = *schema.MaxItems
	}

	length, _ := generator.lengthGenerator.GenerateLength(schema.MinItems, maxItems)

	return length
}
