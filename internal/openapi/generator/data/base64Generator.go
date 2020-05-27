package data

import (
	"bytes"
	"encoding/base64"
	"syreclabs.com/go/faker"
)

type base64Generator struct {
	generator *rangedTextGenerator
}

func (generator *base64Generator) GenerateBase64Text(minLength int, maxLength int) string {
	if maxLength == 1 {
		return "="
	}

	if maxLength <= 0 {
		maxLength = defaultMaxLength
	}
	rawMinLength := minLength*3/4 - 1
	rawMaxLength := maxLength*3/4 - 1

	text := generator.generator.generateRangedText(rawMinLength, rawMaxLength)
	encoded := &bytes.Buffer{}

	encoder := base64.NewEncoder(base64.StdEncoding, encoded)
	_, _ = encoder.Write([]byte(text))
	_ = encoder.Close()

	encodedText := encoded.String()

	if len(encodedText) > maxLength || len(encodedText) < minLength {
		encodedText = faker.RandomString(maxLength-1) + "="
	}

	return encodedText
}
