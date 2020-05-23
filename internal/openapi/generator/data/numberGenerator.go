package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/pkg/errors"
	"math"
)

type numberGenerator struct {
	random         randomGenerator
	defaultMinimum float64
	defaultMaximum float64
}

func (generator *numberGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	minimum, maximum := generator.getMinMax(schema)

	if maximum < minimum {
		return 0, errors.WithStack(&ErrGenerationFailed{
			GeneratorID: "numberGenerator",
			Message:     "maximum cannot be less than minimum",
		})
	}
	if maximum == minimum {
		return minimum, nil
	}

	value := generator.generateUniformRandomValue(schema)
	value = value*(maximum-minimum) + minimum

	if schema.MultipleOf != nil {
		value = math.Floor(value / *schema.MultipleOf) * *schema.MultipleOf
	}

	return value, nil
}

func (generator *numberGenerator) getMinMax(schema *openapi3.Schema) (float64, float64) {
	minimum := generator.defaultMinimum
	maximum := generator.defaultMaximum

	if schema.Min != nil {
		minimum = *schema.Min
	}
	if schema.Max != nil {
		maximum = *schema.Max
	}

	return minimum, maximum
}

func (generator *numberGenerator) generateUniformRandomValue(schema *openapi3.Schema) float64 {
	minimum := 0
	maximum := math.MaxInt64
	if schema.ExclusiveMin {
		minimum++
	}
	if schema.ExclusiveMax {
		maximum--
	}
	delta := maximum - minimum

	value1 := float64(generator.random.Intn(delta)+minimum) / float64(math.MaxInt64)
	value2 := float64(generator.random.Intn(delta)+minimum) / float64(math.MaxInt64)

	return value1 * value2
}
