package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/mock"
	"github.com/stretchr/testify/suite"
	"testing"
)

type ObjectGenerationDelegatorSuite struct {
	suite.Suite

	freeFormGenerator *mockSchemaGenerator
	hashMapGenerator  *mockSchemaGenerator
	objectGenerator   *mockSchemaGenerator

	delegator *objectGenerationDelegator
}

func (suite *ObjectGenerationDelegatorSuite) SetupTest() {
	suite.freeFormGenerator = &mockSchemaGenerator{}
	suite.hashMapGenerator = &mockSchemaGenerator{}
	suite.objectGenerator = &mockSchemaGenerator{}

	suite.delegator = &objectGenerationDelegator{
		freeFormGenerator: suite.freeFormGenerator,
		hashMapGenerator:  suite.hashMapGenerator,
		objectGenerator:   suite.objectGenerator,
	}
}

func TestObjectGenerationDelegatorSuite(t *testing.T) {
	suite.Run(t, new(ObjectGenerationDelegatorSuite))
}

func (suite *ObjectGenerationDelegatorSuite) TestGenerateDataBySchema_GivenObjectSchema_ValueGeneratedByExpectedGenerator() {
	allowed := true
	notAllowed := false

	tests := []struct {
		name                  string
		schema                *openapi3.Schema
		expectedGeneratorData string
	}{
		{
			"object schema",
			openapi3.NewSchema(),
			"object",
		},
		{
			"additional properties true",
			&openapi3.Schema{
				AdditionalPropertiesAllowed: &allowed,
			},
			"freeForm",
		},
		{
			"additional properties false",
			&openapi3.Schema{
				AdditionalPropertiesAllowed: &notAllowed,
			},
			"object",
		},
		{
			"additional properties empty object",
			&openapi3.Schema{
				AdditionalProperties: &openapi3.SchemaRef{},
			},
			"freeForm",
		},
		{
			"additional properties filled",
			&openapi3.Schema{
				AdditionalProperties: &openapi3.SchemaRef{
					Value: &openapi3.Schema{
						Type: "type",
					},
				},
			},
			"hashMap",
		},
	}
	for _, test := range tests {
		suite.T().Run(test.name, func(t *testing.T) {
			suite.objectGenerator.On("GenerateDataBySchema", mock.Anything, mock.Anything).Return("object", nil)
			suite.freeFormGenerator.On("GenerateDataBySchema", mock.Anything, mock.Anything).Return("freeForm", nil)
			suite.hashMapGenerator.On("GenerateDataBySchema", mock.Anything, mock.Anything).Return("hashMap", nil)

			data, err := suite.delegator.GenerateDataBySchema(context.Background(), test.schema)

			assert.NoError(t, err)
			assert.Equal(t, test.expectedGeneratorData, data)
		})
	}
}
