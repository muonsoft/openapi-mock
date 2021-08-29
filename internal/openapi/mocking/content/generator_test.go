package content_test

import (
	"context"
	"errors"
	"testing"

	"github.com/getkin/kin-openapi/openapi3"
	"github.com/muonsoft/openapi-mock/internal/enum"
	apperrors "github.com/muonsoft/openapi-mock/internal/errors"
	"github.com/muonsoft/openapi-mock/internal/openapi/mocking/content"
	datamock "github.com/muonsoft/openapi-mock/test/mocks/openapi/mocking/data"
	"github.com/sirupsen/logrus"
	"github.com/sirupsen/logrus/hooks/test"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"github.com/stretchr/testify/suite"
)

type GeneratorSuite struct {
	suite.Suite

	dataGenerator *datamock.Generator

	logger *logrus.Logger
	hook   *test.Hook

	generator content.Generator
}

func (suite *GeneratorSuite) SetupTest() {
	suite.dataGenerator = &datamock.Generator{}
	suite.logger, suite.hook = test.NewNullLogger()

	suite.generator = content.NewGenerator(enum.No, suite.dataGenerator)
}

func TestGeneratorSuite(t *testing.T) {
	suite.Run(t, new(GeneratorSuite))
}

func (suite *GeneratorSuite) TestGenerateContent_WhenGivenMediaType_ExpectDataGenerated() {
	suite.dataGenerator.On("GenerateDataBySchema", mock.Anything, mock.Anything).Return("generated", nil)

	tests := []struct {
		name         string
		contentType  string
		mediaType    *openapi3.MediaType
		expectedData interface{}
	}{
		{
			name:        "html, no examples",
			contentType: "text/html",
			mediaType: &openapi3.MediaType{
				Schema: &openapi3.SchemaRef{
					Value: &openapi3.Schema{Type: "string", Format: "html"},
				},
			},
			expectedData: "generated",
		},
		{
			name:        "html, single example",
			contentType: "text/html",
			mediaType: &openapi3.MediaType{
				Example: "example",
				Schema: &openapi3.SchemaRef{
					Value: &openapi3.Schema{Type: "string", Format: "html"},
				},
			},
			expectedData: "example",
		},
		{
			name:        "html, multiple examples",
			contentType: "text/html",
			mediaType: &openapi3.MediaType{
				Examples: openapi3.Examples{
					"example": &openapi3.ExampleRef{
						Value: &openapi3.Example{Value: "example"},
					},
				},
				Schema: &openapi3.SchemaRef{
					Value: &openapi3.Schema{Type: "string", Format: "html"},
				},
			},
			expectedData: "example",
		},
		{
			name:        "plain text, no examples",
			contentType: "text/plain",
			mediaType: &openapi3.MediaType{
				Schema: &openapi3.SchemaRef{
					Value: &openapi3.Schema{Type: "string"},
				},
			},
			expectedData: "generated",
		},
		{
			name:        "plain text, single example",
			contentType: "text/plain",
			mediaType: &openapi3.MediaType{
				Example: "example",
				Schema: &openapi3.SchemaRef{
					Value: &openapi3.Schema{Type: "string"},
				},
			},
			expectedData: "example",
		},
		{
			name:        "plain text, multiple examples",
			contentType: "text/plain",
			mediaType: &openapi3.MediaType{
				Examples: openapi3.Examples{
					"example": &openapi3.ExampleRef{
						Value: &openapi3.Example{Value: "example"},
					},
				},
				Schema: &openapi3.SchemaRef{
					Value: &openapi3.Schema{Type: "string"},
				},
			},
			expectedData: "example",
		},
	}
	for _, test := range tests {
		suite.T().Run(test.name, func(t *testing.T) {
			response := givenContentResponse(test.contentType, test.mediaType)
			generator := content.NewGenerator(enum.IfPresent, suite.dataGenerator)

			data, err := generator.GenerateContent(context.Background(), response, test.contentType)

			assert.NoError(t, err)
			assert.Equal(t, test.expectedData, data)
		})
	}
}

func (suite *GeneratorSuite) TestGenerateContent_WhenNoContentType_ExpectEmptyString() {
	response := &openapi3.Response{}

	data, err := suite.generator.GenerateContent(context.Background(), response, "")

	suite.NoError(err)
	suite.Equal("", data)
}

func (suite *GeneratorSuite) TestGenerateContent_WhenNoMatchingProcessorFound_ExpectUnsupportedError() {
	response := &openapi3.Response{}

	data, err := suite.generator.GenerateContent(context.Background(), response, "unknown/media")

	suite.EqualError(err, "generating response for content type 'unknown/media' is not supported")
	var notSupported apperrors.NotSupportedError
	suite.True(errors.As(err, &notSupported))
	suite.Nil(data)
}

func (suite *GeneratorSuite) TestGenerateContent_WhenContentType_ExpectSupportStatus() {
	tests := []struct {
		contentType string
		isSupported bool
	}{
		{"application/json", true},
		{"application/ld+json", true},
		{"application/xml", true},
		{"application/soap+xml", true},
		{"text/plain", true},
		{"text/html", true},
		{"not/supported", false},
	}
	for _, test := range tests {
		suite.T().Run(test.contentType, func(t *testing.T) {
			response := &openapi3.Response{
				Content: map[string]*openapi3.MediaType{
					test.contentType: {
						Schema: openapi3.NewSchemaRef("", openapi3.NewSchema()),
					},
				},
			}
			suite.dataGenerator.On("GenerateDataBySchema", mock.Anything, mock.Anything).Return("data", nil)

			_, err := suite.generator.GenerateContent(context.Background(), response, test.contentType)

			assert.Equal(t, test.isSupported, err == nil)
		})
	}
}

func givenContentResponse(contentType string, mediaType *openapi3.MediaType) *openapi3.Response {
	return &openapi3.Response{
		Content: map[string]*openapi3.MediaType{
			contentType: mediaType,
		},
	}
}

func (suite *GeneratorSuite) assertWarningWasLogged(message string) {
	suite.Equal(logrus.WarnLevel, suite.hook.LastEntry().Level)
	suite.Equal(message, suite.hook.LastEntry().Message)
}
