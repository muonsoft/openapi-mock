package generator

import (
	"errors"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/getkin/kin-openapi/openapi3filter"
	"github.com/stretchr/testify/mock"
	"github.com/stretchr/testify/suite"
	"net/http"
	generatormock "swagger-mock/test/mocks/mock/generator"
	negotiatormock "swagger-mock/test/mocks/openapi/generator/negotiator"
	"testing"
)

type CoordinatingGeneratorSuite struct {
	suite.Suite

	statusCodeNegotiator  *negotiatormock.StatusCodeNegotiator
	contentTypeNegotiator *negotiatormock.ContentTypeNegotiator
	mediaGenerator        *generatormock.MediaGenerator

	generator *coordinatingGenerator
}

func (suite *CoordinatingGeneratorSuite) SetupTest() {
	suite.statusCodeNegotiator = &negotiatormock.StatusCodeNegotiator{}
	suite.contentTypeNegotiator = &negotiatormock.ContentTypeNegotiator{}
	suite.mediaGenerator = &generatormock.MediaGenerator{}

	suite.generator = &coordinatingGenerator{
		statusCodeNegotiator:  suite.statusCodeNegotiator,
		contentTypeNegotiator: suite.contentTypeNegotiator,
		mediaGenerator:        suite.mediaGenerator,
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
	route := &openapi3filter.Route{}
	route.Operation = &openapi3.Operation{}
	route.Operation.Responses = openapi3.Responses{
		"200": {
			Value: matchingResponse,
		},
	}
	suite.statusCodeNegotiator.On("NegotiateStatusCode", request, route.Operation.Responses).Return("200", http.StatusOK, nil).Once()
	suite.contentTypeNegotiator.On("NegotiateContentType", request, matchingResponse).Return("contentType").Once()
	suite.mediaGenerator.On("GenerateData", mock.Anything, mediaType).Return("data", nil).Once()

	apiResponse, err := suite.generator.GenerateResponse(request, route)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Equal(http.StatusOK, apiResponse.StatusCode)
	suite.Equal("contentType", apiResponse.ContentType)
	suite.Equal("data", apiResponse.Data)
}

func (suite *CoordinatingGeneratorSuite) TestGenerateResponse_NoMediaType_EmptyResponseReturned() {
	request, _ := http.NewRequest("", "", nil)
	matchingResponse := &openapi3.Response{
		Content: map[string]*openapi3.MediaType{},
	}
	route := &openapi3filter.Route{}
	route.Operation = &openapi3.Operation{}
	route.Operation.Responses = openapi3.Responses{
		"204": {
			Value: matchingResponse,
		},
	}
	suite.statusCodeNegotiator.On("NegotiateStatusCode", request, route.Operation.Responses).Return("204", http.StatusNoContent, nil).Once()
	suite.contentTypeNegotiator.On("NegotiateContentType", request, matchingResponse).Return("").Once()

	apiResponse, err := suite.generator.GenerateResponse(request, route)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Equal(http.StatusNoContent, apiResponse.StatusCode)
	suite.Equal("", apiResponse.ContentType)
	suite.Equal("", apiResponse.Data)
}

func (suite *CoordinatingGeneratorSuite) TestGenerateResponse_DataGenerationError_Error() {
	request, _ := http.NewRequest("", "", nil)
	mediaType := &openapi3.MediaType{}
	matchingResponse := &openapi3.Response{
		Content: map[string]*openapi3.MediaType{
			"contentType": mediaType,
		},
	}
	route := &openapi3filter.Route{}
	route.Operation = &openapi3.Operation{}
	route.Operation.Responses = openapi3.Responses{
		"200": {
			Value: matchingResponse,
		},
	}
	suite.statusCodeNegotiator.On("NegotiateStatusCode", request, route.Operation.Responses).Return("200", http.StatusOK, nil).Once()
	suite.contentTypeNegotiator.On("NegotiateContentType", request, matchingResponse).Return("contentType").Once()
	suite.mediaGenerator.On("GenerateData", mock.Anything, mediaType).Return(nil, errors.New("error")).Once()

	apiResponse, err := suite.generator.GenerateResponse(request, route)

	suite.assertExpectations()
	suite.EqualError(err, "[coordinatingGenerator] failed to generate response data: error")
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
	route := &openapi3filter.Route{}
	route.Operation = &openapi3.Operation{}
	route.Operation.Responses = openapi3.Responses{
		"200": {
			Value: matchingResponse,
		},
	}
	suite.statusCodeNegotiator.On("NegotiateStatusCode", request, route.Operation.Responses).Return("", 0, errors.New("error")).Once()

	apiResponse, err := suite.generator.GenerateResponse(request, route)

	suite.assertExpectations()
	suite.EqualError(err, "[coordinatingGenerator] failed to negotiate response: error")
	suite.Nil(apiResponse)
}

func (suite *CoordinatingGeneratorSuite) assertExpectations() {
	suite.statusCodeNegotiator.AssertExpectations(suite.T())
	suite.contentTypeNegotiator.AssertExpectations(suite.T())
	suite.mediaGenerator.AssertExpectations(suite.T())
}
