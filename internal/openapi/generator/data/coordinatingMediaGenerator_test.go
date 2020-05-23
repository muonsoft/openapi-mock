package data

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

func TestCoordinatingMediaGenerator_GenerateData_UseExamplesOptionAndGivenExample_ExpectedData(t *testing.T) {
	const randomData = "randomData"
	const exampleData = "exampleData"

	schemaGeneratorMock := &mockSchemaGenerator{}
	mediaGenerator := &coordinatingMediaGenerator{
		schemaGenerator: schemaGeneratorMock,
	}
	schema := openapi3.NewSchema()
	mediaType := &openapi3.MediaType{
		Schema: openapi3.NewSchemaRef("", schema),
	}
	schemaGeneratorMock.On("GenerateDataBySchema", mock.Anything, schema).Return(randomData, nil)

	tests := []struct {
		name         string
		useExamples  UseExamplesEnum
		example      interface{}
		examples     map[string]*openapi3.ExampleRef
		expectedData interface{}
	}{
		{
			"use examples disabled, no example, no examples",
			No,
			nil,
			nil,
			randomData,
		},
		{
			"use examples disabled, given example, no examples",
			No,
			exampleData,
			nil,
			randomData,
		},
		{
			"use examples disabled, no example, given examples",
			No,
			nil,
			map[string]*openapi3.ExampleRef{
				"example": {
					Value: &openapi3.Example{Value: exampleData},
				},
			},
			randomData,
		},
		{
			"use examples if present, no example, no examples",
			IfPresent,
			nil,
			nil,
			randomData,
		},
		{
			"use examples if present, given example, no examples",
			IfPresent,
			exampleData,
			nil,
			exampleData,
		},
		{
			"use examples if present, no example, given examples",
			IfPresent,
			nil,
			map[string]*openapi3.ExampleRef{
				"example": {
					Value: &openapi3.Example{Value: exampleData},
				},
			},
			exampleData,
		},
		{
			"use examples exclusively, no example, no examples",
			Exclusively,
			nil,
			nil,
			nil,
		},
		{
			"use examples exclusively, given example, no examples",
			Exclusively,
			exampleData,
			nil,
			exampleData,
		},
		{
			"use examples exclusively, no example, given examples",
			Exclusively,
			nil,
			map[string]*openapi3.ExampleRef{
				"example": {
					Value: &openapi3.Example{Value: exampleData},
				},
			},
			exampleData,
		},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			mediaGenerator.useExamples = test.useExamples
			mediaType.Example = test.example
			mediaType.Examples = test.examples

			data, err := mediaGenerator.GenerateData(context.Background(), mediaType)

			assert.NoError(t, err)
			assert.Equal(t, test.expectedData, data)
		})
	}
}
