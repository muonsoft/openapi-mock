package content_test

import (
	"context"
	"testing"

	"github.com/getkin/kin-openapi/openapi3"
	"github.com/muonsoft/openapi-mock/internal/enum"
	"github.com/muonsoft/openapi-mock/internal/openapi/mocking/content"
	"github.com/muonsoft/openapi-mock/pkg/logcontext"
	"github.com/stretchr/testify/assert"
)

func (suite *GeneratorSuite) TestGenerateContent_WhenPlainTextMediaType_ExpectDataGeneratedByString() {
	tests := []struct {
		name      string
		mediaType *openapi3.MediaType
	}{
		{
			name: "object schema",
			mediaType: &openapi3.MediaType{
				Schema: &openapi3.SchemaRef{
					Value: &openapi3.Schema{Type: "object"},
				},
			},
		},
		{
			name:      "no schema",
			mediaType: &openapi3.MediaType{},
		},
	}
	for _, test := range tests {
		suite.T().Run(test.name, func(t *testing.T) {
			ctx := logcontext.WithLogger(context.Background(), suite.logger)
			expectedSchema := &openapi3.Schema{Type: "string"}
			suite.dataGenerator.On("GenerateDataBySchema", ctx, expectedSchema).Return("generated", nil)
			response := givenContentResponse("text/plain", test.mediaType)
			generator := content.NewGenerator(enum.IfPresent, suite.dataGenerator)

			data, err := generator.GenerateContent(ctx, response, "text/plain")

			assert.NoError(t, err)
			assert.Equal(t, "generated", data)
			suite.assertWarningWasLogged("only string schema is supported for 'text/plain' content type")
		})
	}
}
