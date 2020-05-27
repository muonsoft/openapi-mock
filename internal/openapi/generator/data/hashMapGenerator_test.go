package data

import (
	"context"
	"errors"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/mock"
	"github.com/stretchr/testify/suite"
	"testing"
)

type HashMapGeneratorSuite struct {
	suite.Suite

	lengthGenerator *mockArrayLengthGenerator
	keyGenerator    *mockKeyGenerator
	schemaGenerator *mockSchemaGenerator

	generator *hashMapGenerator
}

func (suite *HashMapGeneratorSuite) SetupTest() {
	suite.lengthGenerator = &mockArrayLengthGenerator{}
	suite.keyGenerator = &mockKeyGenerator{}
	suite.schemaGenerator = &mockSchemaGenerator{}

	suite.generator = &hashMapGenerator{
		lengthGenerator: suite.lengthGenerator,
		keyGenerator:    suite.keyGenerator,
		schemaGenerator: suite.schemaGenerator,
	}
}

func TestHashMapGeneratorSuite(t *testing.T) {
	suite.Run(t, new(HashMapGeneratorSuite))
}

func (suite *HashMapGeneratorSuite) TestGenerateDataBySchema_NoLimitsAndZeroLength_EmptyMap() {
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(0), uint64(0)).Once()
	schema := openapi3.NewSchema()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Len(data, 0)
}

func (suite *HashMapGeneratorSuite) TestGenerateDataBySchema_NoLimitsAndLengthIs1_OneKeyValueGenerated() {
	propertiesSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.AdditionalProperties = openapi3.NewSchemaRef("", propertiesSchema)
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(1), uint64(0)).Once()
	suite.keyGenerator.On("GenerateKey").Return("key", nil).Once()
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, propertiesSchema).Return("value", nil).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Len(data, 1)
	suite.Equal("value", data.(map[string]interface{})["key"])
}

func (suite *HashMapGeneratorSuite) TestGenerateDataBySchema_LengthIs2AndMinLengthIs1AndCannotGenerateSecondUniqueValue_MapWithOneValue() {
	propertiesSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.MinProps = 2
	schema.MaxProps = &schema.MinProps
	schema.AdditionalProperties = openapi3.NewSchemaRef("", propertiesSchema)
	suite.lengthGenerator.On("GenerateLength", uint64(2), uint64(2)).Return(uint64(2), uint64(1)).Once()
	suite.keyGenerator.On("GenerateKey").Return("key", nil)
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, propertiesSchema).Return("value", nil).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Len(data, 1)
	suite.Equal("value", data.(map[string]interface{})["key"])
}

func (suite *HashMapGeneratorSuite) TestGenerateDataBySchema_LengthIs2AndMinLengthIs2AndCannotGenerateSecondUniqueValue_Error() {
	propertiesSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.MinProps = 2
	schema.MaxProps = &schema.MinProps
	schema.AdditionalProperties = openapi3.NewSchemaRef("", propertiesSchema)
	suite.lengthGenerator.On("GenerateLength", uint64(2), uint64(2)).Return(uint64(2), uint64(2)).Once()
	suite.keyGenerator.On("GenerateKey").Return("key", nil)
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, propertiesSchema).Return("value", nil).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.EqualError(err, "[hashMapGenerator] failed to generate hash map key: [uniqueKeyGenerator] failed to generate unique key: attempts limit exceeded")
	suite.Len(data, 1)
	suite.Equal("value", data.(map[string]interface{})["key"])
}

func (suite *HashMapGeneratorSuite) TestGenerateDataBySchema_ValueGenerationError_Error() {
	propertiesSchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.AdditionalProperties = openapi3.NewSchemaRef("", propertiesSchema)
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(1), uint64(0)).Once()
	suite.keyGenerator.On("GenerateKey").Return("key", nil)
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, propertiesSchema).Return(nil, errors.New("error")).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.EqualError(err, "[hashMapGenerator] failed to generate hash map value: error")
	suite.Len(data, 0)
}

func (suite *HashMapGeneratorSuite) TestGenerateDataBySchema_DefaultProperty_DefaultValueGenerated() {
	propertiesSchema := openapi3.NewSchema()
	defaultPropertySchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.Required = []string{"default"}
	schema.Properties = map[string]*openapi3.SchemaRef{
		"default": {
			Value: defaultPropertySchema,
		},
	}
	schema.AdditionalProperties = openapi3.NewSchemaRef("", propertiesSchema)
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(1), uint64(0)).Once()
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, defaultPropertySchema).Return("value", nil).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Len(data, 1)
	suite.Equal("value", data.(map[string]interface{})["default"])
}

func (suite *HashMapGeneratorSuite) TestGenerateDataBySchema_DefaultPropertyGenerationError_Error() {
	propertiesSchema := openapi3.NewSchema()
	defaultPropertySchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.Required = []string{"default"}
	schema.Properties = map[string]*openapi3.SchemaRef{
		"default": {
			Value: defaultPropertySchema,
		},
	}
	schema.AdditionalProperties = openapi3.NewSchemaRef("", propertiesSchema)
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(1), uint64(0)).Once()
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, defaultPropertySchema).Return(nil, errors.New("error")).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.EqualError(err, "[hashMapGenerator] failed to generate default value 'default': error")
	suite.Len(data, 0)
}

func (suite *HashMapGeneratorSuite) TestGenerateDataBySchema_DefaultPropertyAndRandomPropertyWithEqualKey_DefaultValueOnlyGenerated() {
	propertiesSchema := openapi3.NewSchema()
	defaultPropertySchema := openapi3.NewSchema()
	schema := openapi3.NewSchema()
	schema.Required = []string{"default"}
	schema.Properties = map[string]*openapi3.SchemaRef{
		"default": {
			Value: defaultPropertySchema,
		},
	}
	schema.AdditionalProperties = openapi3.NewSchemaRef("", propertiesSchema)
	suite.lengthGenerator.On("GenerateLength", uint64(0), uint64(0)).Return(uint64(2), uint64(0)).Once()
	suite.keyGenerator.On("GenerateKey").Return("default", nil)
	suite.schemaGenerator.On("GenerateDataBySchema", mock.Anything, defaultPropertySchema).Return("value", nil).Once()

	data, err := suite.generator.GenerateDataBySchema(context.Background(), schema)

	suite.assertExpectations()
	suite.NoError(err)
	suite.Len(data, 1)
	suite.Equal("value", data.(map[string]interface{})["default"])
}

func (suite *HashMapGeneratorSuite) assertExpectations() {
	suite.lengthGenerator.AssertExpectations(suite.T())
	suite.keyGenerator.AssertExpectations(suite.T())
	suite.schemaGenerator.AssertExpectations(suite.T())
}
