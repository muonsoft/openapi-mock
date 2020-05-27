package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"testing"
)

const (
	testDefaultMinInt int64 = 0
	testDefaultMaxInt int64 = 12345
)

func TestIntegerGenerator_GenerateDataBySchema_GivenSchemaAndRandomValue_ExpectedValue(t *testing.T) {
	min := 10.0
	max := 100.0

	tests := []struct {
		name             string
		schema           *openapi3.Schema
		randomValue      int64
		expectedMaxValue int64
		expectedValue    int64
	}{
		{
			"no params, min random value",
			openapi3.NewSchema(),
			0,
			testDefaultMaxInt + 1,
			0,
		},
		{
			"no params, max random value",
			openapi3.NewSchema(),
			testDefaultMaxInt,
			testDefaultMaxInt + 1,
			testDefaultMaxInt,
		},
		{
			"given range, min random value",
			&openapi3.Schema{
				Min: &min,
				Max: &max,
			},
			0,
			91,
			10,
		},
		{
			"given range, max random value",
			&openapi3.Schema{
				Min: &min,
				Max: &max,
			},
			90,
			91,
			100,
		},
		{
			"given exclusive range, min random value",
			&openapi3.Schema{
				Min:          &min,
				Max:          &max,
				ExclusiveMin: true,
				ExclusiveMax: true,
			},
			0,
			89,
			11,
		},
		{
			"given exclusive range, max random value",
			&openapi3.Schema{
				Min:          &min,
				Max:          &max,
				ExclusiveMin: true,
				ExclusiveMax: true,
			},
			88,
			89,
			99,
		},
		{
			"32bit format, min random value",
			&openapi3.Schema{
				Format: "int32",
			},
			0,
			testDefaultMaxInt + 1,
			0,
		},
		{
			"multiple of, random value",
			&openapi3.Schema{
				MultipleOf: &min,
			},
			17,
			testDefaultMaxInt + 1,
			10,
		},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			randomMock := &mockRandomGenerator{}
			integerGeneratorInstance := &integerGenerator{
				random:         randomMock,
				defaultMinimum: testDefaultMinInt,
				defaultMaximum: testDefaultMaxInt,
			}
			randomMock.On("Int63n", test.expectedMaxValue).Return(test.randomValue).Once()

			data, err := integerGeneratorInstance.GenerateDataBySchema(context.Background(), test.schema)

			randomMock.AssertExpectations(t)
			assert.NoError(t, err)
			assert.Equal(t, test.expectedValue, data)
		})
	}
}

func TestIntegerGenerator_GenerateDataBySchema_MaxLessThanMin_Error(t *testing.T) {
	integerGeneratorInstance := &integerGenerator{}
	min := 11.0
	max := 10.0
	schema := openapi3.NewSchema()
	schema.Min = &min
	schema.Max = &max

	data, err := integerGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	assert.EqualError(t, err, "[integerGenerator] maximum cannot be less than minimum")
	assert.Equal(t, 0, data)
}

func TestIntegerGenerator_GenerateDataBySchema_MaxEqualToMin_StaticValue(t *testing.T) {
	integerGeneratorInstance := &integerGenerator{}
	min := 10.0
	max := 10.0
	schema := openapi3.NewSchema()
	schema.Min = &min
	schema.Max = &max

	data, err := integerGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	assert.NoError(t, err)
	assert.Equal(t, int64(10), data)
}
