package generator

import (
	"fmt"
	"github.com/getkin/kin-openapi/openapi3filter"
	"net/http"
	"swagger-mock/internal/mock/generator"
	"swagger-mock/internal/openapi/generator/negotiator"
)

type coordinatingGenerator struct {
	statusCodeNegotiator  negotiator.StatusCodeNegotiator
	contentTypeNegotiator negotiator.ContentTypeNegotiator
	mediaGenerator        generator.MediaGenerator
}

func (generator *coordinatingGenerator) GenerateResponse(request *http.Request, route *openapi3filter.Route) (*Response, error) {
	responseKey, statusCode, err := generator.statusCodeNegotiator.NegotiateStatusCode(request, route.Operation.Responses)
	if err != nil {
		return nil, fmt.Errorf("[coordinatingGenerator] failed to negotiate response: %w", err)
	}

	bestResponse := route.Operation.Responses[responseKey].Value
	contentType := generator.contentTypeNegotiator.NegotiateContentType(request, bestResponse)

	mediaType := bestResponse.Content[contentType]
	if mediaType == nil {
		return &Response{StatusCode: statusCode, Data: ""}, nil
	}

	data, err := generator.mediaGenerator.GenerateData(request.Context(), mediaType)
	if err != nil {
		return nil, fmt.Errorf("[coordinatingGenerator] failed to generate response data: %w", err)
	}

	response := &Response{
		StatusCode:  statusCode,
		ContentType: contentType,
		Data:        data,
	}

	return response, nil
}
