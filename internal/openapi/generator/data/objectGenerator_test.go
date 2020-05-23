package data

import (
	"context"
	"errors"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"testing"
)

func TestObjectGenerator_GenerateDataBySchema_ObjectWithOneProperty_PropertyGeneratedBySchemaGenerator(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	objectGeneratorInstance := &objectGenerator{
		schemaGenerator: schemaGeneratorMock,
	}
	propertySchema := &openapi3.Schema{}
	schema := &openapi3.Schema{
		Type: "object",
		Properties: map[string]*openapi3.SchemaRef{
			"propertyName": {
				Value: propertySchema,
			},
		},
	}
	schemaGeneratorMock.On("GenerateDataBySchema", mock.Anything, propertySchema).Return("propertyValue", nil).Once()

	data, err := objectGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	schemaGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "propertyValue", data.(map[string]interface{})["propertyName"])
}

func TestObjectGenerator_GenerateDataBySchema_ObjectWithOneWriteOnlyProperty_EmptyObject(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	objectGeneratorInstance := &objectGenerator{
		schemaGenerator: schemaGeneratorMock,
	}
	propertySchema := &openapi3.Schema{}
	propertySchema.WriteOnly = true
	schema := &openapi3.Schema{
		Type: "object",
		Properties: map[string]*openapi3.SchemaRef{
			"propertyName": {
				Value: propertySchema,
			},
		},
	}

	data, err := objectGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	schemaGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Len(t, data, 0)
}

func TestObjectGenerator_GenerateDataBySchema_SchemaGeneratorError_ErrorReturned(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	objectGeneratorInstance := &objectGenerator{
		schemaGenerator: schemaGeneratorMock,
	}
	propertySchema := &openapi3.Schema{}
	schema := &openapi3.Schema{
		Type: "object",
		Properties: map[string]*openapi3.SchemaRef{
			"propertyName": {
				Value: propertySchema,
			},
		},
	}
	schemaGeneratorMock.On("GenerateDataBySchema", mock.Anything, propertySchema).Return(nil, errors.New("error")).Once()

	data, err := objectGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	schemaGeneratorMock.AssertExpectations(t)
	assert.EqualError(t, err, "[objectGenerator] failed to generate object property 'propertyName': error")
	assert.Nil(t, data)
}
