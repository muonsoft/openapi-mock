package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/mock"
	"github.com/stretchr/testify/suite"
	"testing"
)

type CombinedSchemaGeneratorSuite struct {
	suite.Suite

	merger          *mockSchemaMerger
	schemaGenerator *mockSchemaGenerator

	generator *combinedSchemaGenerator
}

func (suite *CombinedSchemaGeneratorSuite) SetupTest() {
	suite.merger = &mockSchemaMerger{}
	suite.schemaGenerator = &mockSchemaGenerator{}

	suite.generator = &combinedSchemaGenerator{
		merger:          suite.merger,
		schemaGenerator: suite.schemaGenerator,
	}
}

func TestCombinedSchemaGeneratorSuite(t *testing.T) {
	suite.Run(t, new(CombinedSchemaGeneratorSuite))
}

func (suite *CombinedSchemaGeneratorSuite) TestGenerateDataBySchema_CombiningSchema_DataGeneratedForMergedSchema() {
	schema := openapi3.NewSchema()
	mergedSchema := openapi3.NewSchema()
	suite.merger.On("MergeSchemas", schema).Return(mergedSchema).Once()
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, mergedSchema).Return("data", nil).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.merger.AssertExpectations(suite.T())
	suite.schemaGenerator.AssertExpectations(suite.T())
	suite.NoError(err)
	suite.Equal("data", data)
}

func (suite *CombinedSchemaGeneratorSuite) TestGenerateDataBySchema_NotACombiningSchema_Error() {
	schema := openapi3.NewSchema()
	schema.Title = "schema"
	suite.merger.On("MergeSchemas", schema).Return(nil).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.merger.AssertExpectations(suite.T())
	suite.schemaGenerator.AssertExpectations(suite.T())
	suite.EqualError(err, "[combinedSchemaGenerator] schema 'schema' is not a combining schema")
	suite.Nil(data)
}
