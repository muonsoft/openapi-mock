package generator

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"net/http"
	"swagger-mock/internal/application/mock/generator"
	"swagger-mock/internal/infrastructure/openapi/generator/negotiator"
)

type ResponseGenerator interface {
	GenerateResponse(request *http.Request, route *openapi3filter.Route) (*Response, error)
}

func New(dataGenerator generator.DataGenerator) ResponseGenerator {
	return &coordinatingGenerator{
		mediaTypeNegotiator:  negotiator.NewMediaTypeNegotiator(),
		statusCodeNegotiator: negotiator.NewStatusCodeNegotiator(),
		dataGenerator:        dataGenerator,
	}
}
