package data

import (
	"errors"
	"github.com/stretchr/testify/assert"
	"github.com/stretchr/testify/suite"
	"syreclabs.com/go/faker"
	"testing"
)

func TestCamelCaseKeyGenerator_GenerateKey_NoParams_CamelCaseKey(t *testing.T) {
	faker.Seed(0)
	randomMock := &mockRandomGenerator{}
	generator := &camelCaseKeyGenerator{random: randomMock}
	randomMock.On("Intn", 9).Return(2).Once()

	key, err := generator.GenerateKey()

	randomMock.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "mollitiaPariaturNon", key)
}

type UniqueKeyGeneratorSuite struct {
	suite.Suite

	keyGenerator *mockKeyGenerator

	generator *uniqueKeyGenerator
}

func (suite *UniqueKeyGeneratorSuite) SetupTest() {
	suite.keyGenerator = &mockKeyGenerator{}

	suite.generator = newUniqueKeyGenerator(suite.keyGenerator).(*uniqueKeyGenerator)
}

func TestUniqueKeyGeneratorSuite(t *testing.T) {
	suite.Run(t, new(UniqueKeyGeneratorSuite))
}

func (suite *UniqueKeyGeneratorSuite) TestGenerateKey_EmptyUniqueValues_FirstValue() {
	suite.keyGenerator.On("GenerateKey").Return("key", nil)

	key, err := suite.generator.GenerateKey()

	suite.keyGenerator.AssertExpectations(suite.T())
	suite.NoError(err)
	suite.Equal("key", key)
}

func (suite *UniqueKeyGeneratorSuite) TestGenerateKey_SecondValueIsNotUnique_ThirdValueReturned() {
	suite.keyGenerator.On("GenerateKey").Return("unique", nil).Once()

	suite.generator.AddKey("notUnique")
	key, err := suite.generator.GenerateKey()

	suite.keyGenerator.AssertExpectations(suite.T())
	suite.NoError(err)
	suite.Equal("unique", key)
}

func (suite *UniqueKeyGeneratorSuite) TestGenerateKey_FailedToGenerateUniqueValue_ErrorReturned() {
	suite.keyGenerator.On("GenerateKey").Return("key", nil).Times(maxAttempts + 1)

	_, _ = suite.generator.GenerateKey()
	key, err := suite.generator.GenerateKey()

	suite.keyGenerator.AssertExpectations(suite.T())
	suite.EqualError(err, "[uniqueKeyGenerator] failed to generate unique key: attempts limit exceeded")
	suite.True(errors.Is(err, errAttemptsLimitExceeded))
	suite.Equal("", key)
}

func (suite *UniqueKeyGeneratorSuite) TestGenerateKey_GenerationError_ErrorReturned() {
	suite.keyGenerator.On("GenerateKey").Return("key", errors.New("error")).Once()

	key, err := suite.generator.GenerateKey()

	suite.keyGenerator.AssertExpectations(suite.T())
	suite.EqualError(err, "[uniqueKeyGenerator] failed to generate key: error")
	suite.Equal("", key)
}
