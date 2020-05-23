package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
)

type nullGenerator struct {
	nullProbability float64
	random          randomGenerator
	schemaGenerator schemaGenerator
}

func (generator *nullGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	if schema.Nullable && generator.random.Float64() < generator.nullProbability {
		return nil, nil
	}

	return generator.schemaGenerator.GenerateDataBySchema(ctx, schema)
}
