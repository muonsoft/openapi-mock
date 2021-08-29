package content

import (
	"context"

	"github.com/getkin/kin-openapi/openapi3"
	"github.com/muonsoft/openapi-mock/pkg/logcontext"
)

type plainTextGenerator struct {
	mediaGenerator *mediaGenerator
}

func (generator *plainTextGenerator) GenerateContent(ctx context.Context, response *openapi3.Response, contentType string) (interface{}, error) {
	originMediaType := response.Content.Get(contentType)

	schema := originMediaType.Schema

	if schema == nil || schema.Value.Type != "string" {
		logcontext.Warnf(ctx, "only string schema is supported for '%s' content type", contentType)

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

	return generator.mediaGenerator.GenerateData(ctx, mediaType)
}
