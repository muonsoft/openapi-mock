package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
)

type booleanGenerator struct {
	random randomGenerator
}

func (generator *booleanGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	value := false

	if generator.random.Float64() < 0.5 {
		value = true
	}

	return value, nil
}
