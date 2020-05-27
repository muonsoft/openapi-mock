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
		Servers: *givenServers(),
		Paths: map[string]*openapi3.PathItem{
			"path": {
				Servers: *givenServers(),
				Connect: &openapi3.Operation{Servers: givenServers()},
				Delete:  &openapi3.Operation{Servers: givenServers()},
				Get:     &openapi3.Operation{Servers: givenServers()},
				Head:    &openapi3.Operation{Servers: givenServers()},
				Options: &openapi3.Operation{Servers: givenServers()},
				Patch:   &openapi3.Operation{Servers: givenServers()},
				Post:    &openapi3.Operation{Servers: givenServers()},
				Put:     &openapi3.Operation{Servers: givenServers()},
				Trace:   &openapi3.Operation{Servers: givenServers()},
			},
		},
	}
	loaderMock.On("LoadFromURI", uri).Return(originalSwagger, nil).Once()

	swagger, err := loader.LoadFromURI(uri)

	loaderMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Len(t, swagger.Servers, 1)
	assert.Equal(t, "/path", swagger.Servers[0].URL)
	assert.Equal(t, "/path", swagger.Paths["path"].Servers[0].URL)
	assert.Equal(t, "/path", (*swagger.Paths["path"].Connect.Servers)[0].URL)
	assert.Equal(t, "/path", (*swagger.Paths["path"].Delete.Servers)[0].URL)
	assert.Equal(t, "/path", (*swagger.Paths["path"].Get.Servers)[0].URL)
	assert.Equal(t, "/path", (*swagger.Paths["path"].Head.Servers)[0].URL)
	assert.Equal(t, "/path", (*swagger.Paths["path"].Options.Servers)[0].URL)
	assert.Equal(t, "/path", (*swagger.Paths["path"].Patch.Servers)[0].URL)
	assert.Equal(t, "/path", (*swagger.Paths["path"].Post.Servers)[0].URL)
	assert.Equal(t, "/path", (*swagger.Paths["path"].Put.Servers)[0].URL)
	assert.Equal(t, "/path", (*swagger.Paths["path"].Trace.Servers)[0].URL)
}

func givenServers() *openapi3.Servers {
	return &openapi3.Servers{
		{
			URL: "https://localhost:1345/path?param=value",
		},
	}
}
