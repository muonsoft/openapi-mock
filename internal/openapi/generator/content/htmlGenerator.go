package content

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"swagger-mock/internal/mock/generator"
	"swagger-mock/pkg/logcontext"
)

type htmlGenerator struct {
	contentGenerator generator.MediaGenerator
}

func (generator *htmlGenerator) GenerateContent(ctx context.Context, response *openapi3.Response, contentType string) (interface{}, error) {
	originMediaType := response.Content.Get(contentType)

	schema := originMediaType.Schema

	if schema == nil || schema.Value.Type != "string" || schema.Value.Format != "html" {
		logger := logcontext.LoggerFromContext(ctx)
		logger.Warnf("only string schema with html format is supported for '%s' content type", contentType)

		schema = &openapi3.SchemaRef{
			Value: &openapi3.Schema{
				Type:   "string",
				Format: "html",
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
