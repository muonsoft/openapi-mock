package generator

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
)

type objectGenerator struct {
	schemaGenerator schemaGenerator
}

func (generator *objectGenerator) SetSchemaGenerator(schemaGenerator schemaGenerator) {
	generator.schemaGenerator = schemaGenerator
}

func (generator *objectGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	var err error
	object := map[string]interface{}{}

	for propertyName, propertySchema := range schema.Properties {
		object[propertyName], err = generator.schemaGenerator.GenerateDataBySchema(ctx, propertySchema.Value)
		if err != nil {
			return nil, err
		}
	}

	return object, nil
}
