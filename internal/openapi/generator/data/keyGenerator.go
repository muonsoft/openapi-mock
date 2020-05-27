package data

import (
	"github.com/pkg/errors"
	"strings"
	"syreclabs.com/go/faker"
)

type keyGenerator interface {
	AddKey(key string)
	GenerateKey() (string, error)
}

type camelCaseKeyGenerator struct {
	random randomGenerator
}

func (generator *camelCaseKeyGenerator) AddKey(key string) {
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

func (generator *uniqueKeyGenerator) AddKey(key string) {
	generator.uniqueKeys[key] = true
}

func (generator *uniqueKeyGenerator) GenerateKey() (string, error) {
	var key string
	var err error
	var attempt int

	for attempt = 0; attempt < maxAttempts; attempt++ {
		key, err = generator.keyGenerator.GenerateKey()
		if err != nil {
			return "", errors.WithMessage(err, "[uniqueKeyGenerator] failed to generate key")
		}

		if !generator.uniqueKeys[key] {
			break
		}
	}

	if attempt >= maxAttempts {
		return "", errors.Wrap(errAttemptsLimitExceeded, "[uniqueKeyGenerator] failed to generate unique key")
	}

	generator.uniqueKeys[key] = true

	return key, err
}
