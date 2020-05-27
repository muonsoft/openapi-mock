package data

import (
	"github.com/stretchr/testify/assert"
	"testing"
)

func TestRandomArrayLengthGenerator_GenerateLength_GivenRange_ExpectedLengths(t *testing.T) {
	tests := []struct {
		name              string
		min               uint64
		max               uint64
		randomValue       int
		expectedMaxValue  int
		expectedLength    int
		expectedMinLength int
	}{
		{
			"empty values, random value equals to min",
			0,
			0,
			0,
			defaultMaxItems + 1,
			0,
			0,
		},
		{
			"empty values, random value equals to max",
			0,
			0,
			defaultMaxItems,
			defaultMaxItems + 1,
			defaultMaxItems,
			0,
		},
		{
			"given range, random value equals to min",
			10,
			100,
			0,
			91,
			10,
			10,
		},
		{
			"given range, random value equals to max",
			10,
			100,
			90,
			91,
			100,
			10,
		},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			randomMock := &mockRandomGenerator{}
			generator := &randomArrayLengthGenerator{random: randomMock}
			randomMock.On("Intn", test.expectedMaxValue).Return(test.randomValue).Once()

			length, minLength := generator.GenerateLength(test.min, test.max)

			randomMock.AssertExpectations(t)
			assert.Equal(t, uint64(test.expectedLength), length)
			assert.Equal(t, uint64(test.expectedMinLength), minLength)
		})
	}
}

func TestRandomArrayLengthGenerator_GenerateLength_MaxLessThanMin_MinValueReturned(t *testing.T) {
	generator := &randomArrayLengthGenerator{}

	length, minLength := generator.GenerateLength(100, 0)

	assert.Equal(t, uint64(100), length)
	assert.Equal(t, uint64(100), minLength)
}
