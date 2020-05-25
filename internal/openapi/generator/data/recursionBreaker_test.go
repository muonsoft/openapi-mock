package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"testing"
)

func TestRecursionBreaker_GenerateDataBySchema_NoLevel_FirstLevelAdded(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	breaker := &recursionBreaker{schemaGenerator: schemaGeneratorMock}
	schema := openapi3.NewSchema()
	schemaGeneratorMock.On("GenerateDataBySchema", mock.MatchedBy(func(ctx context.Context) bool {
		level, exists := ctx.Value(recursionKey).(int)
		assert.True(t, exists)
		return assert.Equal(t, 1, level)
	}), schema).Return("data", nil).Once()

	data, err := breaker.GenerateDataBySchema(context.Background(), schema)

	schemaGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "data", data)
}

func TestRecursionBreaker_GenerateDataBySchema_GivenLevel_LevelIncremented(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	breaker := &recursionBreaker{schemaGenerator: schemaGeneratorMock}
	schema := openapi3.NewSchema()
	schemaGeneratorMock.On("GenerateDataBySchema", mock.MatchedBy(func(ctx context.Context) bool {
		level, exists := ctx.Value(recursionKey).(int)
		assert.True(t, exists)
		return assert.Equal(t, 6, level)
	}), schema).Return("data", nil).Once()

	nextContext := context.WithValue(context.Background(), recursionKey, 5)
	data, err := breaker.GenerateDataBySchema(nextContext, schema)

	schemaGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "data", data)
}

func TestRecursionBreaker_GenerateDataBySchema_MaxLevelReached_Error(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	breaker := &recursionBreaker{schemaGenerator: schemaGeneratorMock}
	schema := openapi3.NewSchema()

	nextContext := context.WithValue(context.Background(), recursionKey, maxRecursionLevel)
	data, err := breaker.GenerateDataBySchema(nextContext, schema)

	assert.Nil(t, data)
	assert.EqualError(t, err, "[recursionBreaker] max recursion level reached")
}
