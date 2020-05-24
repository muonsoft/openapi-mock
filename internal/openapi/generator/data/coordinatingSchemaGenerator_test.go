package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"testing"
)

func TestCoordinatingSchemaGenerator_GenerateDataBySchema_NotSupportedType_Error(t *testing.T) {
	coordinatingGenerator := &coordinatingSchemaGenerator{
		generatorsByType: map[string]schemaGenerator{},
	}
	schema := &openapi3.Schema{Type: "type"}

	data, err := coordinatingGenerator.GenerateDataBySchema(context.Background(), schema)

	assert.EqualError(t, err, "[coordinatingSchemaGenerator] data generation for objects of type 'type' is not supported")
	assert.Nil(t, data)
}

func TestCoordinatingSchemaGenerator_GenerateDataBySchema_MatchingType_DataGeneratedBySpecificGenerator(t *testing.T) {
	mockGenerator := &mockSchemaGenerator{}
	coordinatingGenerator := &coordinatingSchemaGenerator{
		generatorsByType: map[string]schemaGenerator{
			"type": mockGenerator,
		},
	}
	schema := &openapi3.Schema{Type: "type"}
	generatedData := map[string]interface{}{}
	mockGenerator.On("GenerateDataBySchema", mock.Anything, schema).Return(generatedData, nil).Once()

	data, err := coordinatingGenerator.GenerateDataBySchema(context.Background(), schema)

	mockGenerator.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, generatedData, data)
}

func TestCoordinatingSchemaGenerator_GenerateDataBySchema_SchemaWithEmptyTypeAndProperties_DataGeneratedByObjectGenerator(t *testing.T) {
	mockGenerator := &mockSchemaGenerator{}
	coordinatingGenerator := &coordinatingSchemaGenerator{
		generatorsByType: map[string]schemaGenerator{
			"object": mockGenerator,
		},
	}
	schema := &openapi3.Schema{
		Properties: map[string]*openapi3.SchemaRef{
			"key": openapi3.NewSchemaRef("", openapi3.NewSchema()),
		},
	}
	generatedData := map[string]interface{}{}
	mockGenerator.On("GenerateDataBySchema", mock.Anything, schema).Return(generatedData, nil).Once()

	data, err := coordinatingGenerator.GenerateDataBySchema(context.Background(), schema)

	mockGenerator.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, generatedData, data)
}

func TestCoordinatingSchemaGenerator_GenerateDataBySchema_CombiningType_DataGeneratedBySpecificGenerator(t *testing.T) {
	tests := []struct {
		combiningType string
		schema        *openapi3.Schema
	}{
		{
			"oneOf",
			&openapi3.Schema{
				OneOf: []*openapi3.SchemaRef{},
			},
		},
		{
			"anyOf",
			&openapi3.Schema{
				AnyOf: []*openapi3.SchemaRef{},
			},
		},
		{
			"allOf",
			&openapi3.Schema{
				AllOf: []*openapi3.SchemaRef{},
			},
		},
	}
	for _, test := range tests {
		t.Run(test.combiningType, func(t *testing.T) {
			mockGenerator := &mockSchemaGenerator{}
			coordinatingGenerator := &coordinatingSchemaGenerator{
				generatorsByType: map[string]schemaGenerator{
					test.combiningType: mockGenerator,
				},
			}
			generatedData := map[string]interface{}{}
			mockGenerator.On("GenerateDataBySchema", mock.Anything, test.schema).Return(generatedData, nil).Once()

			data, err := coordinatingGenerator.GenerateDataBySchema(context.Background(), test.schema)

			mockGenerator.AssertExpectations(t)
			assert.NoError(t, err)
			assert.Equal(t, generatedData, data)
		})
	}
}
