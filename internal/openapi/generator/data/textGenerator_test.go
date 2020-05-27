package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"math/rand"
	"testing"
	"time"
)

func TestTextGenerator_GenerateDataBySchema_MaxLengthLessThanMinLength_Error(t *testing.T) {
	textGeneratorInstance := &textGenerator{}
	schema := openapi3.NewSchema()
	var maxLength uint64 = 4
	schema.MinLength = 5
	schema.MaxLength = &maxLength

	data, err := textGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	assert.EqualError(t, err, "[textGenerator] max length cannot be less than min length")
	assert.Equal(t, "", data)
}

func TestTextGenerator_GenerateDataBySchema_MaxLengthLessThan5_RandomStringOfGivenLength(t *testing.T) {
	textGeneratorInstance := &textGenerator{}
	schema := openapi3.NewSchema()
	var maxLength uint64 = 4
	schema.MaxLength = &maxLength

	data, err := textGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	assert.NoError(t, err)
	assert.Len(t, data, 4)
}

func TestTextGenerator_GenerateDataBySchema_MinAndMaxLength_LengthOfStringInRange(t *testing.T) {
	randomSource := rand.NewSource(time.Now().UnixNano())
	textGeneratorInstance := &textGenerator{
		generator: &rangedTextGenerator{
			random: rand.New(randomSource),
		},
	}
	schema := openapi3.NewSchema()
	var maxLength uint64 = 1000
	schema.MinLength = 10
	schema.MaxLength = &maxLength

	for i := 0; i < 1000; i++ {
		data, err := textGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

		assert.NoError(t, err)
		assert.GreaterOrEqual(t, len(data.(string)), 10)
		assert.LessOrEqual(t, len(data.(string)), 1000)
	}
}

func TestTextGenerator_GenerateDataBySchema_NoOptions_LengthOfStringInDefaultRange(t *testing.T) {
	randomSource := rand.NewSource(time.Now().UnixNano())
	textGeneratorInstance := &textGenerator{
		generator: &rangedTextGenerator{
			random: rand.New(randomSource),
		},
	}
	schema := openapi3.NewSchema()

	for i := 0; i < 1000; i++ {
		data, err := textGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

		assert.NoError(t, err)
		assert.GreaterOrEqual(t, len(data.(string)), 0)
		assert.LessOrEqual(t, len(data.(string)), defaultMaxLength)
	}
}

func TestTextGenerator_GenerateDataBySchema_MinLengthGreaterThanDefaultMaxLength_LengthOfStringInExpectedRange(t *testing.T) {
	randomSource := rand.NewSource(time.Now().UnixNano())
	textGeneratorInstance := &textGenerator{
		generator: &rangedTextGenerator{
			random: rand.New(randomSource),
		},
	}
	schema := openapi3.NewSchema()
	schema.MinLength = defaultMaxLength

	for i := 0; i < 1000; i++ {
		data, err := textGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

		assert.NoError(t, err)
		assert.GreaterOrEqual(t, len(data.(string)), defaultMaxLength)
		assert.LessOrEqual(t, len(data.(string)), 2*defaultMaxLength)
	}
}

func TestTextGenerator_GenerateDataBySchema_StrictLength_LengthOfStringHasExpectedLength(t *testing.T) {
	randomSource := rand.NewSource(time.Now().UnixNano())
	textGeneratorInstance := &textGenerator{
		generator: &rangedTextGenerator{
			random: rand.New(randomSource),
		},
	}
	schema := openapi3.NewSchema()
	var maxLength uint64 = defaultMaxLength
	schema.MinLength = defaultMaxLength
	schema.MaxLength = &maxLength

	for i := 0; i < 1000; i++ {
		data, err := textGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

		assert.NoError(t, err)
		assert.Len(t, data, defaultMaxLength)
	}
}
