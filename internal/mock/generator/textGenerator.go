package generator

import (
	"context"
	"fmt"
	"github.com/getkin/kin-openapi/openapi3"
	"syreclabs.com/go/faker"
)

type textGenerator struct {
	random randomGenerator
}

const defaultMaxLength = 200

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

	text := ""

	if maxLength < 5 {
		text = faker.RandomString(int(maxLength))
	} else {
		text = generator.generateRangedText(int(schema.MinLength), int(maxLength))
	}

	return text, nil
}

func (generator *textGenerator) generateRangedText(minLength int, maxLength int) string {
	length := generator.random.Intn(maxLength-minLength) + minLength
	text := ""

	for {
		wordsCount := generator.random.Intn(9) + 3
		sentence := faker.Lorem().Sentence(wordsCount)

		var extendedText string
		if len(text) == 0 {
			extendedText = sentence
		} else {
			extendedText = text + " " + sentence
		}

		if len(extendedText) >= length {
			if len(extendedText) >= maxLength {
				break
			}

			text = extendedText
			break
		}

		text = extendedText
	}

	return text
}
