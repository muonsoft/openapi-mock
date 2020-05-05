package loader

import (
	"github.com/getkin/kin-openapi/openapi3"
	"net/url"
)

type processingLoader struct {
	loader SpecificationLoader
}

func (loader *processingLoader) LoadFromURI(uri string) (*openapi3.Swagger, error) {
	specification, err := loader.loader.LoadFromURI(uri)
	if err != nil {
		return nil, err
	}

	for i := range specification.Servers {
		serverUrl, err := url.Parse(specification.Servers[i].URL)
		if err != nil {
			return nil, err
		}

		// server urls need to be cleaned for proper routing
		specification.Servers[i].URL = serverUrl.Path
	}

	return specification, nil
}
