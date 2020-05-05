package generator

import (
	"github.com/getkin/kin-openapi/openapi3"
)

type MediaGenerator interface {
	GenerateData(mediaType *openapi3.MediaType) (Data, error)
}

func New() MediaGenerator {
	return &coordinatingMediaGenerator{}
}
