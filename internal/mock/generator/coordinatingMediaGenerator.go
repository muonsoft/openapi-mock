package generator

import "github.com/getkin/kin-openapi/openapi3"

type coordinatingMediaGenerator struct{}

func (generator *coordinatingMediaGenerator) GenerateData(mediaType *openapi3.MediaType) (Data, error) {
	return map[string]interface{}{"ok": true}, nil
}
