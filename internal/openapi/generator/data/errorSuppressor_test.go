package data

import (
	"context"
	"errors"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/muonsoft/openapi-mock/pkg/logcontext"
	"github.com/sirupsen/logrus"
	"github.com/sirupsen/logrus/hooks/test"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"testing"
)

func TestErrorSuppressor_GenerateDataBySchema_GeneratorReturnsValue_ValueReturned(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	suppressor := &errorSuppressor{schemaGenerator: schemaGeneratorMock}
	schema := &openapi3.Schema{}
	schemaGeneratorMock.On("GenerateDataBySchema", mock.Anything, schema).Return("value", nil).Once()

	data, err := suppressor.GenerateDataBySchema(context.Background(), schema)

	schemaGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "value", data)
}

func TestErrorSuppressor_GenerateDataBySchema_GeneratorReturnsError_ErrorIsLoggedAndDefaultValueReturned(t *testing.T) {
	schemaGeneratorMock := &mockSchemaGenerator{}
	suppressor := &errorSuppressor{schemaGenerator: schemaGeneratorMock}
	schema := &openapi3.Schema{
		Default: "default",
	}
	schemaGeneratorMock.On("GenerateDataBySchema", mock.Anything, schema).Return(nil, errors.New("error")).Once()
	logger, hook := test.NewNullLogger()
	ctx := logcontext.WithLogger(context.Background(), logger)

	data, err := suppressor.GenerateDataBySchema(ctx, schema)

	schemaGeneratorMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "default", data)
	assert.Len(t, hook.Entries, 1)
	assert.Equal(t, logrus.ErrorLevel, hook.LastEntry().Level)
	assert.Equal(t, "generation error was suppressed (default value is used): error", hook.LastEntry().Message)
}
