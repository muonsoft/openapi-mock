package content

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	generatormock "github.com/muonsoft/openapi-mock/test/mocks/mock/generator"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"testing"
)

func TestMediaGenerator_GenerateContent_ResponseWithContent_GeneratedMediaDataReturned(t *testing.T) {
	contentGenerator := &generatormock.MediaGenerator{}
	generator := &mediaGenerator{contentGenerator: contentGenerator}
	mediaType := &openapi3.MediaType{}
	response := &openapi3.Response{
		Content: map[string]*openapi3.MediaType{
			"contentType": mediaType,
		},
	}
	contentGenerator.On("GenerateData", mock.Anything, mediaType).Return("data", nil).Once()

	content, err := generator.GenerateContent(context.Background(), response, "contentType")

	contentGenerator.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "data", content)
}

func TestMediaGenerator_GenerateContent_ResponseWithoutContent_EmptyDataReturned(t *testing.T) {
	contentGenerator := &generatormock.MediaGenerator{}
	generator := &mediaGenerator{contentGenerator: contentGenerator}
	response := &openapi3.Response{
		Content: map[string]*openapi3.MediaType{},
	}

	content, err := generator.GenerateContent(context.Background(), response, "contentType")

	contentGenerator.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "", content)
}
