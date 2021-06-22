package loader

import (
	"net/url"

	"github.com/getkin/kin-openapi/openapi3"
)

type autoLoader struct {
	loader externalLoader
}

func (loader *autoLoader) LoadFromURI(uri string) (*openapi3.T, error) {
	specificationURL, err := url.Parse(uri)
	if err != nil || specificationURL.Scheme == "" {
		return loader.loader.LoadFromFile(uri)
	}

	return loader.loader.LoadFromURI(specificationURL)
}
