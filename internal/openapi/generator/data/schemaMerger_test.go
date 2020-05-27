package data

import (
	"github.com/getkin/kin-openapi/openapi3"
	"github.com/stretchr/testify/assert"
	"testing"
)

var (
	testAllowed        = true
	testFloat64        = 1.1
	testUint64  uint64 = 10
)

func TestCombinedSchemaMerger_MergeSchemas_TwoAllOfSchemas_MergedSchema(t *testing.T) {
	schema1 := givenSchema1()
	schema2 := givenSchema2()
	schema := openapi3.NewSchema()
	schema.AllOf = []*openapi3.SchemaRef{
		openapi3.NewSchemaRef("", schema1),
		openapi3.NewSchemaRef("", schema2),
	}
	merger := &combinedSchemaMerger{}

	mergedSchema := merger.MergeSchemas(schema)

	assertSchemaEqualsToMergedSchema(t, mergedSchema)
}

func TestCombinedSchemaMerger_MergeSchemas_ThreeAllOfSchemasAndLastOneIsEmpty_MergedSchemaShouldBeEqualTo1And2(t *testing.T) {
	schema1 := givenSchema1()
	schema2 := givenSchema2()
	schema := openapi3.NewSchema()
	schema.AllOf = []*openapi3.SchemaRef{
		openapi3.NewSchemaRef("", schema1),
		openapi3.NewSchemaRef("", schema2),
		openapi3.NewSchemaRef("", openapi3.NewSchema()),
	}
	merger := &combinedSchemaMerger{}

	mergedSchema := merger.MergeSchemas(schema)

	assertSchemaEqualsToMergedSchema(t, mergedSchema)
}

func TestCombinedSchemaMerger_MergeSchemas_HierarchicalAllOfSchemas_RecursivelyMergedSchema(t *testing.T) {
	schema1 := givenSchema1()
	schema2 := givenSchema2()
	internalSchema := openapi3.NewSchema()
	internalSchema.AllOf = []*openapi3.SchemaRef{
		openapi3.NewSchemaRef("", schema2),
		openapi3.NewSchemaRef("", openapi3.NewSchema()),
	}
	schema := openapi3.NewSchema()
	schema.AllOf = []*openapi3.SchemaRef{
		openapi3.NewSchemaRef("", schema1),
		openapi3.NewSchemaRef("", openapi3.NewSchema()),
		openapi3.NewSchemaRef("", internalSchema),
	}
	merger := &combinedSchemaMerger{}

	mergedSchema := merger.MergeSchemas(schema)

	assertSchemaEqualsToMergedSchema(t, mergedSchema)
}

func TestCombinedSchemaMerger_MergeSchemas_TwoAnyOfSchemasAndFirstSchemaRandomlyChosen_FirstSchema(t *testing.T) {
	randomMock := &mockRandomGenerator{}
	schema1 := givenSchema1()
	schema2 := givenSchema2()
	schema := openapi3.NewSchema()
	schema.AnyOf = []*openapi3.SchemaRef{
		openapi3.NewSchemaRef("", schema1),
		openapi3.NewSchemaRef("", schema2),
	}
	randomMock.On("Intn", 2).Return(0).Once()
	randomMock.On("Float64").Return(0.49).Twice()
	merger := &combinedSchemaMerger{random: randomMock}

	mergedSchema := merger.MergeSchemas(schema)

	randomMock.AssertExpectations(t)
	assertSchemaEqualsToFirstSchema(t, mergedSchema)
}

func TestCombinedSchemaMerger_MergeSchemas_TwoAnyOfSchemasAndAllSchemasRandomlyChosen_MergedSchema(t *testing.T) {
	randomMock := &mockRandomGenerator{}
	schema1 := givenSchema1()
	schema2 := givenSchema2()
	schema := openapi3.NewSchema()
	schema.AnyOf = []*openapi3.SchemaRef{
		openapi3.NewSchemaRef("", schema1),
		openapi3.NewSchemaRef("", schema2),
	}
	randomMock.On("Intn", 2).Return(0).Once()
	randomMock.On("Float64").Return(0.5).Twice()
	merger := &combinedSchemaMerger{random: randomMock}

	mergedSchema := merger.MergeSchemas(schema)

	randomMock.AssertExpectations(t)
	assertSchemaEqualsToMergedSchema(t, mergedSchema)
}

func TestCombinedSchemaMerger_MergeSchemas_TwoOneOfSchemasAndFirstSchemaRandomlyChosen_FirstSchema(t *testing.T) {
	randomMock := &mockRandomGenerator{}
	schema1 := givenSchema1()
	schema2 := givenSchema2()
	schema := openapi3.NewSchema()
	schema.OneOf = []*openapi3.SchemaRef{
		openapi3.NewSchemaRef("", schema1),
		openapi3.NewSchemaRef("", schema2),
	}
	randomMock.On("Intn", 2).Return(0).Once()
	merger := &combinedSchemaMerger{random: randomMock}

	mergedSchema := merger.MergeSchemas(schema)

	randomMock.AssertExpectations(t)
	assertSchemaEqualsToFirstSchema(t, mergedSchema)
}

func TestIsEmpty(t *testing.T) {
	tests := []struct {
		name            string
		value           interface{}
		expectedIsEmpty bool
	}{
		{
			"nil",
			nil,
			true,
		},
		{
			"empty string",
			"",
			true,
		},
		{
			"non empty string",
			"a",
			false,
		},
		{
			"empty int",
			0,
			true,
		},
		{
			"non empty int",
			1,
			false,
		},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			assert.Equal(t, test.expectedIsEmpty, isEmpty(test.value))
		})
	}
}

func givenSchema1() *openapi3.Schema {
	schema1 := openapi3.NewSchema()
	schema1.Type = "type1"
	schema1.Title = "title1"
	schema1.Format = "format1"
	schema1.Description = "description1"
	schema1.Enum = []interface{}{"enum1"}
	schema1.Default = "default1"
	schema1.Example = "example1"
	schema1.AdditionalPropertiesAllowed = nil
	schema1.UniqueItems = false
	schema1.ExclusiveMin = false
	schema1.ExclusiveMax = false
	schema1.Nullable = false
	schema1.ReadOnly = false
	schema1.WriteOnly = false
	schema1.Min = nil
	schema1.Max = nil
	schema1.MultipleOf = nil
	schema1.MinLength = 1
	schema1.Pattern = "pattern1"
	schema1.MinItems = 1
	schema1.MaxItems = nil
	schema1.Required = []string{"required1"}
	schema1.Properties = map[string]*openapi3.SchemaRef{
		"property1": openapi3.NewSchemaRef("", openapi3.NewSchema()),
	}
	schema1.MinProps = 1
	schema1.MaxProps = nil
	schema1.AdditionalProperties = nil
	schema1.Discriminator = nil

	return schema1
}

func givenSchema2() *openapi3.Schema {
	schema2 := openapi3.NewSchema()
	schema2.Type = "type2"
	schema2.Title = "title2"
	schema2.Format = "format2"
	schema2.Description = "description2"
	schema2.Enum = []interface{}{"enum2"}
	schema2.Default = "default2"
	schema2.Example = "example2"
	schema2.AdditionalPropertiesAllowed = &testAllowed
	schema2.UniqueItems = true
	schema2.ExclusiveMin = true
	schema2.ExclusiveMax = true
	schema2.Nullable = true
	schema2.ReadOnly = true
	schema2.WriteOnly = true
	schema2.Min = &testFloat64
	schema2.Max = &testFloat64
	schema2.MultipleOf = &testFloat64
	schema2.MinLength = 2
	schema2.MaxLength = &testUint64
	schema2.Pattern = "pattern2"
	schema2.MinItems = 2
	schema2.MaxItems = &testUint64
	schema2.Required = []string{"required2"}
	schema2.Properties = map[string]*openapi3.SchemaRef{
		"property2": openapi3.NewSchemaRef("", openapi3.NewSchema()),
	}
	schema2.MinProps = 2
	schema2.MaxProps = &testUint64
	schema2.AdditionalProperties = openapi3.NewSchemaRef("", openapi3.NewSchema())
	schema2.Discriminator = &openapi3.Discriminator{}

	return schema2
}

func assertSchemaEqualsToFirstSchema(t *testing.T, mergedSchema *openapi3.Schema) {
	assert.Equal(t, "type1", mergedSchema.Type)
	assert.Equal(t, "title1", mergedSchema.Title)
	assert.Equal(t, "format1", mergedSchema.Format)
	assert.Equal(t, "description1", mergedSchema.Description)
	assert.Equal(t, []interface{}{"enum1"}, mergedSchema.Enum)
	assert.Equal(t, "default1", mergedSchema.Default)
	assert.Equal(t, "example1", mergedSchema.Example)
	assert.Nil(t, mergedSchema.AdditionalPropertiesAllowed)
	assert.False(t, mergedSchema.UniqueItems)
	assert.False(t, mergedSchema.ExclusiveMin)
	assert.False(t, mergedSchema.ExclusiveMax)
	assert.False(t, mergedSchema.Nullable)
	assert.False(t, mergedSchema.ReadOnly)
	assert.False(t, mergedSchema.WriteOnly)
	assert.Nil(t, mergedSchema.Min)
	assert.Nil(t, mergedSchema.Max)
	assert.Nil(t, mergedSchema.MultipleOf)
	assert.Equal(t, uint64(1), mergedSchema.MinLength)
	assert.Nil(t, mergedSchema.MaxLength)
	assert.Equal(t, "pattern1", mergedSchema.Pattern)
	assert.Equal(t, uint64(1), mergedSchema.MinItems)
	assert.Nil(t, mergedSchema.MaxItems)
	assert.Equal(t, []string{"required1"}, mergedSchema.Required)
	assert.Len(t, mergedSchema.Properties, 1)
	assert.NotNil(t, mergedSchema.Properties["property1"])
	assert.Equal(t, uint64(1), mergedSchema.MinProps)
	assert.Nil(t, mergedSchema.MaxProps)
	assert.Nil(t, mergedSchema.AdditionalProperties)
	assert.Nil(t, mergedSchema.Discriminator)
}

func assertSchemaEqualsToMergedSchema(t *testing.T, mergedSchema *openapi3.Schema) {
	assert.Equal(t, "type2", mergedSchema.Type)
	assert.Equal(t, "title2", mergedSchema.Title)
	assert.Equal(t, "format2", mergedSchema.Format)
	assert.Equal(t, "description2", mergedSchema.Description)
	assert.Equal(t, []interface{}{"enum1", "enum2"}, mergedSchema.Enum)
	assert.Equal(t, "default2", mergedSchema.Default)
	assert.Equal(t, "example2", mergedSchema.Example)
	assert.True(t, *mergedSchema.AdditionalPropertiesAllowed)
	assert.True(t, mergedSchema.UniqueItems)
	assert.True(t, mergedSchema.ExclusiveMin)
	assert.True(t, mergedSchema.ExclusiveMax)
	assert.True(t, mergedSchema.Nullable)
	assert.True(t, mergedSchema.ReadOnly)
	assert.True(t, mergedSchema.WriteOnly)
	assert.Equal(t, testFloat64, *mergedSchema.Min)
	assert.Equal(t, testFloat64, *mergedSchema.Max)
	assert.Equal(t, testFloat64, *mergedSchema.MultipleOf)
	assert.Equal(t, uint64(2), mergedSchema.MinLength)
	assert.Equal(t, testUint64, *mergedSchema.MaxLength)
	assert.Equal(t, "pattern2", mergedSchema.Pattern)
	assert.Equal(t, uint64(2), mergedSchema.MinItems)
	assert.Equal(t, testUint64, *mergedSchema.MaxItems)
	assert.Equal(t, []string{"required1", "required2"}, mergedSchema.Required)
	assert.Len(t, mergedSchema.Properties, 2)
	assert.NotNil(t, mergedSchema.Properties["property1"])
	assert.NotNil(t, mergedSchema.Properties["property2"])
	assert.Equal(t, uint64(2), mergedSchema.MinProps)
	assert.Equal(t, &testUint64, mergedSchema.MaxProps)
	assert.NotNil(t, mergedSchema.AdditionalProperties)
	assert.NotNil(t, mergedSchema.Discriminator)
}
