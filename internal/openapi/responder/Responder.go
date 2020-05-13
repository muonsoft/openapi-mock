package responder

import (
	"net/http"
	"regexp"
	"swagger-mock/internal/openapi/generator"
	"swagger-mock/internal/openapi/responder/serializer"
)

type Responder interface {
	WriteResponse(writer http.ResponseWriter, response *generator.Response)
	WriteUnexpectedError(writer http.ResponseWriter, message string)
}

func New() Responder {
	return &coordinatingResponder{
		serializer: serializer.New(),
		formatGuessers: []formatGuess{
			{
				format:  "json",
				pattern: regexp.MustCompile("^application/.*json$"),
			},
			{
				format:  "xml",
				pattern: regexp.MustCompile("^application/.*xml$"),
			},
		},
	}
}
