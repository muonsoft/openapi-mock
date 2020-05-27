package content

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/muonsoft/openapi-mock/internal/openapi/generator/data"
	"github.com/muonsoft/openapi-mock/pkg/logcontext"
)

type plainTextGenerator struct {
	contentGenerator data.MediaGenerator
}

func (generator *plainTextGenerator) GenerateContent(ctx context.Context, response *openapi3.Response, contentType string) (interface{}, error) {
	originMediaType := response.Content.Get(contentType)

	schema := originMediaType.Schema

	if schema == nil || schema.Value.Type != "string" {
		logger := logcontext.LoggerFromContext(ctx)
		logger.Warnf("only string schema is supported for '%s' content type", contentType)

		schema = &openapi3.SchemaRef{
			Value: &openapi3.Schema{
				Type: "string",
			},
		}
	}

	mediaType := &openapi3.MediaType{
		Schema:   schema,
		Example:  originMediaType.Example,
		Examples: originMediaType.Examples,
	}

	return generator.contentGenerator.GenerateData(ctx, mediaType)
}
