package data

import (
	"context"
	"errors"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/mock"
	"github.com/stretchr/testify/suite"
	"testing"
)

type RegularArrayGeneratorSuite struct {
	suite.Suite

	lengthGenerator *mockArrayLengthGenerator
	schemaGenerator *mockSchemaGenerator

	generator *regularArrayGenerator
}

func (suite *RegularArrayGeneratorSuite) SetupTest() {
	suite.lengthGenerator = &mockArrayLengthGenerator{}
	suite.schemaGenerator = &mockSchemaGenerator{}

	suite.generator = &regularArrayGenerator{
		lengthGenerator: suite.lengthGenerator,
		schemaGenerator: suite.schemaGenerator,
	}
}

func TestRegularArrayGeneratorSuite(t *testing.T) {
	suite.Run(t, new(RegularArrayGeneratorSuite))
}

func (suite *RegularArrayGeneratorSuite) TestGenerateDataBySchema_RandomLength_ArrayOfLengthGenerated() {
	itemsSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.Items = openapi3.NewSchemaRef("", itemsSchema)
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(2), uint64(0)).Once()
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, itemsSchema).Return("value", nil).Twice()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Len(data, 2)
	suite.Equal("value", data.([]interface{})[0])
}

func (suite *RegularArrayGeneratorSuite) TestGenerateDataBySchema_GivenItemsLength_ArrayOfLengthGenerated() {
	itemsSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.Items = openapi3.NewSchemaRef("", itemsSchema)
	schema.MinItems = 1
	schema.MaxItems = &schema.MinItems
	suite.lengthGenerator.On("GenerateLength", uint64(1), uint64(1)).Return(uint64(1), uint64(0)).Once()
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, itemsSchema).Return("value", nil).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Len(data, 1)
	suite.Equal("value", data.([]interface{})[0])
}

func (suite *RegularArrayGeneratorSuite) TestGenerateDataBySchema_SecondValuesLeadsToError_ReducedArrayAndError() {
	itemsSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.Items = openapi3.NewSchemaRef("", itemsSchema)
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(2), uint64(0)).Once()
	suite.schemaGenerator.
		On("GenerateDataBySchema", mock.Anything, itemsSchema).Return("value", nil).Once().
		On("GenerateDataBySchema", mock.Anything, itemsSchema).Return(nil, errors.New("error")).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.EqualError(err, "[regularArrayGenerator] error occurred while generating array value: error")
	suite.Len(data, 1)
	suite.Equal("value", data.([]interface{})[0])
}

func (suite *RegularArrayGeneratorSuite) assertExpectations() {
	suite.lengthGenerator.AssertExpectations(suite.T())
	suite.schemaGenerator.AssertExpectations(suite.T())
}
