package data

import "syreclabs.com/go/faker"

type rangedTextGenerator struct {
	random randomGenerator
}

func (generator *rangedTextGenerator) generateRangedText(minLength int, maxLength int) string {
	if maxLength < 5 {
		return faker.RandomString(maxLength)
	}

	length := minLength
	if maxLength-minLength > 0 {
		length = generator.random.Intn(maxLength-minLength) + minLength
	}

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

	if len(text) < minLength {
		text = faker.RandomString(minLength)
	}

	return text
}
