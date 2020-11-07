package loader

import (
	"net/url"

	"github.com/getkin/kin-openapi/openapi3"
)

type externalLoader interface {
	LoadSwaggerFromURI(location *url.URL) (*openapi3.Swagger, error)
	LoadSwaggerFromFile(path string) (*openapi3.Swagger, error)
}
