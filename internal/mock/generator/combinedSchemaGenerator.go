package generator

import (
	"context"
	"fmt"
	"github.com/getkin/kin-openapi/openapi3"
)

type combinedSchemaGenerator struct {
	merger          schemaMerger
	schemaGenerator schemaGenerator
}

func (generator *combinedSchemaGenerator) SetSchemaGenerator(schemaGenerator schemaGenerator) {
	generator.schemaGenerator = schemaGenerator
}

func (generator *combinedSchemaGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	mergedSchema := generator.merger.MergeSchemas(schema)
	if mergedSchema == nil {
		return nil, fmt.Errorf("[combinedSchemaGenerator] schema '%s' is not a combining schema", schema.Title)
	}

	return generator.schemaGenerator.GenerateDataBySchema(ctx, mergedSchema)
}
