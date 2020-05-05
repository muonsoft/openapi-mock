package generator

import "github.com/getkin/kin-openapi/openapi3"

type schemaGenerator interface {
	GenerateDataBySchema(schema *openapi3.Schema) (Data, error)
}
