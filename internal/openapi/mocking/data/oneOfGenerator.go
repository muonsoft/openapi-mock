package data

import (
	"context"

	"github.com/getkin/kin-openapi/openapi3"
)

type oneOfGenerator struct {
	random          randomGenerator
	schemaGenerator Generator
}

func (generator *oneOfGenerator) SetSchemaGenerator(schemaGenerator Generator) {
	generator.schemaGenerator = schemaGenerator
}

func (generator *oneOfGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (interface{}, error) {
	if len(schema.OneOf) == 0 {
		return map[string]interface{}{}, nil
	}

	n := generator.random.Intn(len(schema.OneOf))

	return generator.schemaGenerator.GenerateDataBySchema(ctx, schema.OneOf[n].Value)
}
