package content

import (
	"context"
	"errors"
	"github.com/getkin/kin-openapi/openapi3"
	apperrors "github.com/muonsoft/openapi-mock/internal/errors"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"regexp"
	"testing"
)

func TestDelegatingGenerator_GenerateContent_MatchingProcessorFound_ResponseProcessedByMatchingProcessor(t *testing.T) {
	matchingGenerator := &MockGenerator{}
	generator := &delegatingGenerator{
		matchers: []contentMatcher{
			{
				pattern:   regexp.MustCompile("^application/.*json$"),
				generator: matchingGenerator,
			},
		},
	}
	contentType := "application/any-json"
	response := &openapi3.Response{}
	matchingGenerator.On("GenerateContent", mock.Anything, response, contentType).Return("data", nil).Once()

	content, err := generator.GenerateContent(context.Background(), response, contentType)

	matchingGenerator.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "data", content)
}

func TestDelegatingGenerator_GenerateContent_NoMatchingProcessorFound_MediaTypeAndError(t *testing.T) {
	generator := &delegatingGenerator{
		matchers: []contentMatcher{},
	}
	contentType := "contentType"
	response := &openapi3.Response{}

	content, err := generator.GenerateContent(context.Background(), response, contentType)

	assert.EqualError(t, err, "generating response for content type 'contentType' is not supported")
	var notSupported *apperrors.NotSupported
	assert.True(t, errors.As(err, &notSupported))
	assert.Nil(t, content)
}

func TestDelegatingGenerator_GenerateContent_NoContentType_EmptyString(t *testing.T) {
	generator := &delegatingGenerator{
		matchers: []contentMatcher{},
	}
	response := &openapi3.Response{}

	content, err := generator.GenerateContent(context.Background(), response, "")

	assert.NoError(t, err)
	assert.Equal(t, "", content)
}
