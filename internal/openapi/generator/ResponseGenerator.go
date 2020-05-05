package generator

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"net/http"
	"swagger-mock/internal/mock/generator"
	"swagger-mock/internal/openapi/generator/negotiator"
)

type ResponseGenerator interface {
	GenerateResponse(request *http.Request, route *openapi3filter.Route) (*Response, error)
}

func New(dataGenerator generator.MediaGenerator) ResponseGenerator {
	return &coordinatingGenerator{
		contentTypeNegotiator: negotiator.NewContentTypeNegotiator(),
		statusCodeNegotiator:  negotiator.NewStatusCodeNegotiator(),
		mediaGenerator:        dataGenerator,
	}
}
