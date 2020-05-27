package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/pkg/errors"
	"math"
)

type integerGenerator struct {
	random         randomGenerator
	defaultMinimum int64
	defaultMaximum int64
}

func (generator *integerGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	minimum, maximum := generator.getMinMax(schema)

	if maximum < minimum {
		return 0, errors.WithStack(&ErrGenerationFailed{
			GeneratorID: "integerGenerator",
			Message:     "maximum cannot be less than minimum",
		})
	}
	if maximum == minimum {
		return minimum, nil
	}

	value := generator.generateValueInRange(minimum, maximum)

	if schema.MultipleOf != nil {
		value -= value % int64(*schema.MultipleOf)
	}

	return value, nil
}

func (generator *integerGenerator) getMinMax(schema *openapi3.Schema) (int64, int64) {
	minimum := generator.defaultMinimum
	maximum := generator.defaultMaximum
	if schema.Format == "int32" && generator.defaultMaximum > math.MaxInt32 {
		maximum = math.MaxInt32
	}

	if schema.Min != nil {
		minimum = int64(*schema.Min)
	}
	if schema.Max != nil {
		maximum = int64(*schema.Max)
	}

	if schema.ExclusiveMin {
		minimum++
	}
	if schema.ExclusiveMax {
		maximum--
	}

	return minimum, maximum
}

func (generator *integerGenerator) generateValueInRange(minimum int64, maximum int64) int64 {
	delta := maximum - minimum
	if delta < math.MaxInt64 {
		delta++
	}

	return generator.random.Int63n(delta) + minimum
}
