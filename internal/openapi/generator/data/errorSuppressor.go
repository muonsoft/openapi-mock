package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/muonsoft/openapi-mock/pkg/logcontext"
)

type errorSuppressor struct {
	schemaGenerator schemaGenerator
}

func (suppressor *errorSuppressor) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	data, err := suppressor.schemaGenerator.GenerateDataBySchema(ctx, schema)
	if err == nil {
		return data, err
	}

	logger := logcontext.LoggerFromContext(ctx)
	logger.Errorf("generation error was suppressed (default value is used): %s", err)

	return schema.Default, nil
}
