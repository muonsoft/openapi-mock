package data

import (
	"context"
	"fmt"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/pkg/errors"
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
		return nil, errors.WithStack(&ErrGenerationFailed{
			GeneratorID: "combinedSchemaGenerator",
			Message:     fmt.Sprintf("schema '%s' is not a combining schema", schema.Title),
		})
	}

	return generator.schemaGenerator.GenerateDataBySchema(ctx, mergedSchema)
}
