package data

import (
	"context"
	"fmt"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/pkg/errors"
	"syreclabs.com/go/faker"
)

type stringGenerator struct {
	random           randomGenerator
	textGenerator    schemaGenerator
	formatGenerators map[string]stringGeneratorFunction
}

func newStringGenerator(random randomGenerator) schemaGenerator {
	generator := &rangedTextGenerator{
		random: random,
	}

	return &stringGenerator{
		random:           random,
		textGenerator:    &textGenerator{generator: generator},
		formatGenerators: defaultFormattedStringGenerators(generator),
	}
}

func (generator *stringGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	var value Data
	var err error

	if len(schema.Enum) > 0 {
		value = generator.getRandomEnumValue(schema.Enum)
	} else if schema.Pattern != "" {
		value, err = generator.generateValueByPattern(schema.Pattern)
	} else if formatGenerator, isSupported := generator.formatGenerators[schema.Format]; isSupported {
		maxLength := 0
		if schema.MaxLength != nil {
			maxLength = int(*schema.MaxLength)
		}
		value = formatGenerator(int(schema.MinLength), maxLength)
	} else {
		value, err = generator.textGenerator.GenerateDataBySchema(ctx, schema)
	}

	return value, err
}

func (generator *stringGenerator) getRandomEnumValue(enum []interface{}) string {
	return fmt.Sprint(enum[generator.random.Intn(len(enum))])
}

func (generator *stringGenerator) generateValueByPattern(pattern string) (string, error) {
	value, err := faker.Regexify(pattern)
	if err != nil {
		return "", errors.WithStack(&ErrGenerationFailed{
			GeneratorID: "stringGenerator",
			Message:     fmt.Sprintf("cannot generate string value by pattern '%s'", pattern),
			Previous:    err,
		})
	}

	return value, nil
}
