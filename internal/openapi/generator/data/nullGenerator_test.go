package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"testing"
)

func TestNullGenerator_GenerateDataBySchema_NullableValueAndRandomValueLessThanProbability_GeneratedDataReturned(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	random := &mockRandomGenerator{}
	generator := &nullGenerator{
		nullProbability: 0.5,
		random:          random,
		schemaGenerator: schemaGeneratorMock,
	}
	schema := openapi3.NewSchema()
	schema.Nullable = true
	schemaGeneratorMock.On("GenerateDataBySchema", mock.Anything, schema).Return("data", nil).Once()
	random.On("Float64").Return(0.5).Once()

	data, err := generator.GenerateDataBySchema(context.Background(), schema)

	random.AssertExpectations(t)
	schemaGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "data", data)
}

func TestNullGenerator_GenerateDataBySchema_NullableValueAndRandomValueMoreThanProbability_NullReturned(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	random := &mockRandomGenerator{}
	generator := &nullGenerator{
		nullProbability: 0.5,
		random:          random,
		schemaGenerator: schemaGeneratorMock,
	}
	schema := openapi3.NewSchema()
	schema.Nullable = true
	random.On("Float64").Return(0.49).Once()

	data, err := generator.GenerateDataBySchema(context.Background(), schema)

	random.AssertExpectations(t)
	schemaGeneratorMock.AssertNotCalled(t, "GenerateDataBySchema")
	assert.NoError(t, err)
	assert.Nil(t, data)
}

func TestNullGenerator_GenerateDataBySchema_NotNullableValueAndRandomValueMoreThanProbability_GeneratedDataReturned(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	random := &mockRandomGenerator{}
	generator := &nullGenerator{
		nullProbability: 0.5,
		random:          random,
		schemaGenerator: schemaGeneratorMock,
	}
	schema := openapi3.NewSchema()
	schema.Nullable = false
	schemaGeneratorMock.On("GenerateDataBySchema", mock.Anything, schema).Return("data", nil).Once()

	data, err := generator.GenerateDataBySchema(context.Background(), schema)

	schemaGeneratorMock.AssertNotCalled(t, "GenerateDataBySchema")
	assert.NoError(t, err)
	assert.Equal(t, "data", data)
}
