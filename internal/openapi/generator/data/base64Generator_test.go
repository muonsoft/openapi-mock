package data

import (
	"github.com/stretchr/testify/assert"
	"math/rand"
	"testing"
	"time"
)

func TestBase64Generator_GenerateBase64Text_NoRange_GeneratedTextWithDefaultMaxLength(t *testing.T) {
	randomSource := rand.NewSource(time.Now().UnixNano())
	base64GeneratorInstance := &base64Generator{
		generator: &rangedTextGenerator{
			random: rand.New(randomSource),
		},
	}

	for i := 0; i < 100; i++ {
		text := base64GeneratorInstance.GenerateBase64Text(0, 0)

		assert.GreaterOrEqual(t, len(text), 0)
		assert.LessOrEqual(t, len(text), defaultMaxLength)
	}
}

func TestBase64Generator_GenerateBase64Text_FixedRange_GeneratedTextWithFixedLength(t *testing.T) {
	randomSource := rand.NewSource(time.Now().UnixNano())
	base64GeneratorInstance := &base64Generator{
		generator: &rangedTextGenerator{
			random: rand.New(randomSource),
		},
	}

	for i := 0; i < 100; i++ {
		text := base64GeneratorInstance.GenerateBase64Text(defaultMaxLength, defaultMaxLength)

		assert.Len(t, text, defaultMaxLength)
	}
}

func TestBase64Generator_GenerateBase64Text_MaxLength1_StaticText(t *testing.T) {
	randomSource := rand.NewSource(time.Now().UnixNano())
	base64GeneratorInstance := &base64Generator{
		generator: &rangedTextGenerator{
			random: rand.New(randomSource),
		},
	}

	text := base64GeneratorInstance.GenerateBase64Text(0, 1)

	assert.Equal(t, "=", text)
}
