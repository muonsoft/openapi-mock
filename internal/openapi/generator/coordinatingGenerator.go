package generator

import (
	"fmt"
	"github.com/getkin/kin-openapi/openapi3filter"
	"net/http"
	"swagger-mock/internal/openapi/generator/content"
	"swagger-mock/internal/openapi/generator/negotiator"
)

type coordinatingGenerator struct {
	statusCodeNegotiator  negotiator.StatusCodeNegotiator
	contentTypeNegotiator negotiator.ContentTypeNegotiator
	contentGenerator      content.Generator
}

func (generator *coordinatingGenerator) GenerateResponse(request *http.Request, route *openapi3filter.Route) (*Response, error) {
	responseKey, statusCode, err := generator.statusCodeNegotiator.NegotiateStatusCode(request, route.Operation.Responses)
	if err != nil {
		return nil, fmt.Errorf("[coordinatingGenerator] failed to negotiate response: %w", err)
	}

	bestResponse := route.Operation.Responses[responseKey].Value
	contentType := generator.contentTypeNegotiator.NegotiateContentType(request, bestResponse)

	contentData, err := generator.contentGenerator.GenerateContent(request.Context(), bestResponse, contentType)
	if err != nil {
		return nil, fmt.Errorf("[coordinatingGenerator] failed to generate response data: %w", err)
	}

	response := &Response{
		StatusCode:  statusCode,
		ContentType: contentType,
		Data:        contentData,
	}

	return response, nil
}
