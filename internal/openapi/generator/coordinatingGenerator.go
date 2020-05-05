package generator

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"net/http"
	"swagger-mock/internal/mock/generator"
	"swagger-mock/internal/openapi/generator/negotiator"
)

type coordinatingGenerator struct {
	mediaTypeNegotiator  negotiator.MediaTypeNegotiator
	statusCodeNegotiator negotiator.StatusCodeNegotiator
	dataGenerator        generator.DataGenerator
}

func (generator *coordinatingGenerator) GenerateResponse(request *http.Request, route *openapi3filter.Route) (*Response, error) {
	statusCode, _ := generator.statusCodeNegotiator.NegotiateStatusCode(request, route)
	mediaType, _ := generator.mediaTypeNegotiator.NegotiateMediaType(request, route)
	data, _ := generator.dataGenerator.GenerateData(route)

	response := &Response{
		StatusCode: statusCode,
		MediaType:  mediaType,
		Data:       data,
	}

	return response, nil
}
