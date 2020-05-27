package responder

import (
	"context"
	"github.com/muonsoft/openapi-mock/internal/openapi/generator"
	"github.com/muonsoft/openapi-mock/internal/openapi/responder/serializer"
	"net/http"
	"regexp"
)

type Responder interface {
	WriteResponse(ctx context.Context, writer http.ResponseWriter, response *generator.Response)
	WriteError(ctx context.Context, writer http.ResponseWriter, err error)
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
