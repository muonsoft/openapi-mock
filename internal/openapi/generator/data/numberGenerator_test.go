package data

import (
	"context"
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"math"
	"testing"
)

const (
	testDefaultMinFloat = -12345.0
	testDefaultMaxFloat = 12345.0
)

func TestNumberGenerator_GenerateDataBySchema_GivenSchemaAndRandomValue_ExpectedValue(t *testing.T) {
	min := 10.0
	max := 100.0

	tests := []struct {
		name             string
		schema           *openapi3.Schema
		randomValue      int
		expectedMaxValue int
		expectedValue    float64
	}{
		{
			"no params, min random value",
			openapi3.NewSchema(),
			0,
			math.MaxInt64,
			testDefaultMinFloat,
		},
		{
			"no params, max random value",
			openapi3.NewSchema(),
			math.MaxInt64,
			math.MaxInt64,
			testDefaultMaxFloat,
		},
		{
			"given range, min random value",
			&openapi3.Schema{
				Min: &min,
				Max: &max,
			},
			0,
			math.MaxInt64,
			10,
		},
		{
			"given range, max random value",
			&openapi3.Schema{
				Min: &min,
				Max: &max,
			},
			math.MaxInt64,
			math.MaxInt64,
			100,
		},
		{
			"exclusive range, min random value",
			&openapi3.Schema{
				Min:          &min,
				Max:          &max,
				ExclusiveMin: true,
				ExclusiveMax: true,
			},
			0,
			math.MaxInt64 - 2,
			10,
		},
		{
			"exclusive range, max random value",
			&openapi3.Schema{
				Min:          &min,
				Max:          &max,
				ExclusiveMin: true,
				ExclusiveMax: true,
			},
			math.MaxInt64 - 2,
			math.MaxInt64 - 2,
			100,
		},
		{
			"given range and multiple of, middle random value",
			&openapi3.Schema{
				Min:        &min,
				Max:        &max,
				MultipleOf: &min,
			},
			math.MaxInt64 / 5,
			math.MaxInt64,
			10,
		},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			randomMock := &mockRandomGenerator{}
			numberGeneratorInstance := &numberGenerator{
				random:         randomMock,
				defaultMinimum: testDefaultMinFloat,
				defaultMaximum: testDefaultMaxFloat,
			}
			randomMock.On("Intn", test.expectedMaxValue).Return(test.randomValue).Twice()

			data, err := numberGeneratorInstance.GenerateDataBySchema(context.Background(), test.schema)

			randomMock.AssertExpectations(t)
			assert.NoError(t, err)
			assert.Equal(t, test.expectedValue, data)
		})
	}
}

func TestNumberGenerator_GenerateDataBySchema_MaxLessThanMin_Error(t *testing.T) {
	numberGeneratorInstance := &numberGenerator{}
	min := 11.0
	max := 10.0
	schema := openapi3.NewSchema()
	schema.Min = &min
	schema.Max = &max

	data, err := numberGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	assert.EqualError(t, err, "[numberGenerator] maximum cannot be less than minimum")
	assert.Equal(t, 0, data)
}

func TestNumberGenerator_GenerateDataBySchema_MaxEqualToMin_StaticValue(t *testing.T) {
	numberGeneratorInstance := &numberGenerator{}
	min := 10.0
	max := 10.0
	schema := openapi3.NewSchema()
	schema.Min = &min
	schema.Max = &max

	data, err := numberGeneratorInstance.GenerateDataBySchema(context.Background(), schema)

	assert.NoError(t, err)
	assert.Equal(t, 10.0, data)
}
