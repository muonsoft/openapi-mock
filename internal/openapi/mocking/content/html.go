package content

import (
	"context"

	"github.com/getkin/kin-openapi/openapi3"
	"github.com/muonsoft/openapi-mock/pkg/logcontext"
)

type htmlGenerator struct {
	mediaGenerator *mediaGenerator
}

func (generator *htmlGenerator) GenerateContent(ctx context.Context, response *openapi3.Response, contentType string) (interface{}, error) {
	originMediaType := response.Content.Get(contentType)

	schema := originMediaType.Schema

	if schema == nil || schema.Value.Type != "string" || schema.Value.Format != "html" {
		logcontext.Warnf(ctx, "only string schema with html format is supported for '%s' content type", contentType)

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

	return generator.mediaGenerator.GenerateData(ctx, mediaType)
}
