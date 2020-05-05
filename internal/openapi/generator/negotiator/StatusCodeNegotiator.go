package negotiator

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"net/http"
)

type StatusCodeNegotiator interface {
	NegotiateStatusCode(request *http.Request, route *openapi3filter.Route) (int, error)
}

func NewStatusCodeNegotiator() StatusCodeNegotiator {
	return &statusCodeNegotiator{}
}

type statusCodeNegotiator struct{}

func (statusCodeNegotiator) NegotiateStatusCode(request *http.Request, route *openapi3filter.Route) (int, error) {
	return http.StatusOK, nil
}
