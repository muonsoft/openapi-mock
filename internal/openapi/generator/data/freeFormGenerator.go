package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/pkg/errors"
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
		if errors.Is(err, errAttemptsLimitExceeded) && i > minLength {
			return values, nil
		}
		if err != nil {
			return values, errors.WithMessage(err, "[freeFormGenerator] failed to generate free form object")
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
