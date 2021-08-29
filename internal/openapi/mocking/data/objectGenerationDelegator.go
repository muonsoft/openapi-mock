package data

import (
	"context"

	"github.com/getkin/kin-openapi/openapi3"
)

type objectGenerationDelegator struct {
	freeFormGenerator Generator
	hashMapGenerator  Generator
	objectGenerator   Generator
}

func newObjectGenerator(lengthGenerator arrayLengthGenerator, keyGenerator keyGenerator) Generator {
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

func (delegator *objectGenerationDelegator) SetSchemaGenerator(schemaGenerator Generator) {
	delegator.hashMapGenerator.(recursiveGenerator).SetSchemaGenerator(schemaGenerator)
	delegator.objectGenerator.(recursiveGenerator).SetSchemaGenerator(schemaGenerator)
}

func (delegator *objectGenerationDelegator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (interface{}, error) {
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
