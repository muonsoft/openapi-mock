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

	loader.processServers(&specification.Servers)

	for i := range specification.Paths {
		loader.processServers(&specification.Paths[i].Servers)
		loader.processOperation(specification.Paths[i].Get)
		loader.processOperation(specification.Paths[i].Connect)
		loader.processOperation(specification.Paths[i].Delete)
		loader.processOperation(specification.Paths[i].Get)
		loader.processOperation(specification.Paths[i].Head)
		loader.processOperation(specification.Paths[i].Options)
		loader.processOperation(specification.Paths[i].Patch)
		loader.processOperation(specification.Paths[i].Post)
		loader.processOperation(specification.Paths[i].Put)
		loader.processOperation(specification.Paths[i].Trace)
	}

	return specification, nil
}

func (loader *processingLoader) processServers(servers *openapi3.Servers) {
	if servers == nil {
		return
	}

	for i := range *servers {
		serverURL, err := url.Parse((*servers)[i].URL)
		if err != nil {
			continue
		}

		// server urls need to be cleaned for proper routing
		(*servers)[i].URL = serverURL.Path
	}
}

func (loader *processingLoader) processOperation(operation *openapi3.Operation) {
	if operation != nil {
		loader.processServers(operation.Servers)
	}
}
