package generator

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
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
	generatedData := map[string]interface{}{}
	mockGenerator.On("GenerateDataBySchema", mock.Anything, schema).Return(generatedData, nil).Once()

	data, err := coordinatingGenerator.GenerateDataBySchema(context.Background(), schema)

	mockGenerator.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, generatedData, data)
}
