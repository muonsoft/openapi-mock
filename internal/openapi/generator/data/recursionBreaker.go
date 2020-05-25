package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
)

const (
	recursionKey      int = 0
	maxRecursionLevel     = 100
)

type recursionBreaker struct {
	schemaGenerator schemaGenerator
}

func (breaker *recursionBreaker) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	level, _ := ctx.Value(recursionKey).(int)
	if level >= maxRecursionLevel {
		return nil, &ErrGenerationFailed{GeneratorID: "recursionBreaker", Message: "max recursion level reached"}
	}

	level++
	nextContext := context.WithValue(ctx, recursionKey, level)

	return breaker.schemaGenerator.GenerateDataBySchema(nextContext, schema)
}
