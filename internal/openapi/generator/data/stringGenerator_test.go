package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"testing"
)

func TestStringGenerator_GenerateDataBySchema_SchemaWithOneEnum_EnumValueReturned(t *testing.T) {
	randomMock := &mockRandomGenerator{}
	stringGeneratorInstance := &stringGenerator{
		random: randomMock,
	}
	schema := &openapi3.Schema{
		Type: "string",
		Enum: []interface{}{"enumValue"},
	}
	randomMock.On("Intn", 1).Return(0).Once()

	data, err := stringGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	randomMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "enumValue", data)
}

func TestStringGenerator_GenerateDataBySchema_SchemaWithEmptyEnum_RandomTextReturned(t *testing.T) {
	randomMock := &mockRandomGenerator{}
	textGeneratorMock := &mockSchemaGenerator{}
	stringGeneratorInstance := &stringGenerator{
		random:        randomMock,
		textGenerator: textGeneratorMock,
	}
	schema := &openapi3.Schema{
		Type: "string",
		Enum: []interface{}{},
	}
	textGeneratorMock.On("GenerateDataBySchema", mock.Anything, schema).Return("generatedText", nil).Once()

	data, err := stringGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	textGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.NotEqual(t, "enumValue", data)
}

func TestStringGenerator_GenerateDataBySchema_SchemaWithPattern_RegExpGeneratedValueReturned(t *testing.T) {
	stringGeneratorInstance := &stringGenerator{}
	schema := &openapi3.Schema{
		Type:    "string",
		Pattern: "/ABC/",
	}

	data, err := stringGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	assert.NoError(t, err)
	assert.Equal(t, "ABC", data)
}

func TestStringGenerator_GenerateDataBySchema_SchemaWithSupportedFormat_FormattedValueReturned(t *testing.T) {
	stringGeneratorInstance := &stringGenerator{
		formatGenerators: map[string]stringGeneratorFunction{
			"format": func(_ int, _ int) string {
				return "formatValue"
			},
		},
	}
	schema := &openapi3.Schema{
		Type:   "string",
		Format: "format",
	}

	data, err := stringGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	assert.NoError(t, err)
	assert.Equal(t, "formatValue", data)
}

func TestStringGenerator_GenerateDataBySchema_SchemaWithoutOptions_GeneratedTextReturned(t *testing.T) {
	textGeneratorMock := &mockSchemaGenerator{}
	stringGeneratorInstance := &stringGenerator{
		textGenerator: textGeneratorMock,
	}
	schema := &openapi3.Schema{Type: "string"}
	textGeneratorMock.On("GenerateDataBySchema", mock.Anything, schema).Return("generatedText", nil).Once()

	data, err := stringGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	textGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "generatedText", data)
}
