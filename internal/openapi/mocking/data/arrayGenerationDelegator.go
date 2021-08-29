package data

import (
	"context"

	"github.com/getkin/kin-openapi/openapi3"
)

type arrayGenerationDelegator struct {
	uniqueGenerator  Generator
	regularGenerator Generator
}

func newArrayGenerator(lengthGenerator arrayLengthGenerator) Generator {
	return &arrayGenerationDelegator{
		uniqueGenerator:  &uniqueArrayGenerator{lengthGenerator: lengthGenerator},
		regularGenerator: &regularArrayGenerator{lengthGenerator: lengthGenerator},
	}
}

func (delegator *arrayGenerationDelegator) SetSchemaGenerator(schemaGenerator Generator) {
	delegator.uniqueGenerator.(recursiveGenerator).SetSchemaGenerator(schemaGenerator)
	delegator.regularGenerator.(recursiveGenerator).SetSchemaGenerator(schemaGenerator)
}

func (delegator *arrayGenerationDelegator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (interface{}, error) {
	if schema.UniqueItems {
		return delegator.uniqueGenerator.GenerateDataBySchema(ctx, schema)
	}

	return delegator.regularGenerator.GenerateDataBySchema(ctx, schema)
}
