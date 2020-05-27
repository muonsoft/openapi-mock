package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/suite"
	"syreclabs.com/go/faker"
	"testing"
)

type FreeFormGeneratorSuite struct {
	suite.Suite

	lengthGenerator *mockArrayLengthGenerator
	keyGenerator    *mockKeyGenerator

	generator *freeFormGenerator
}

func (suite *FreeFormGeneratorSuite) SetupTest() {
	suite.lengthGenerator = &mockArrayLengthGenerator{}
	suite.keyGenerator = &mockKeyGenerator{}

	suite.generator = &freeFormGenerator{
		lengthGenerator: suite.lengthGenerator,
		keyGenerator:    suite.keyGenerator,
	}
}

func TestFreeFormGeneratorSuite(t *testing.T) {
	suite.Run(t, new(FreeFormGeneratorSuite))
}

func (suite *FreeFormGeneratorSuite) TestGenerateDataBySchema_NoLimitsAndZeroLength_EmptyMap() {
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(0), uint64(0)).Once()
	schema := openapi3.NewSchema()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Len(data, 0)
}

func (suite *FreeFormGeneratorSuite) TestGenerateDataBySchema_NoLimitsAndLengthIs1_OneKeyValueGenerated() {
	faker.Seed(0)
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(1), uint64(0)).Once()
	suite.keyGenerator.On("GenerateKey").Return("key", nil).Once()
	schema := openapi3.NewSchema()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Len(data, 1)
	suite.Equal("Temporibus laborum omnis.", data.(map[string]interface{})["key"])
}

func (suite *FreeFormGeneratorSuite) TestGenerateDataBySchema_LengthIs2AndMinLengthIs1AndCannotGenerateSecondUniqueValue_MapWithOneValue() {
	faker.Seed(0)
	suite.lengthGenerator.On("GenerateLength", uint64(2), uint64(2)).Return(uint64(2), uint64(1)).Once()
	suite.keyGenerator.On("GenerateKey").Return("key", nil)
	schema := openapi3.NewSchema()
	schema.MinProps = 2
	schema.MaxProps = &schema.MinProps

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Len(data, 1)
	suite.Equal("Temporibus laborum omnis.", data.(map[string]interface{})["key"])
}

func (suite *FreeFormGeneratorSuite) TestGenerateDataBySchema_LengthIs2AndMinLengthIs2AndCannotGenerateSecondUniqueValue_Error() {
	faker.Seed(0)
	suite.lengthGenerator.On("GenerateLength", uint64(2), uint64(2)).Return(uint64(2), uint64(2)).Once()
	suite.keyGenerator.On("GenerateKey").Return("key", nil)
	schema := openapi3.NewSchema()
	schema.MinProps = 2
	schema.MaxProps = &schema.MinProps

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.EqualError(err, "[freeFormGenerator] failed to generate free form object: [uniqueKeyGenerator] failed to generate unique key: attempts limit exceeded")
	suite.Len(data, 1)
	suite.Equal("Temporibus laborum omnis.", data.(map[string]interface{})["key"])
}

func (suite *FreeFormGeneratorSuite) assertExpectations() {
	suite.lengthGenerator.AssertExpectations(suite.T())
	suite.keyGenerator.AssertExpectations(suite.T())
}
