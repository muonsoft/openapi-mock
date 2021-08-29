package mocking

import (
	"net/http"

	"github.com/getkin/kin-openapi/openapi3"
	"github.com/go-ozzo/ozzo-routing/v2/content"
	"github.com/muonsoft/openapi-mock/pkg/logcontext"
)

func NegotiateContentType(request *http.Request, response *openapi3.Response) string {
	if len(response.Content) == 0 {
		logcontext.Infof(request.Context(), "[NegotiateContentType] response has no content")

		return ""
	}

	contentTypes := getResponseContentTypes(response)
	contentType := content.NegotiateContentType(request, contentTypes, contentTypes[0])

	logcontext.Infof(
		request.Context(),
		"[NegotiateContentType] best media type '%s' was negotiated for accept header '%s'",
		contentType,
		request.Header.Get("Accept"),
	)

	return contentType
}

func getResponseContentTypes(response *openapi3.Response) []string {
	contentTypes := make([]string, 0, len(response.Content))

	for contentType := range response.Content {
		contentTypes = append(contentTypes, contentType)
	}

	return contentTypes
}
