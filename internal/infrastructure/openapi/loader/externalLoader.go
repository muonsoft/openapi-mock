package loader

import (
	"github.com/getkin/kin-openapi/openapi3"
	"net/url"
)

type externalLoader interface {
	LoadSwaggerFromURI(location *url.URL) (*openapi3.Swagger, error)
	LoadSwaggerFromFile(path string) (*openapi3.Swagger, error)
}
