package data

import (
	"github.com/getkin/kin-openapi/openapi3"
	"reflect"
)

type schemaMerger interface {
	MergeSchemas(schema *openapi3.Schema) *openapi3.Schema
}

type combinedSchemaMerger struct {
	random randomGenerator
}

func (merger *combinedSchemaMerger) MergeSchemas(schema *openapi3.Schema) *openapi3.Schema {
	var mergedSchema *openapi3.Schema

	switch {
	case schema.AllOf != nil:
		mergedSchema = merger.mergeAllSchemas(schema.AllOf)
	case schema.AnyOf != nil:
		mergedSchema = merger.mergeAnySchemasRandomly(schema.AnyOf)
	case schema.OneOf != nil:
		n := merger.random.Intn(len(schema.OneOf))
		mergedSchema = schema.OneOf[n].Value
	}

	return mergedSchema
}

func (merger *combinedSchemaMerger) mergeAllSchemas(schemas []*openapi3.SchemaRef) *openapi3.Schema {
	mergedSchema := openapi3.NewSchema()

	for _, nextSchema := range schemas {
		if merger.isCombiningSchema(nextSchema.Value) {
			internalSchema := merger.MergeSchemas(nextSchema.Value)
			merger.mergeAllNonEmptyAttributes(mergedSchema, internalSchema)
		} else {
			merger.mergeAllNonEmptyAttributes(mergedSchema, nextSchema.Value)
		}
	}

	return mergedSchema
}

func (merger *combinedSchemaMerger) mergeAnySchemasRandomly(schemas []*openapi3.SchemaRef) *openapi3.Schema {
	mergedSchema := openapi3.NewSchema()

	needsToBeMerged := merger.generateRandomlyNeedsToBeMerged(schemas)
	for i := range needsToBeMerged {
		if needsToBeMerged[i] {
			if merger.isCombiningSchema(schemas[i].Value) {
				internalSchema := merger.MergeSchemas(schemas[i].Value)
				merger.mergeAllNonEmptyAttributes(mergedSchema, internalSchema)
			} else {
				merger.mergeAllNonEmptyAttributes(mergedSchema, schemas[i].Value)
			}
		}
	}

	return mergedSchema
}

func (merger *combinedSchemaMerger) generateRandomlyNeedsToBeMerged(schemas []*openapi3.SchemaRef) []bool {
	needsToBeMerged := make([]bool, len(schemas))
	// guarantees that at least 1 schema will be merged
	needsToBeMerged[merger.random.Intn(len(schemas))] = true

	for i := 0; i < len(schemas); i++ {
		if merger.random.Float64() >= 0.5 {
			needsToBeMerged[i] = true
		}
	}

	return needsToBeMerged
}

func (merger *combinedSchemaMerger) isCombiningSchema(nextSchema *openapi3.Schema) bool {
	return nextSchema.AllOf != nil || nextSchema.AnyOf != nil || nextSchema.OneOf != nil
}

func (merger *combinedSchemaMerger) mergeAllNonEmptyAttributes(mergedSchema *openapi3.Schema, mergingSchema *openapi3.Schema) {
	// string attributes
	mergedSchema.Type = merger.replaceNonEmptyString(mergedSchema.Type, mergingSchema.Type)
	mergedSchema.Title = merger.replaceNonEmptyString(mergedSchema.Title, mergingSchema.Title)
	mergedSchema.Format = merger.replaceNonEmptyString(mergedSchema.Format, mergingSchema.Format)
	mergedSchema.Description = merger.replaceNonEmptyString(mergedSchema.Description, mergingSchema.Description)
	mergedSchema.Enum = append(mergedSchema.Enum, mergingSchema.Enum...)
	mergedSchema.Default = merger.replaceNonEmpty(mergedSchema.Default, mergingSchema.Default)
	mergedSchema.Example = merger.replaceNonEmpty(mergedSchema.Example, mergingSchema.Example)
	mergedSchema.Pattern = merger.replaceNonEmptyString(mergedSchema.Pattern, mergingSchema.Pattern)

	// bool attributes
	mergedSchema.UniqueItems = mergedSchema.UniqueItems || mergingSchema.UniqueItems
	mergedSchema.ExclusiveMin = mergedSchema.ExclusiveMin || mergingSchema.ExclusiveMin
	mergedSchema.ExclusiveMax = mergedSchema.ExclusiveMax || mergingSchema.ExclusiveMax
	mergedSchema.Nullable = mergedSchema.Nullable || mergingSchema.Nullable
	mergedSchema.ReadOnly = mergedSchema.ReadOnly || mergingSchema.ReadOnly
	mergedSchema.WriteOnly = mergedSchema.WriteOnly || mergingSchema.WriteOnly

	// property attributes
	mergedSchema.Required = append(mergedSchema.Required, mergingSchema.Required...)
	merger.mergeProperties(mergedSchema, mergingSchema)
	merger.mergeAdditionalProperties(mergedSchema, mergingSchema)
	merger.mergeDiscriminator(mergedSchema, mergingSchema)

	// numeric attributes
	mergedSchema.Min = merger.replaceNonNilFloat64(mergedSchema.Min, mergingSchema.Min)
	mergedSchema.Max = merger.replaceNonNilFloat64(mergedSchema.Max, mergingSchema.Max)
	mergedSchema.MultipleOf = merger.replaceNonNilFloat64(mergedSchema.MultipleOf, mergingSchema.MultipleOf)
	mergedSchema.MinLength = merger.replaceNonZeroUint64(mergedSchema.MinLength, mergingSchema.MinLength)
	mergedSchema.MaxLength = merger.replaceNonNilUint64(mergedSchema.MaxLength, mergingSchema.MaxLength)
	mergedSchema.MinItems = merger.replaceNonZeroUint64(mergedSchema.MinItems, mergingSchema.MinItems)
	mergedSchema.MaxItems = merger.replaceNonNilUint64(mergedSchema.MaxItems, mergingSchema.MaxItems)
	mergedSchema.MinProps = merger.replaceNonZeroUint64(mergedSchema.MinProps, mergingSchema.MinProps)
	mergedSchema.MaxProps = merger.replaceNonNilUint64(mergedSchema.MaxProps, mergingSchema.MaxProps)
}

func (merger *combinedSchemaMerger) mergeProperties(mergedSchema *openapi3.Schema, mergingSchema *openapi3.Schema) {
	if len(mergingSchema.Properties) > 0 && mergedSchema.Properties == nil {
		mergedSchema.Properties = map[string]*openapi3.SchemaRef{}
	}
	for propertyName, propertySchema := range mergingSchema.Properties {
		mergedSchema.Properties[propertyName] = propertySchema
	}
}

func (merger *combinedSchemaMerger) mergeAdditionalProperties(mergedSchema *openapi3.Schema, mergingSchema *openapi3.Schema) {
	if mergingSchema.AdditionalPropertiesAllowed != nil {
		mergedSchema.AdditionalPropertiesAllowed = mergingSchema.AdditionalPropertiesAllowed
	}
	if mergingSchema.AdditionalProperties != nil {
		mergedSchema.AdditionalProperties = mergingSchema.AdditionalProperties
	}
}

func (merger *combinedSchemaMerger) mergeDiscriminator(mergedSchema *openapi3.Schema, mergingSchema *openapi3.Schema) {
	if mergingSchema.Discriminator != nil {
		mergedSchema.Discriminator = mergingSchema.Discriminator
	}
}

func (merger *combinedSchemaMerger) replaceNonEmpty(s1 interface{}, s2 interface{}) interface{} {
	if !isEmpty(s2) {
		s1 = s2
	}
	return s1
}

func (merger *combinedSchemaMerger) replaceNonEmptyString(s1 string, s2 string) string {
	if s2 != "" {
		s1 = s2
	}
	return s1
}

func (merger *combinedSchemaMerger) replaceNonNilFloat64(f1 *float64, f2 *float64) *float64 {
	if f2 != nil {
		f1 = f2
	}
	return f1
}

func (merger *combinedSchemaMerger) replaceNonZeroUint64(u1 uint64, u2 uint64) uint64 {
	if u2 != 0 {
		u1 = u2
	}
	return u1
}

func (merger *combinedSchemaMerger) replaceNonNilUint64(u1 *uint64, u2 *uint64) *uint64 {
	if u2 != nil {
		u1 = u2
	}
	return u1
}

func isEmpty(x interface{}) bool {
	return x == nil || x == reflect.Zero(reflect.TypeOf(x)).Interface()
}
