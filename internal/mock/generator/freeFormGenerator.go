package generator

import (
	"context"
	"errors"
	"fmt"
	"github.com/getkin/kin-openapi/openapi3"
	"syreclabs.com/go/faker"
)

type freeFormGenerator struct {
	lengthGenerator arrayLengthGenerator
	keyGenerator    keyGenerator
}

func (generator *freeFormGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	keyGenerator := newUniqueKeyGenerator(generator.keyGenerator)

	length, minLength := generator.generateLength(schema)
	values := make(map[string]interface{})

	for i := uint64(1); i <= length; i++ {
		key, err := keyGenerator.GenerateKey()
		if err != nil {
			if errors.Is(err, errAttemptsLimitExceeded) {
				if i > minLength {
					return values, nil
				}
			}

			return values, fmt.Errorf("[freeFormGenerator] failed to generate free form object: %w", err)
		}

		values[key] = faker.Lorem().String()
	}

	return values, nil
}

func (generator *freeFormGenerator) generateLength(schema *openapi3.Schema) (uint64, uint64) {
	var maxProps uint64
	if schema.MaxProps != nil {
		maxProps = *schema.MaxProps
	}

	return generator.lengthGenerator.GenerateLength(schema.MinProps, maxProps)
}
