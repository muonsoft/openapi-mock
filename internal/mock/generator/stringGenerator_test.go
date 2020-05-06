package generator

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"math/rand"
	"testing"
)

func TestStringGenerator_GenerateDataBySchema_Enum_PropertyGeneratedBySchemaGenerator(t *testing.T) {
	stringGeneratorInstance := &stringGenerator{
		*rand.New(rand.NewSource(1)),
	}
	schema := &openapi3.Schema{
		Type: "string",
		Enum: []interface{}{"enumValue"},
	}

	data, err := stringGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	assert.NoError(t, err)
	assert.Equal(t, "enumValue", data)
}
