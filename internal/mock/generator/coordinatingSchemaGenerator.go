package generator

import (
	"github.com/getkin/kin-openapi/openapi3"
)

type coordinatingSchemaGenerator struct {
	generatorsByType map[string]schemaGenerator
}

func (generator *coordinatingSchemaGenerator) GenerateDataBySchema(schema *openapi3.Schema) (Data, error) {
	specificGenerator := generator.generatorsByType[schema.Type]

	return specificGenerator.GenerateDataBySchema(schema)
}
