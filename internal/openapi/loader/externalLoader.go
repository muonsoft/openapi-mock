package loader

import (
	"net/url"

	"github.com/getkin/kin-openapi/openapi3"
)

type externalLoader interface {
	LoadFromURI(location *url.URL) (*openapi3.T, error)
	LoadFromFile(path string) (*openapi3.T, error)
}
