package negotiator

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"net/http"
)

type MediaTypeNegotiator interface {
	NegotiateMediaType(request *http.Request, route *openapi3filter.Route) (string, error)
}

func NewMediaTypeNegotiator() MediaTypeNegotiator {
	return &mediaTypeNegotiator{}
}

type mediaTypeNegotiator struct{}

func (mediaTypeNegotiator) NegotiateMediaType(request *http.Request, route *openapi3filter.Route) (string, error) {
	return "application/json", nil
}
