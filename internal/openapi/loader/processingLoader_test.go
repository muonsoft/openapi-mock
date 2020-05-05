package loader

import (
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"testing"
)

func TestProcessingLoader_LoadFromURI_ServerUrlHasSchemeAndHost_OnlyPathInServerUrl(t *testing.T) {
	const uri = "uri"
	loaderMock := &MockSpecificationLoader{}
	loader := &processingLoader{loader: loaderMock}
	originalSwagger := &openapi3.Swagger{
		Servers: []*openapi3.Server{
			{
				URL: "https://localhost:1345/path?param=value",
			},
		},
	}
	loaderMock.On("LoadFromURI", uri).Return(originalSwagger, nil).Once()

	swagger, err := loader.LoadFromURI(uri)

	loaderMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Len(t, swagger.Servers, 1)
	assert.Equal(t, "/path", swagger.Servers[0].URL)
}
