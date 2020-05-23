package generator

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"net/http"
	"swagger-mock/internal/openapi/generator/content"
	"swagger-mock/internal/openapi/generator/data"
	"swagger-mock/internal/openapi/generator/negotiator"
)

type ResponseGenerator interface {
	GenerateResponse(request *http.Request, route *openapi3filter.Route) (*Response, error)
}

func New(dataGenerator data.MediaGenerator) ResponseGenerator {
	return &coordinatingGenerator{
		contentTypeNegotiator: negotiator.NewContentTypeNegotiator(),
		statusCodeNegotiator:  negotiator.NewStatusCodeNegotiator(),
		contentGenerator:      content.NewGenerator(dataGenerator),
	}
}
