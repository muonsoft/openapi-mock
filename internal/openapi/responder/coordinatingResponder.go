package responder

import (
	"fmt"
	"net/http"
	"regexp"
	"strings"
	"swagger-mock/internal/openapi/generator"
	"swagger-mock/internal/openapi/responder/serializer"
)

type coordinatingResponder struct {
	serializer     serializer.Serializer
	formatGuessers []formatGuess
}

type formatGuess struct {
	format  string
	pattern *regexp.Regexp
}

func (responder *coordinatingResponder) WriteResponse(writer http.ResponseWriter, response *generator.Response) {
	format := responder.guessSerializationFormat(response.ContentType)

	data, err := responder.serializer.Serialize(response.Data, format)
	if err != nil {
		responder.WriteUnexpectedError(writer, err.Error())
		return
	}

	writer.Header().Set("Content-Type", fmt.Sprintf("%s; charset=utf-8", response.ContentType))
	writer.Header().Set("X-Content-Type-Options", "nosniff")
	writer.WriteHeader(response.StatusCode)
	_, _ = writer.Write(data)
}

func (responder *coordinatingResponder) WriteUnexpectedError(writer http.ResponseWriter, message string) {
	writer.Header().Set("Content-Type", "text/html; charset=utf-8")
	writer.Header().Set("X-Content-Type-Options", "nosniff")
	writer.WriteHeader(http.StatusInternalServerError)

	html := strings.ReplaceAll(errorTemplate, "{{title}}", "Unexpected error")
	html = strings.ReplaceAll(html, "{{message}}", fmt.Sprintf("An unexpected error occurred: %s", message))

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
