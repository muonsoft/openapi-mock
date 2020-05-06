package generator

import (
	"context"
	"fmt"
	"github.com/getkin/kin-openapi/openapi3"
	"math/rand"
	"syreclabs.com/go/faker"
	"time"
)

type stringGenerator struct {
	random *rand.Rand
}

func (generator *stringGenerator) GenerateDataBySchema(ctx context.Context, schema *openapi3.Schema) (Data, error) {
	var value string
	var err error

	supportedFormatMap := map[string]Data{
		"date":      dateFormat(),
		"date-time": dateTimeFormat(),
		"email":     faker.Internet().Email(),
		"uri":       faker.Internet().Url(),
		"hostname":  faker.Internet().DomainName(),
		"ipv4":      faker.Internet().IpV4Address(),
		"ipv6":      faker.Internet().IpV6Address(),
	}

	if schema.Enum != nil {
		value = generator.generateRandomEnumValue(schema)
	} else if schema.Pattern != "" {
		value, err = generateValueByPattern(schema)
	} else if hasSupportedFormat(schema, supportedFormatMap) {
		value = generateValueByFormat(schema, supportedFormatMap)
	} else {
		value = generateText(schema)
	}

	if err != nil {
		return nil, err
	}

	return value, nil
}

func (generator *stringGenerator) generateRandomEnumValue(schema *openapi3.Schema) string {
	return fmt.Sprint(schema.Enum[generator.random.Intn(len(schema.Enum))])
}

func generateValueByPattern(schema *openapi3.Schema) (string, error) {
	value, err := faker.Regexify(schema.Pattern)

	if err != nil {
		return "", fmt.Errorf("[stringGenerator] Cannot generate string value by pattern %s", schema.Pattern)
	}

	return value, nil
}

func hasSupportedFormat(schema *openapi3.Schema, supportedFormatMap map[string]Data) bool {
	_, supported := supportedFormatMap[schema.Format]

	return supported
}

func generateValueByFormat(schema *openapi3.Schema, supportedFormatMap map[string]Data) string {
	return supportedFormatMap[schema.Format].(string)
}

func generateText(schema *openapi3.Schema) string {
	return faker.RandomString((int)(*schema.MaxLength))
}

func dateFormat() string {
	date := generateDate()

	return fmt.Sprintf("%d-%d-%d", date.Year(), int(date.Month()), date.Day())
}

func dateTimeFormat() string {
	date := generateDate()

	return date.Format(time.RFC3339)
}

func generateDate() time.Time {
	return faker.Date().Between(
		time.Date(1800, 1, 1, 1, 1, 1, 1, time.UTC),
		time.Date(2100, 1, 1, 1, 1, 1, 1, time.UTC))
}
