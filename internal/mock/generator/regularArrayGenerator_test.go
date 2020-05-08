package generator

import (
	"context"
	"errors"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"testing"
)

func TestRegularArrayGenerator_GenerateDataBySchema_RandomLength_ArrayOfLengthGenerated(t *testing.T) {
	lengthGeneratorMock := &mockArrayLengthGenerator{}
	schemaGeneratorMock := &mockSchemaGenerator{}
	arrayGenerator := &regularArrayGenerator{
		lengthGenerator: lengthGeneratorMock,
		schemaGenerator: schemaGeneratorMock,
	}
	itemsSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.Items = openapi3.NewSchemaRef("", itemsSchema)
	lengthGeneratorMock.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(2), uint64(0)).Once()
	schemaGeneratorMock.On("GenerateDataBySchema", mock.Anything, itemsSchema).Return("value", nil).Twice()

	data, err := arrayGenerator.GenerateDataBySchema(context.Background(), schema)

	lengthGeneratorMock.AssertExpectations(t)
	schemaGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Len(t, data, 2)
	assert.Equal(t, "value", data.([]interface{})[0])
}

func TestRegularArrayGenerator_GenerateDataBySchema_GivenItemsLength_ArrayOfLengthGenerated(t *testing.T) {
	lengthGeneratorMock := &mockArrayLengthGenerator{}
	schemaGeneratorMock := &mockSchemaGenerator{}
	arrayGenerator := &regularArrayGenerator{
		lengthGenerator: lengthGeneratorMock,
		schemaGenerator: schemaGeneratorMock,
	}
	itemsSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.Items = openapi3.NewSchemaRef("", itemsSchema)
	schema.MinItems = 1
	schema.MaxItems = &schema.MinItems
	lengthGeneratorMock.On("GenerateLength", uint64(1), uint64(1)).Return(uint64(1), uint64(0)).Once()
	schemaGeneratorMock.On("GenerateDataBySchema", mock.Anything, itemsSchema).Return("value", nil).Once()

	data, err := arrayGenerator.GenerateDataBySchema(context.Background(), schema)

	lengthGeneratorMock.AssertExpectations(t)
	schemaGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Len(t, data, 1)
	assert.Equal(t, "value", data.([]interface{})[0])
}

func TestRegularArrayGenerator_GenerateDataBySchema_SecondValuesLeadsToError_ReducedArrayAndError(t *testing.T) {
	lengthGeneratorMock := &mockArrayLengthGenerator{}
	schemaGeneratorMock := &mockSchemaGenerator{}
	arrayGenerator := &regularArrayGenerator{
		lengthGenerator: lengthGeneratorMock,
		schemaGenerator: schemaGeneratorMock,
	}
	itemsSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.Items = openapi3.NewSchemaRef("", itemsSchema)
	lengthGeneratorMock.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(2), uint64(0)).Once()
	schemaGeneratorMock.
		On("GenerateDataBySchema", mock.Anything, itemsSchema).Return("value", nil).Once().
		On("GenerateDataBySchema", mock.Anything, itemsSchema).Return(nil, errors.New("error")).Once()

	data, err := arrayGenerator.GenerateDataBySchema(context.Background(), schema)

	lengthGeneratorMock.AssertExpectations(t)
	schemaGeneratorMock.AssertExpectations(t)
	assert.EqualError(t, err, "[regularArrayGenerator] error occurred while generating array value: error")
	assert.Len(t, data, 1)
	assert.Equal(t, "value", data.([]interface{})[0])
}
