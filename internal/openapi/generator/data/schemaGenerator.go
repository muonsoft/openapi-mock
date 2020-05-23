package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
)

type schemaGenerator interface {
	GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error)
}
