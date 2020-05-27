package content

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/muonsoft/openapi-mock/pkg/logcontext"
	generatormock "github.com/muonsoft/openapi-mock/test/mocks/mock/generator"
	"github.com/sirupsen/logrus"
	"github.com/sirupsen/logrus/hooks/test"
	"github.com/stretchr/testify/mock"
	"github.com/stretchr/testify/suite"
	"testing"
)

type HTMLGeneratorSuite struct {
	suite.Suite

	contentGenerator *generatormock.MediaGenerator

	logger *logrus.Logger
	hook   *test.Hook

	examples map[string]*openapi3.ExampleRef

	htmlGenerator *htmlGenerator
}

func (suite *HTMLGeneratorSuite) SetupTest() {
	suite.contentGenerator = &generatormock.MediaGenerator{}
	suite.logger, suite.hook = test.NewNullLogger()

	suite.examples = map[string]*openapi3.ExampleRef{}

	suite.htmlGenerator = &htmlGenerator{
		contentGenerator: suite.contentGenerator,
	}
}

func TestHtmlGeneratorSuite(t *testing.T) {
	suite.Run(t, new(HTMLGeneratorSuite))
}

func (suite *HTMLGeneratorSuite) TestHtmlProcessor_GenerateContent_ResponseWithString_GeneratedDataWithoutWarnings() {
	response := suite.givenResponse()
	suite.contentGenerator.On("GenerateData", mock.Anything, suite.expectedMediaType()).Return("data", nil).Once()

	content, err := suite.htmlGenerator.GenerateContent(context.Background(), response, "text/html")

	suite.contentGenerator.AssertExpectations(suite.T())
	suite.NoError(err)
	suite.Equal("data", content)
}

func (suite *HTMLGeneratorSuite) TestHtmlProcessor_GenerateContent_ResponseWithObject_Warning() {
	response := suite.givenResponse()
	response.Content["text/html"].Schema.Value.Type = "object"
	ctx := logcontext.WithLogger(context.Background(), suite.logger)
	suite.contentGenerator.On("GenerateData", mock.Anything, suite.expectedMediaType()).Return("data", nil).Once()

	content, err := suite.htmlGenerator.GenerateContent(ctx, response, "text/html")

	suite.contentGenerator.AssertExpectations(suite.T())
	suite.NoError(err)
	suite.Equal("data", content)
	suite.assertWarningWasLogged()
}

func (suite *HTMLGeneratorSuite) TestHtmlProcessor_GenerateContent_ResponseWithoutSchema_Warning() {
	response := suite.givenResponse()
	response.Content["text/html"].Schema = nil
	ctx := logcontext.WithLogger(context.Background(), suite.logger)
	suite.contentGenerator.On("GenerateData", mock.Anything, suite.expectedMediaType()).Return("data", nil).Once()

	content, err := suite.htmlGenerator.GenerateContent(ctx, response, "text/html")

	suite.contentGenerator.AssertExpectations(suite.T())
	suite.NoError(err)
	suite.Equal("data", content)
	suite.assertWarningWasLogged()
}

func (suite *HTMLGeneratorSuite) givenResponse() *openapi3.Response {
	return &openapi3.Response{
		Content: map[string]*openapi3.MediaType{
			"text/html": {
				Example:  "example",
				Examples: suite.examples,
				Schema: &openapi3.SchemaRef{
					Value: &openapi3.Schema{
						Type: "string",
					},
				},
			},
		},
	}
}

func (suite *HTMLGeneratorSuite) expectedMediaType() interface{} {
	return mock.MatchedBy(func(mediaType *openapi3.MediaType) bool {
		suite.Equal("string", mediaType.Schema.Value.Type)
		suite.Equal("html", mediaType.Schema.Value.Format)
		suite.Equal("example", mediaType.Example)
		suite.Equal(suite.examples, mediaType.Examples)

		return true
	})
}

func (suite *HTMLGeneratorSuite) assertWarningWasLogged() {
	suite.Len(suite.hook.Entries, 1)
	suite.Equal(logrus.WarnLevel, suite.hook.LastEntry().Level)
	suite.Equal("only string schema with html format is supported for 'text/html' content type", suite.hook.LastEntry().Message)
}
