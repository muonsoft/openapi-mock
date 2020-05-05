package generator

import (
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"testing"
)

func TestCoordinatingSchemaGenerator_GenerateDataBySchema_MatchingType_DataGeneratedBySpecificGenerator(t *testing.T) {
	mockGenerator := &mockSchemaGenerator{}
	coordinatingGenerator := &coordinatingSchemaGenerator{
		generatorsByType: map[string]schemaGenerator{
			"type": mockGenerator,
		},
	}
	schema := &openapi3.Schema{Type: "type"}
	generatedData := Data{}
	mockGenerator.On("GenerateDataBySchema", schema).Return(generatedData, nil).Once()

	data, err := coordinatingGenerator.GenerateDataBySchema(schema)

	mockGenerator.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, generatedData, data)
}
