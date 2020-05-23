package data

import (
	"github.com/stretchr/testify/assert"
	"math/rand"
	"testing"
)

func TestDefaultFormattedStringGenerators_GivenFormat_ExpectedRegExp(t *testing.T) {
	tests := []struct {
		format         string
		expectedRegExp string
	}{
		{
			"date",
			"^\\d{4}-\\d{2}-\\d{2}$",
		},
		{
			"date-time",
			"^([0-9]+)-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])[Tt]([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]|60)(\\.[0-9]+)?(([Zz])|([\\+|\\-]([01][0-9]|2[0-3]):[0-5][0-9]))$",
		},
		{
			"email",
			"^\\S+@\\S+$",
		},
		{
			"uri",
			"(http://|https://|www\\.)([^ '\"]*)",
		},
		{
			"hostname",
			".*\\..*",
		},
		{
			"ipv4",
			"^(?:[0-9]{1,3}\\.){3}[0-9]{1,3}$",
		},
		{
			"ipv6",
			"^[a-fA-F0-9:]+$",
		},
		{
			"uuid",
			"\\b[0-9a-f]{8}\\b-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-\\b[0-9a-f]{12}\\b",
		},
	}

	randomSource := rand.NewSource(0)
	generators := defaultFormattedStringGenerators(&rangedTextGenerator{
		random: rand.New(randomSource),
	})

	for _, test := range tests {
		t.Run(test.format, func(t *testing.T) {
			generator := generators[test.format]

			for i := 0; i < 100; i++ {
				value := generator(0, 0)

				assert.Regexp(t, test.expectedRegExp, value)
			}
		})
	}
}
