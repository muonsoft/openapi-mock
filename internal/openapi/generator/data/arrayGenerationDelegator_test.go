package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/mock"
	"github.com/stretchr/testify/suite"
	"testing"
)

type ArrayGenerationDelegatorSuite struct {
	suite.Suite

	uniqueGenerator  *mockSchemaGenerator
	regularGenerator *mockSchemaGenerator

	delegator *arrayGenerationDelegator
}

func (suite *ArrayGenerationDelegatorSuite) SetupTest() {
	suite.uniqueGenerator = &mockSchemaGenerator{}
	suite.regularGenerator = &mockSchemaGenerator{}

	suite.delegator = &arrayGenerationDelegator{
		uniqueGenerator:  suite.uniqueGenerator,
		regularGenerator: suite.regularGenerator,
	}
}

func TestArrayGenerationDelegatorSuite(t *testing.T) {
	suite.Run(t, new(ArrayGenerationDelegatorSuite))
}

func (suite *ArrayGenerationDelegatorSuite) TestGenerateDataBySchema_RegularArray_DataGeneratedByRegularGenerator() {
	schema := openapi3.NewSchema()
	suite.regularGenerator.On("GenerateDataBySchema", mock.Anything, schema).Return("data", nil).Once()

	data, err := suite.delegator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Equal("data", data)
}

func (suite *ArrayGenerationDelegatorSuite) TestGenerateDataBySchema_UniqueArray_DataGeneratedByUniqueGenerator() {
	schema := openapi3.NewSchema()
	schema.UniqueItems = true
	suite.uniqueGenerator.On("GenerateDataBySchema", mock.Anything, schema).Return("data", nil).Once()

	data, err := suite.delegator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Equal("data", data)
}

func (suite *ArrayGenerationDelegatorSuite) assertExpectations() {
	suite.regularGenerator.AssertExpectations(suite.T())
	suite.uniqueGenerator.AssertExpectations(suite.T())
}
