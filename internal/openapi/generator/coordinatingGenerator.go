package generator

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"net/http"
	"swagger-mock/internal/mock/generator"
	"swagger-mock/internal/openapi/generator/negotiator"
)

type coordinatingGenerator struct {
	contentTypeNegotiator negotiator.ContentTypeNegotiator
	statusCodeNegotiator  negotiator.StatusCodeNegotiator
	mediaGenerator        generator.MediaGenerator
}

func (generator *coordinatingGenerator) GenerateResponse(request *http.Request, route *openapi3filter.Route) (*Response, error) {
	responseKey, statusCode, _ := generator.statusCodeNegotiator.NegotiateStatusCode(request, route.Operation.Responses)
	contentType, _ := generator.contentTypeNegotiator.NegotiateContentType(request, route)

	mediaType := route.Operation.Responses[responseKey].Value.Content[contentType]

	data, _ := generator.mediaGenerator.GenerateData(request.Context(), mediaType)

	response := &Response{
		StatusCode: statusCode,
		MediaType:  contentType,
		Data:       data,
	}

	return response, nil
}
