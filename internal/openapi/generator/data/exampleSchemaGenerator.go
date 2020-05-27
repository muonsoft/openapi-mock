package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
)

type exampleSchemaGenerator struct {
	useExamples     UseExamplesEnum
	schemaGenerator schemaGenerator
}

func (generator *exampleSchemaGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	if schema.Example != nil {
		return schema.Example, nil
	}

	if generator.useExamples == Exclusively {
		return schema.Default, nil
	}

	return generator.schemaGenerator.GenerateDataBySchema(ctx, schema)
}
