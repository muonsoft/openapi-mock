package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
)

type arrayGenerationDelegator struct {
	uniqueGenerator  schemaGenerator
	regularGenerator schemaGenerator
}

func newArrayGenerator(lengthGenerator arrayLengthGenerator) schemaGenerator {
	return &arrayGenerationDelegator{
		uniqueGenerator:  &uniqueArrayGenerator{lengthGenerator: lengthGenerator},
		regularGenerator: &regularArrayGenerator{lengthGenerator: lengthGenerator},
	}
}

func (delegator *arrayGenerationDelegator) SetSchemaGenerator(schemaGenerator schemaGenerator) {
	delegator.uniqueGenerator.(recursiveGenerator).SetSchemaGenerator(schemaGenerator)
	delegator.regularGenerator.(recursiveGenerator).SetSchemaGenerator(schemaGenerator)
}

func (delegator *arrayGenerationDelegator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	if schema.UniqueItems {
		return delegator.uniqueGenerator.GenerateDataBySchema(ctx, schema)
	}

	return delegator.regularGenerator.GenerateDataBySchema(ctx, schema)
}
