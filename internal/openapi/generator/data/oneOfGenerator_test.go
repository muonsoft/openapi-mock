package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/mock"
	"github.com/stretchr/testify/suite"
	"testing"
)

type OneOfGeneratorSuite struct {
	suite.Suite

	random          *mockRandomGenerator
	schemaGenerator *mockSchemaGenerator

	generator *oneOfGenerator
}

func (suite *OneOfGeneratorSuite) SetupTest() {
	suite.random = &mockRandomGenerator{}
	suite.schemaGenerator = &mockSchemaGenerator{}

	suite.generator = &oneOfGenerator{
		random:          suite.random,
		schemaGenerator: suite.schemaGenerator,
	}
}

func TestOneOfGeneratorSuite(t *testing.T) {
	suite.Run(t, new(OneOfGeneratorSuite))
}

func (suite *OneOfGeneratorSuite) TestGenerateDataBySchema_EmptySchemas_EmptyObject() {
	schema := openapi3.NewSchema()
	schema.OneOf = []*openapi3.SchemaRef{}

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.NoError(err)
	suite.Len(data, 0)
}

func (suite *OneOfGeneratorSuite) TestGenerateDataBySchema_TwoSchemasAndRandomIndexIsFirst_DataGeneratedForFirstSchema() {
	firstSchema := openapi3.NewSchema()
	secondSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.OneOf = []*openapi3.SchemaRef{
		openapi3.NewSchemaRef("", firstSchema),
		openapi3.NewSchemaRef("", secondSchema),
	}
	suite.random.On("Intn", 2).Return(0).Once()
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, firstSchema).Return("data", nil).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.NoError(err)
	suite.Equal("data", data)
}
