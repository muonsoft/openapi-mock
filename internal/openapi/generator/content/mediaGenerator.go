package content

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"swagger-mock/internal/mock/generator"
)

type mediaGenerator struct {
	contentGenerator generator.MediaGenerator
}

func (generator *mediaGenerator) GenerateContent(ctx context.Context, response *openapi3.Response, contentType string) (interface{}, error) {
	mediaType := response.Content[contentType]
	if mediaType == nil {
		return "", nil
	}

	return generator.contentGenerator.GenerateData(ctx, mediaType)
}
