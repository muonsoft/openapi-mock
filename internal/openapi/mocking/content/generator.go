package content

import (
	"context"
	"fmt"
	"regexp"

	"github.com/getkin/kin-openapi/openapi3"
	"github.com/muonsoft/openapi-mock/internal/enum"
	"github.com/muonsoft/openapi-mock/internal/errors"
)

type Generator interface {
	GenerateContent(ctx context.Context, response *openapi3.Response, contentType string) (interface{}, error)
}

type DataGenerator interface {
	GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (interface{}, error)
}

func NewGenerator(useExamples enum.UseExamples, dataGenerator DataGenerator) Generator {
	mediaGenerator := &mediaGenerator{
		useExamples:   useExamples,
		dataGenerator: dataGenerator,
	}
	contentGenerator := &contentGenerator{
		mediaGenerator: mediaGenerator,
	}

	return multiplexer{
		{
			pattern:   regexp.MustCompile("^application/.*json$"),
			generator: contentGenerator,
		},
		{
			pattern:   regexp.MustCompile("^application/.*xml$"),
			generator: contentGenerator,
		},
		{
			pattern:   regexp.MustCompile("^text/html$"),
			generator: &htmlGenerator{mediaGenerator: mediaGenerator},
		},
		{
			pattern:   regexp.MustCompile("^text/plain$"),
			generator: &plainTextGenerator{mediaGenerator: mediaGenerator},
		},
	}
}

type multiplexer []contentMatcher

type contentMatcher struct {
	pattern   *regexp.Regexp
	generator Generator
}

func (mux multiplexer) GenerateContent(ctx context.Context, response *openapi3.Response, contentType string) (interface{}, error) {
	if contentType == "" {
		return "", nil
	}

	for _, matcher := range mux {
		if matcher.pattern.MatchString(contentType) {
			return matcher.generator.GenerateContent(ctx, response, contentType)
		}
	}

	return nil, errors.NotSupported(fmt.Sprintf("generating response for content type '%s' is not supported", contentType))
}
