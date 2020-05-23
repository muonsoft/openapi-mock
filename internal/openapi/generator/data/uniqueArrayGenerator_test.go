package data

import (
	"context"
	"errors"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/mock"
	"github.com/stretchr/testify/suite"
	"testing"
)

type UniqueArrayGeneratorSuite struct {
	suite.Suite

	lengthGenerator *mockArrayLengthGenerator
	schemaGenerator *mockSchemaGenerator

	generator *uniqueArrayGenerator
}

func (suite *UniqueArrayGeneratorSuite) SetupTest() {
	suite.lengthGenerator = &mockArrayLengthGenerator{}
	suite.schemaGenerator = &mockSchemaGenerator{}

	suite.generator = &uniqueArrayGenerator{
		lengthGenerator: suite.lengthGenerator,
		schemaGenerator: suite.schemaGenerator,
	}
}

func TestUniqueArrayGeneratorSuite(t *testing.T) {
	suite.Run(t, new(UniqueArrayGeneratorSuite))
}

func (suite *UniqueArrayGeneratorSuite) TestGenerateDataBySchema_ZeroLength_EmptySlice() {
	schema := openapi3.NewSchema()
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(0), uint64(0)).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Len(data, 0)
}

func (suite *UniqueArrayGeneratorSuite) TestGenerateDataBySchema_OneElementLengthAndGeneratedValue_SliceWithOneValue() {
	itemsSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.Items = openapi3.NewSchemaRef("", itemsSchema)
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(1), uint64(0)).Once()
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, itemsSchema).Return("data", nil).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Len(data, 1)
	suite.Equal("data", data.([]interface{})[0])
}

func (suite *UniqueArrayGeneratorSuite) TestGenerateDataBySchema_LengthIs2AndMinLengthIs1AndCannotGenerateSecondUniqueValue_SliceWithOneValue() {
	itemsSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.Items = openapi3.NewSchemaRef("", itemsSchema)
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(2), uint64(1)).Once()
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, itemsSchema).Return("data", nil)

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Len(data, 1)
	suite.Equal("data", data.([]interface{})[0])
}

func (suite *UniqueArrayGeneratorSuite) TestGenerateDataBySchema_LengthIs2AndMinLengthIs2AndCannotGenerateSecondUniqueValue_Error() {
	itemsSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.Items = openapi3.NewSchemaRef("", itemsSchema)
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(2), uint64(2)).Once()
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, itemsSchema).Return("data", nil)

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.EqualError(err, "[uniqueArrayGenerator] failed to generate array with unique values: [uniqueValueGenerator] failed to generate unique value: attempts limit exceeded")
	suite.True(errors.Is(err, errAttemptsLimitExceeded))
	suite.Len(data, 1)
	suite.Equal("data", data.([]interface{})[0])
}

func (suite *UniqueArrayGeneratorSuite) TestGenerateDataBySchema_GenerationError_Error() {
	itemsSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.Items = openapi3.NewSchemaRef("", itemsSchema)
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(1), uint64(0)).Once()
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, itemsSchema).Return(nil, errors.New("error")).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.EqualError(err, "[uniqueArrayGenerator] failed to generate array with unique values: [uniqueValueGenerator] failed to generate value: error")
	suite.Len(data, 0)
}

func (suite *UniqueArrayGeneratorSuite) assertExpectations() {
	suite.lengthGenerator.AssertExpectations(suite.T())
	suite.schemaGenerator.AssertExpectations(suite.T())
}
