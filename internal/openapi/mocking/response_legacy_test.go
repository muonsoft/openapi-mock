package mocking

import (
	"errors"
	"net/http"
	"testing"

	"github.com/getkin/kin-openapi/openapi3"
	"github.com/getkin/kin-openapi/routers"
	contentmock "github.com/muonsoft/openapi-mock/test/mocks/openapi/generator/content"
	"github.com/stretchr/testify/mock"
	"github.com/stretchr/testify/suite"
)

type CoordinatingGeneratorSuite struct {
	suite.Suite

	contentGenerator *contentmock.Generator

	generator *ResponseMocker
}

func (suite *CoordinatingGeneratorSuite) SetupTest() {
	suite.contentGenerator = &contentmock.Generator{}

	suite.generator = &ResponseMocker{
		contentGenerator: suite.contentGenerator,
	}
}

func TestCoordinatingGeneratorSuite(t *testing.T) {
	suite.Run(t, new(CoordinatingGeneratorSuite))
}

func (suite *CoordinatingGeneratorSuite) TestGenerateResponse_RouteWithValidResponse_ResponseGeneratedAndReturned() {
	request, _ := http.NewRequest("", "", nil)
	mediaType := &openapi3.MediaType{}
	matchingResponse := &openapi3.Response{
		Content: map[string]*openapi3.MediaType{
			"contentType": mediaType,
		},
	}
	route := &routers.Route{}
	route.Operation = &openapi3.Operation{}
	route.Operation.Responses = openapi3.Responses{
		"200": {
			Value: matchingResponse,
		},
	}
	suite.contentGenerator.On("GenerateContent", mock.Anything, matchingResponse, "contentType").Return("data", nil).Once()

	apiResponse, err := suite.generator.GenerateResponse(request, route)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Equal(http.StatusOK, apiResponse.StatusCode)
	suite.Equal("contentType", apiResponse.ContentType)
	suite.Equal("data", apiResponse.Data)
}

func (suite *CoordinatingGeneratorSuite) TestGenerateResponse_ContentGenerationError_Error() {
	request, _ := http.NewRequest("", "", nil)
	mediaType := &openapi3.MediaType{}
	matchingResponse := &openapi3.Response{
		Content: map[string]*openapi3.MediaType{
			"contentType": mediaType,
		},
	}
	route := &routers.Route{}
	route.Operation = &openapi3.Operation{}
	route.Operation.Responses = openapi3.Responses{
		"200": {
			Value: matchingResponse,
		},
	}
	suite.contentGenerator.On("GenerateContent", mock.Anything, matchingResponse, "contentType").Return(nil, errors.New("error")).Once()

	apiResponse, err := suite.generator.GenerateResponse(request, route)

	suite.assertExpectations()
	suite.EqualError(err, "[ResponseMocker] failed to generate response data: error")
	suite.Nil(apiResponse)
}

func (suite *CoordinatingGeneratorSuite) TestGenerateResponse_StatusCodeNegotiationError_Error() {
	request, _ := http.NewRequest("", "", nil)
	mediaType := &openapi3.MediaType{}
	matchingResponse := &openapi3.Response{
		Content: map[string]*openapi3.MediaType{
			"contentType": mediaType,
		},
	}
	route := &routers.Route{}
	route.Operation = &openapi3.Operation{}
	route.Operation.Responses = openapi3.Responses{
		"200": {
			Value: matchingResponse,
		},
	}

	apiResponse, err := suite.generator.GenerateResponse(request, route)

	suite.assertExpectations()
	suite.EqualError(err, "[ResponseMocker] failed to negotiate response: error")
	suite.Nil(apiResponse)
}

func (suite *CoordinatingGeneratorSuite) assertExpectations() {
	suite.contentGenerator.AssertExpectations(suite.T())
}
