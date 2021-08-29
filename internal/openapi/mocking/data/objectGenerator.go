package data

import (
	"context"

	"github.com/getkin/kin-openapi/openapi3"
	"github.com/pkg/errors"
)

type objectGenerator struct {
	schemaGenerator Generator
}

func (generator *objectGenerator) SetSchemaGenerator(schemaGenerator Generator) {
	generator.schemaGenerator = schemaGenerator
}

func (generator *objectGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (interface{}, error) {
	var err error
	object := map[string]interface{}{}

	for propertyName, propertySchema := range schema.Properties {
		if propertySchema.Value.WriteOnly {
			continue
		}

		object[propertyName], err = generator.schemaGenerator.GenerateDataBySchema(ctx, propertySchema.Value)
		if err != nil {
			return nil, errors.WithMessagef(err, "[objectGenerator] failed to generate object property '%s'", propertyName)
		}
	}

	return object, nil
}
