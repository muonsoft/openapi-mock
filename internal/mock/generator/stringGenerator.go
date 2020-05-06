package generator

import (
	"context"
	"fmt"
	"github.com/getkin/kin-openapi/openapi3"
	"math/rand"
)

type stringGenerator struct {
	random rand.Rand
}

func (generator *stringGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	value := "value"

	if schema.Enum != nil {
		value = generator.generateRandomEnumValue(schema)
	}

	return value, nil
}

func (generator *stringGenerator) generateRandomEnumValue(schema *openapi3.Schema) string {
	return fmt.Sprint(schema.Enum[generator.random.Intn(len(schema.Enum))])
}
