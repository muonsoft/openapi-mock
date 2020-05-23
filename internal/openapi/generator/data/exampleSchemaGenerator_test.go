package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"testing"
)

func TestExampleSchemaGenerator_GenerateDataBySchema_ExamplePresented_ExampleReturned(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	exampleGenerator := &exampleSchemaGenerator{
		schemaGenerator: schemaGeneratorMock,
	}
	schema := openapi3.NewSchema()
	schema.Example = "exampleData"

	data, err := exampleGenerator.GenerateDataBySchema(context.Background(), schema)

	assert.NoError(t, err)
	assert.Equal(t, "exampleData", data)
}

func TestExampleSchemaGenerator_GenerateDataBySchema_NoExamplePresentedAndUseExamplesIsPresent_GeneratedDataReturned(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	exampleGenerator := &exampleSchemaGenerator{
		schemaGenerator: schemaGeneratorMock,
	}
	schema := openapi3.NewSchema()
	schemaGeneratorMock.On("GenerateDataBySchema", mock.Anything, schema).Return("randomData", nil)

	data, err := exampleGenerator.GenerateDataBySchema(context.Background(), schema)

	schemaGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "randomData", data)
}

func TestExampleSchemaGenerator_GenerateDataBySchema_NoExamplePresentedAndUseExamplesExclusively_DefaultValueReturned(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	exampleGenerator := &exampleSchemaGenerator{
		useExamples:     Exclusively,
		schemaGenerator: schemaGeneratorMock,
	}
	schema := openapi3.NewSchema()
	schema.Default = "defaultData"

	data, err := exampleGenerator.GenerateDataBySchema(context.Background(), schema)

	assert.NoError(t, err)
	assert.Equal(t, "defaultData", data)
}
