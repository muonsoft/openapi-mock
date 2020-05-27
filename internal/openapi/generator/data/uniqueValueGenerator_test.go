package data

import (
	"context"
	"errors"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"testing"
)

func TestUniqueValueGenerator_GenerateDataBySchema_EmptyUniqueValues_FirstValueReturned(t *testing.T) {
	valueGeneratorMock := &mockSchemaGenerator{}
	uniqueGenerator := newUniqueValueGenerator(valueGeneratorMock)
	schema := openapi3.NewSchema()
	valueGeneratorMock.On("GenerateDataBySchema", mock.Anything, schema).Return("data", nil).Once()

	data, err := uniqueGenerator.GenerateDataBySchema(context.Background(), schema)

	valueGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "data", data)
}

func TestUniqueValueGenerator_GenerateDataBySchema_SecondValueIsNotUnique_ThirdValueReturned(t *testing.T) {
	valueGeneratorMock := &mockSchemaGenerator{}
	uniqueGenerator := newUniqueValueGenerator(valueGeneratorMock)
	schema := openapi3.NewSchema()
	valueGeneratorMock.
		On("GenerateDataBySchema", mock.Anything, schema).Return("notUnique", nil).Once().
		On("GenerateDataBySchema", mock.Anything, schema).Return("notUnique", nil).Once().
		On("GenerateDataBySchema", mock.Anything, schema).Return("unique", nil).Once()

	_, _ = uniqueGenerator.GenerateDataBySchema(context.Background(), schema)
	data, err := uniqueGenerator.GenerateDataBySchema(context.Background(), schema)

	valueGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "unique", data)
}

func TestUniqueValueGenerator_GenerateDataBySchema_FailedToGenerateUniqueValue_ErrorReturned(t *testing.T) {
	valueGeneratorMock := &mockSchemaGenerator{}
	uniqueGenerator := newUniqueValueGenerator(valueGeneratorMock)
	schema := openapi3.NewSchema()
	valueGeneratorMock.On("GenerateDataBySchema", mock.Anything, schema).Return("notUnique", nil).Times(maxAttempts + 1)

	_, _ = uniqueGenerator.GenerateDataBySchema(context.Background(), schema)
	data, err := uniqueGenerator.GenerateDataBySchema(context.Background(), schema)

	valueGeneratorMock.AssertExpectations(t)
	assert.True(t, errors.Is(err, errAttemptsLimitExceeded))
	assert.EqualError(t, err, "[uniqueValueGenerator] failed to generate unique value: attempts limit exceeded")
	assert.Nil(t, data)
}

func TestUniqueValueGenerator_GenerateDataBySchema_GenerationError_ErrorReturned(t *testing.T) {
	valueGeneratorMock := &mockSchemaGenerator{}
	uniqueGenerator := newUniqueValueGenerator(valueGeneratorMock)
	schema := openapi3.NewSchema()
	valueGeneratorMock.On("GenerateDataBySchema", mock.Anything, schema).Return(nil, errors.New("error")).Once()

	data, err := uniqueGenerator.GenerateDataBySchema(context.Background(), schema)

	valueGeneratorMock.AssertExpectations(t)
	assert.EqualError(t, err, "[uniqueValueGenerator] failed to generate value: error")
	assert.Nil(t, data)
}
