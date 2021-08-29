package responder

import (
	"context"
	"net/http"
	"regexp"

	"github.com/muonsoft/openapi-mock/internal/openapi/mocking"
	"github.com/muonsoft/openapi-mock/internal/openapi/responder/serializer"
)

type Responder interface {
	WriteResponse(ctx context.Context, writer http.ResponseWriter, response *mocking.Response)
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
