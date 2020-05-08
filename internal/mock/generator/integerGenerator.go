package generator

import (
	"context"
	"fmt"
	"github.com/getkin/kin-openapi/openapi3"
	"math"
)

type integerGenerator struct {
	random randomGenerator
}

func (generator *integerGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	minimum, maximum := generator.getMinMax(schema)

	if maximum < minimum {
		return 0, fmt.Errorf("[integerGenerator] maximum cannot be less than minimum")
	}
	if maximum == minimum {
		return minimum, nil
	}

	value := generator.generateValueInRange(minimum, maximum)

	if schema.MultipleOf != nil {
		value -= value % int(*schema.MultipleOf)
	}

	return value, nil
}

func (generator *integerGenerator) getMinMax(schema *openapi3.Schema) (int, int) {
	minimum := 0
	maximum := math.MaxInt64
	if schema.Format == "int32" {
		maximum = math.MaxInt32
	}

	if schema.Min != nil {
		minimum = int(*schema.Min)
	}
	if schema.Max != nil {
		maximum = int(*schema.Max)
	}

	if schema.ExclusiveMin {
		minimum++
	}
	if schema.ExclusiveMax {
		maximum--
	}

	return minimum, maximum
}

func (generator *integerGenerator) generateValueInRange(minimum int, maximum int) int {
	delta := maximum - minimum
	if delta < math.MaxInt64 {
		delta++
	}

	return generator.random.Intn(delta) + minimum
}
