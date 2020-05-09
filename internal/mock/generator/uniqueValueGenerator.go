package generator

import (
	"context"
	"crypto/sha256"
	"encoding/json"
	"errors"
	"github.com/getkin/kin-openapi/openapi3"
)

var errAttemptsLimitExceeded = errors.New("attempts limit exceeded")

type uniqueValueGenerator struct {
	valueGenerator schemaGenerator

	uniqueValues map[[32]byte]bool
}

const maxAttempts = 1000

func newUniqueValueGenerator(valueGenerator schemaGenerator) schemaGenerator {
	return &uniqueValueGenerator{
		valueGenerator: valueGenerator,
		uniqueValues:   make(map[[32]byte]bool),
	}
}

func (generator *uniqueValueGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	var data interface{}
	var err error
	var attempt int

	for attempt = 0; attempt < maxAttempts; attempt++ {
		data, err = generator.valueGenerator.GenerateDataBySchema(ctx, schema)
		if err != nil {
			return nil, err
		}
		if generator.isUnique(data) {
			break
		}
	}

	if attempt >= maxAttempts {
		return nil, errAttemptsLimitExceeded
	}

	generator.rememberValue(data)

	return data, err
}

func (generator *uniqueValueGenerator) isUnique(data interface{}) bool {
	v, _ := json.Marshal(data)
	hash := sha256.Sum256(v)

	return !generator.uniqueValues[hash]
}

func (generator *uniqueValueGenerator) rememberValue(data interface{}) {
	v, _ := json.Marshal(data)
	hash := sha256.Sum256(v)

	generator.uniqueValues[hash] = true
}
