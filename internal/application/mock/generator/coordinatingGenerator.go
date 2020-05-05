package generator

import (
	"github.com/getkin/kin-openapi/openapi3filter"
)

type coordinatingGenerator struct{}

func (generator *coordinatingGenerator) GenerateData(route *openapi3filter.Route) (map[string]interface{}, error) {
	return map[string]interface{}{"ok": true}, nil
}
