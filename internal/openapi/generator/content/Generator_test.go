package content

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	generatormock "github.com/muonsoft/openapi-mock/test/mocks/mock/generator"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"testing"
)

func TestContentProcessor(t *testing.T) {
	tests := []struct {
		contentType string
		isSupported bool
	}{
		{
			"application/json",
			true,
		},
		{
			"application/ld+json",
			true,
		},
		{
			"application/xml",
			true,
		},
		{
			"application/soap+xml",
			true,
		},
		{
			"text/plain",
			true,
		},
		{
			"text/html",
			true,
		},
		{
			"not/supported",
			false,
		},
	}
	for _, test := range tests {
		t.Run(test.contentType, func(t *testing.T) {
			mediaGenerator := &generatormock.MediaGenerator{}
			generator := NewGenerator(mediaGenerator)
			response := &openapi3.Response{
				Content: map[string]*openapi3.MediaType{
					test.contentType: {},
				},
			}
			mediaGenerator.On("GenerateData", mock.Anything, mock.Anything).Return("data", nil)

			_, err := generator.GenerateContent(context.Background(), response, test.contentType)

			assert.Equal(t, test.isSupported, err == nil)
		})
	}
}
