package content

import (
	"context"

	"github.com/getkin/kin-openapi/openapi3"
	"github.com/muonsoft/openapi-mock/internal/enum"
)

type contentGenerator struct {
	mediaGenerator *mediaGenerator
}

func (generator *contentGenerator) GenerateContent(ctx context.Context, response *openapi3.Response, contentType string) (interface{}, error) {
	mediaType := response.Content[contentType]
	if mediaType == nil {
		return "", nil
	}

	return generator.mediaGenerator.GenerateData(ctx, mediaType)
}

type mediaGenerator struct {
	useExamples   enum.UseExamples
	dataGenerator DataGenerator
}

func (generator *mediaGenerator) GenerateData(ctx context.Context, mediaType *openapi3.MediaType) (interface{}, error) {
	if generator.useExamples == enum.IfPresent || generator.useExamples == enum.Exclusively {
		if mediaType.Example != nil {
			return mediaType.Example, nil
		}
		if mediaType.Examples != nil {
			for _, example := range mediaType.Examples {
				return example.Value.Value, nil
			}
		}
	}

	if generator.useExamples == enum.Exclusively {
		return nil, nil
	}

	return generator.dataGenerator.GenerateDataBySchema(ctx, mediaType.Schema.Value)
}
