package data

import (
	"github.com/stretchr/testify/assert"
	"math/rand"
	"testing"
)

func TestHtmlGenerator_GenerateHTML_NoParams_GeneratedHTMLReturned(t *testing.T) {
	randomSource := rand.NewSource(0)
	htmlGeneratorInstance := &htmlGenerator{
		random: rand.New(randomSource),
	}

	html := htmlGeneratorInstance.GenerateHTML(0, 0)

	assert.Contains(t, html, "<html lang=\"en\">")
}
