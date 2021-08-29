package data

import (
	"context"

	"github.com/getkin/kin-openapi/openapi3"
)

type Generator interface {
	GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (interface{}, error)
}
