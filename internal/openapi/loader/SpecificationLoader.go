package loader

import "github.com/getkin/kin-openapi/openapi3"

type SpecificationLoader interface {
	LoadFromURI(uri string) (*openapi3.T, error)
}

func New() SpecificationLoader {
	return &processingLoader{
		&autoLoader{
			loader: openapi3.NewLoader(),
		},
	}
}
