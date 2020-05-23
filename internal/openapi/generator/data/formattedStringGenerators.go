package data

import (
	"fmt"
	"github.com/gofrs/uuid"
	"syreclabs.com/go/faker"
	"time"
)

type stringGeneratorFunction func(minLength int, maxLength int) string

func defaultFormattedStringGenerators(generator *rangedTextGenerator) map[string]stringGeneratorFunction {
	base64 := &base64Generator{generator: generator}
	html := &htmlGenerator{random: generator.random}

	return map[string]stringGeneratorFunction{
		"date": func(_ int, _ int) string {
			date := generateRandomTime()

			return fmt.Sprintf("%d-%02d-%02d", date.Year(), int(date.Month()), date.Day())
		},

		"date-time": func(_ int, _ int) string {
			date := generateRandomTime()

			return date.Format(time.RFC3339)
		},

		"email": func(_ int, _ int) string {
			return faker.Internet().Email()
		},

		"uri": func(_ int, _ int) string {
			return faker.Internet().Url()
		},

		"hostname": func(_ int, _ int) string {
			return faker.Internet().DomainName()
		},

		"ipv4": func(_ int, _ int) string {
			return faker.Internet().IpV4Address()
		},

		"ipv6": func(_ int, _ int) string {
			return faker.Internet().IpV6Address()
		},

		"uuid": func(_ int, _ int) string {
			return uuid.Must(uuid.NewV4()).String()
		},

		"byte": base64.GenerateBase64Text,
		"html": html.GenerateHTML,
	}
}

func generateRandomTime() time.Time {
	return faker.Date().Between(
		time.Date(1800, 1, 1, 1, 1, 1, 1, time.UTC),
		time.Date(2100, 1, 1, 1, 1, 1, 1, time.UTC),
	)
}
