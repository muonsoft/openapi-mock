package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
)

type objectGenerationDelegator struct {
	freeFormGenerator schemaGenerator
	hashMapGenerator  schemaGenerator
	objectGenerator   schemaGenerator
}

func newObjectGenerator(lengthGenerator arrayLengthGenerator, keyGenerator keyGenerator) schemaGenerator {
	return &objectGenerationDelegator{
		freeFormGenerator: &freeFormGenerator{
			lengthGenerator: lengthGenerator,
			keyGenerator:    keyGenerator,
		},
		hashMapGenerator: &hashMapGenerator{
			lengthGenerator: lengthGenerator,
			keyGenerator:    keyGenerator,
		},
		objectGenerator: &objectGenerator{},
	}
}

func (delegator *objectGenerationDelegator) SetSchemaGenerator(schemaGenerator schemaGenerator) {
	delegator.hashMapGenerator.(recursiveGenerator).SetSchemaGenerator(schemaGenerator)
	delegator.objectGenerator.(recursiveGenerator).SetSchemaGenerator(schemaGenerator)
}

func (delegator *objectGenerationDelegator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	if delegator.isHashMap(schema) {
		return delegator.hashMapGenerator.GenerateDataBySchema(ctx, schema)
	}
	if delegator.isFreeForm(schema) {
		return delegator.freeFormGenerator.GenerateDataBySchema(ctx, schema)
	}

	return delegator.objectGenerator.GenerateDataBySchema(ctx, schema)
}

func (delegator *objectGenerationDelegator) isHashMap(schema *openapi3.Schema) bool {
	return schema.AdditionalProperties != nil && schema.AdditionalProperties.Value != nil && schema.AdditionalProperties.Value.Type != ""
}

func (delegator *objectGenerationDelegator) isFreeForm(schema *openapi3.Schema) bool {
	return schema.AdditionalProperties != nil || schema.AdditionalPropertiesAllowed != nil && *schema.AdditionalPropertiesAllowed
}
