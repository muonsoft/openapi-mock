package generator

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"testing"
)

func TestCoordinatingMediaGenerator_GenerateData_DataSchemaOnly_DataGeneratedBySchema(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	mediaGenerator := &coordinatingMediaGenerator{
		schemaGenerator: schemaGeneratorMock,
	}
	schema := openapi3.NewSchema()
	mediaType := &openapi3.MediaType{
		Schema: openapi3.NewSchemaRef("", schema),
	}
	schemaGeneratorMock.On("GenerateDataBySchema", mock.Anything, schema).Return("data", nil).Once()

	data, err := mediaGenerator.GenerateData(context.Background(), mediaType)

	schemaGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "data", data)
}
