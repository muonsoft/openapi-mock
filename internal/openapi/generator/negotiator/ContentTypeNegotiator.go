package negotiator

import (
	"github.com/getkin/kin-openapi/openapi3filter"
	"net/http"
)

type ContentTypeNegotiator interface {
	NegotiateContentType(request *http.Request, route *openapi3filter.Route) (string, error)
}

func NewContentTypeNegotiator() ContentTypeNegotiator {
	return &contentTypeNegotiator{}
}

type contentTypeNegotiator struct{}

func (contentTypeNegotiator) NegotiateContentType(request *http.Request, route *openapi3filter.Route) (string, error) {
	return "application/json", nil
}
