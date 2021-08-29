package data

import (
	"context"

	"github.com/getkin/kin-openapi/openapi3"
	"github.com/muonsoft/openapi-mock/internal/enum"
)

type exampleSchemaGenerator struct {
	useExamples     enum.UseExamples
	schemaGenerator Generator
}

func (generator *exampleSchemaGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (interface{}, error) {
	if schema.Example != nil {
		return schema.Example, nil
	}

	if generator.useExamples == enum.Exclusively {
		return schema.Default, nil
	}

	return generator.schemaGenerator.GenerateDataBySchema(ctx, schema)
}
