package generator

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"github.com/muonsoft/openapi-mock/internal/openapi/generator/content"
	"github.com/muonsoft/openapi-mock/internal/openapi/generator/negotiator"
	"github.com/pkg/errors"
	"net/http"
)

type coordinatingGenerator struct {
	statusCodeNegotiator  negotiator.StatusCodeNegotiator
	contentTypeNegotiator negotiator.ContentTypeNegotiator
	contentGenerator      content.Generator
}

func (generator *coordinatingGenerator) GenerateResponse(request *http.Request, route *openapi3filter.Route) (*Response, error) {
	responseKey, statusCode, err := generator.statusCodeNegotiator.NegotiateStatusCode(request, route.Operation.Responses)
	if err != nil {
		return nil, errors.WithMessage(err, "[coordinatingGenerator] failed to negotiate response")
	}

	bestResponse := route.Operation.Responses[responseKey].Value
	contentType := generator.contentTypeNegotiator.NegotiateContentType(request, bestResponse)

	contentData, err := generator.contentGenerator.GenerateContent(request.Context(), bestResponse, contentType)
	if err != nil {
		return nil, errors.WithMessage(err, "[coordinatingGenerator] failed to generate response data")
	}

	response := &Response{
		StatusCode:  statusCode,
		ContentType: contentType,
		Data:        contentData,
	}

	return response, nil
}
