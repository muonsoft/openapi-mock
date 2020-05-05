package generator

import "github.com/getkin/kin-openapi/openapi3filter"

type DataGenerator interface {
	GenerateData(route *openapi3filter.Route) (map[string]interface{}, error)
}

func New() DataGenerator {
	return &coordinatingGenerator{}
}
