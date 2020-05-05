package loader

import "github.com/getkin/kin-openapi/openapi3"

type SpecificationLoader interface {
	LoadFromURI(uri string) (*openapi3.Swagger, error)
}
