package generator

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"github.com/muonsoft/openapi-mock/internal/openapi/generator/content"
	"github.com/muonsoft/openapi-mock/internal/openapi/generator/data"
	"github.com/muonsoft/openapi-mock/internal/openapi/generator/negotiator"
	"net/http"
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
