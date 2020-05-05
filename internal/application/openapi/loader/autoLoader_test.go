package loader

import (
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"testing"
)

func TestAutoLoader_LoadFromURI_FileName_SpecificationLoadedFromFile(t *testing.T) {
	const filename = "/file/path/specification.json"
	swaggerLoader := &mockExternalLoader{}
	loader := &autoLoader{loader: swaggerLoader}
	expectedSwagger := &openapi3.Swagger{}
	swaggerLoader.On("LoadSwaggerFromFile", filename).Return(expectedSwagger, nil)

	swagger, err := loader.LoadFromURI(filename)

	assert.NoError(t, err)
	assert.NotNil(t, swagger)
	swaggerLoader.AssertExpectations(t)
}

func TestAutoLoader_LoadFromURI_URL_SpecificationLoadedFromURL(t *testing.T) {
	swaggerLoader := &mockExternalLoader{}
	loader := &autoLoader{loader: swaggerLoader}
	expectedSwagger := &openapi3.Swagger{}
	swaggerLoader.On("LoadSwaggerFromURI", mock.Anything).Return(expectedSwagger, nil)

	swagger, err := loader.LoadFromURI("http://localhost/specification.json")

	assert.NoError(t, err)
	assert.NotNil(t, swagger)
	swaggerLoader.AssertExpectations(t)
}
