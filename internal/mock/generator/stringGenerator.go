package generator

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
)

type stringGenerator struct{}

func (generator *stringGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	return "value", nil
}
