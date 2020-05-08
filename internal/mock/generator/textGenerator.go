package generator

import (
	"context"
	"fmt"
	"github.com/getkin/kin-openapi/openapi3"
)

type textGenerator struct {
	generator *rangedTextGenerator
}

func (generator *textGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	var maxLength uint64
	if schema.MaxLength != nil {
		maxLength = *schema.MaxLength
	} else {
		if schema.MinLength < defaultMaxLength {
			maxLength = defaultMaxLength
		} else {
			maxLength = schema.MinLength + defaultMaxLength
		}
	}

	if maxLength < schema.MinLength {
		return "", fmt.Errorf("[textGenerator] max length cannot be less than min length")
	}

	return generator.generator.generateRangedText(int(schema.MinLength), int(maxLength)), nil
}
