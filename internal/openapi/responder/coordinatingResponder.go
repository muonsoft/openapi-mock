package responder

import (
	"context"
	"fmt"
	"net/http"
	"regexp"
	"strings"
	"swagger-mock/internal/openapi/generator"
	"swagger-mock/internal/openapi/responder/serializer"
	"swagger-mock/pkg/logcontext"
)

type coordinatingResponder struct {
	serializer     serializer.Serializer
	formatGuessers []formatGuess
}

type formatGuess struct {
	format  string
	pattern *regexp.Regexp
}

func (responder *coordinatingResponder) WriteResponse(ctx context.Context, writer http.ResponseWriter, response *generator.Response) {
	format := responder.guessSerializationFormat(response.ContentType)

	data, err := responder.serializer.Serialize(response.Data, format)
	if err != nil {
		responder.WriteError(ctx, writer, err)
		return
	}

	if response.ContentType != "" {
		writer.Header().Set("Content-Type", fmt.Sprintf("%s; charset=utf-8", response.ContentType))
	}

	writer.Header().Set("X-Content-Type-Options", "nosniff")
	writer.WriteHeader(response.StatusCode)
	_, _ = writer.Write(data)
}

func (responder *coordinatingResponder) WriteError(ctx context.Context, writer http.ResponseWriter, err error) {
	writer.Header().Set("Content-Type", "text/html; charset=utf-8")
	writer.Header().Set("X-Content-Type-Options", "nosniff")
	writer.WriteHeader(http.StatusInternalServerError)

	html := strings.ReplaceAll(errorTemplate, "{{title}}", "Unexpected error")
	message := "An unexpected error occurred:<br>" + strings.ReplaceAll(err.Error(), ":", ":<br>")
	html = strings.ReplaceAll(html, "{{message}}", message)

	logger := logcontext.LoggerFromContext(ctx)
	logger.Errorf("an unexpected error occurred: %+v", err)

	_, _ = writer.Write([]byte(html))
}

func (responder *coordinatingResponder) guessSerializationFormat(contentType string) string {
	format := "raw"

	for _, guesser := range responder.formatGuessers {
		if guesser.pattern.MatchString(contentType) {
			format = guesser.format
			break
		}
	}

	return format
}
