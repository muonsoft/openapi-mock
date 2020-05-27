package content

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/muonsoft/openapi-mock/internal/openapi/generator/data"
	"regexp"
)

type Generator interface {
	GenerateContent(ctx context.Context, response *openapi3.Response, contentType string) (interface{}, error)
}

func NewGenerator(generator data.MediaGenerator) Generator {
	mediaGenerator := &mediaGenerator{contentGenerator: generator}

	return &delegatingGenerator{
		matchers: []contentMatcher{
			{
				pattern:   regexp.MustCompile("^application/.*json$"),
				generator: mediaGenerator,
			},
			{
				pattern:   regexp.MustCompile("^application/.*xml$"),
				generator: mediaGenerator,
			},
			{
				pattern:   regexp.MustCompile("^text/html$"),
				generator: &htmlGenerator{contentGenerator: generator},
			},
			{
				pattern:   regexp.MustCompile("^text/plain$"),
				generator: &plainTextGenerator{contentGenerator: generator},
			},
		},
	}
}
