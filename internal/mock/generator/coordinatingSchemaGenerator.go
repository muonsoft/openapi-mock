package generator

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
)

type coordinatingSchemaGenerator struct {
	generatorsByType map[string]schemaGenerator
}

func (generator *coordinatingSchemaGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	specificGenerator := generator.generatorsByType[schema.Type]

	return specificGenerator.GenerateDataBySchema(ctx, schema)
}
