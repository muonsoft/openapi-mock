package generator

import (
	"fmt"
	"strings"
	"syreclabs.com/go/faker"
)

type keyGenerator interface {
	GenerateKey() (string, error)
}

type camelCaseKeyGenerator struct {
	random randomGenerator
}

func (generator *camelCaseKeyGenerator) GenerateKey() (string, error) {
	wordsCount := generator.random.Intn(9) + 1
	words := faker.Lorem().Words(wordsCount)

	key := words[0]
	for i := 1; i < len(words); i++ {
		key += strings.Title(words[i])
	}

	return key, nil
}

type uniqueKeyGenerator struct {
	keyGenerator keyGenerator
	uniqueKeys   map[string]bool
}

func newUniqueKeyGenerator(generator keyGenerator) keyGenerator {
	return &uniqueKeyGenerator{
		keyGenerator: generator,
		uniqueKeys:   make(map[string]bool),
	}
}

func (generator *uniqueKeyGenerator) GenerateKey() (string, error) {
	var key string
	var err error
	var attempt int

	for attempt = 0; attempt < maxAttempts; attempt++ {
		key, err = generator.keyGenerator.GenerateKey()
		if err != nil {
			return "", fmt.Errorf("[uniqueKeyGenerator] failed to generate key: %w", err)
		}

		if !generator.uniqueKeys[key] {
			break
		}
	}

	if attempt >= maxAttempts {
		return "", fmt.Errorf("[uniqueKeyGenerator] failed to generate unique key: %w", errAttemptsLimitExceeded)
	}

	generator.uniqueKeys[key] = true

	return key, err
}
