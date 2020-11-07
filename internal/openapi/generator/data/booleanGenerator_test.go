package data

import (
	"context"
	"testing"

	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
)

func TestBooleanGenerator_GenerateDataBySchema_RandomLessThanTheEdge_TrueReturned(t *testing.T) {
	randomMock := &mockRandomGenerator{}
	booleanGeneratorInstance := &booleanGenerator{random: randomMock}
	randomMock.On("Float64").Return(0.499).Once()

	data, err := booleanGeneratorInstance.GenerateDataBySchema(context.Background(), openapi3.NewSchema())

	randomMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.True(t, data.(bool))
}

func TestBooleanGenerator_GenerateDataBySchema_RandomEqualToTheEdge_FalseReturned(t *testing.T) {
	randomMock := &mockRandomGenerator{}
	booleanGeneratorInstance := &booleanGenerator{random: randomMock}
	randomMock.On("Float64").Return(0.5).Once()

	data, err := booleanGeneratorInstance.GenerateDataBySchema(context.Background(), openapi3.NewSchema())

	randomMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.False(t, data.(bool))
}
