package generator

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
)

type coordinatingMediaGenerator struct {
	schemaGenerator schemaGenerator
}

func (generator *coordinatingMediaGenerator) GenerateData(ctx context.Context, mediaType *openapi3.MediaType) (Data, error) {
	return generator.schemaGenerator.GenerateDataBySchema(ctx, mediaType.Schema.Value)
}
